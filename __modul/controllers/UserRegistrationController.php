<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\models\UserRegistration;
use app\services\RegistrationEmailService;

class UserRegistrationController extends BaseController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'approve' => ['POST'],
                    'reject' => ['POST'],
                    'delete' => ['POST'],
                    'resend-otp' => ['POST'],
                    'verify-email' => ['GET', 'POST'],
                ],
            ],
        ]);
    }

    /**
     * Tampilkan form registrasi dan proses pendaftaran
     */
    public function actionRegister()
    {
        $model = new \app\models\RegisterMasyarakatForm();

        if ($model->load(Yii::$app->request->post())) {
            $registration = $model->save();
            
            // Check if validation error is EMAIL_PENDING_RECOVERY
            $emailErrors = $model->getErrors('email');
            if (!empty($emailErrors)) {
                foreach ($emailErrors as $error) {
                    if (strpos($error, 'EMAIL_PENDING_RECOVERY:') === 0) {
                        $registrationId = (int) substr($error, strlen('EMAIL_PENDING_RECOVERY:'));
                        Yii::$app->session->setFlash('warning', 
                            'Email Anda masih dalam proses verifikasi. Klik tombol di bawah untuk melanjutkan.');
                        return $this->redirect(['recover-registration', 'registration_id' => $registrationId]);
                    }
                }
            }
            
            if ($registration instanceof UserRegistration) {
                Yii::$app->session->setFlash('success', 'Pendaftaran berhasil! Silakan verifikasi email Anda.');
                return $this->redirect(['/user-registration/verify-email', 'registration_id' => $registration->id]);
            }
        }

        return $this->render('register', [
            'model' => $model,
        ]);
    }

    /**
     * Recovery registrasi yang terputus / belum verified
     */
    public function actionRecoverRegistration()
    {
        $registration_id = (int) Yii::$app->request->get('registration_id', 0);
        
        if ($registration_id <= 0) {
            // Tampilkan form input email untuk recovery
            $model = new \app\models\RecoverRegistrationForm();
            
            if ($model->load(Yii::$app->request->post())) {
                $registration = UserRegistration::find()
                    ->where(['email' => trim($model->email)])
                    ->andWhere(['status' => UserRegistration::STATUS_EMAIL_PENDING])
                    ->one();
                
                if ($registration) {
                    return $this->redirect(['recover-registration', 'registration_id' => $registration->id]);
                } else {
                    $model->addError('email', 'Email tidak ditemukan atau sudah terverifikasi.');
                }
            }
            
            return $this->render('recover-registration-search', [
                'model' => $model,
            ]);
        }

        $registration = $this->findModel((int) $registration_id);

        // Hanya boleh recover jika status EMAIL_PENDING
        if ($registration->status !== UserRegistration::STATUS_EMAIL_PENDING) {
            if ($registration->status === UserRegistration::STATUS_PENDING_APPROVAL) {
                Yii::$app->session->setFlash('info', 'Email Anda sudah terverifikasi. Menunggu persetujuan admin.');
                return $this->redirect(['waiting-approval', 'registration_id' => $registration->id]);
            }
            Yii::$app->session->setFlash('warning', 'Registrasi tidak dapat diperbaharui pada tahap ini.');
            return $this->redirect(['register']);
        }

        // Cek apakah OTP sudah expired
        $otpExpired = false;
        if (!empty($registration->otp_expires_at) && strtotime($registration->otp_expires_at) < time()) {
            $otpExpired = true;
        }

        return $this->render('recover-registration', [
            'registration' => $registration,
            'otpExpired' => $otpExpired,
        ]);
    }

    /**
     * Edit data registrasi sebelum OTP diverifikasi
     */
    public function actionEditRegistration()
    {
        $registration_id = (int) Yii::$app->request->get('registration_id', 0);
        if ($registration_id <= 0) {
            return $this->redirect(['register']);
        }

        $registration = $this->findModel($registration_id);

        // Hanya boleh edit jika status EMAIL_PENDING
        if ($registration->status !== UserRegistration::STATUS_EMAIL_PENDING) {
            Yii::$app->session->setFlash('warning', 'Data tidak dapat diedit pada tahap ini.');
            return $this->redirect(['verify-email', 'registration_id' => $registration->id]);
        }

        $model = new \app\models\EditRegistrationForm();
        $model->loadFromRegistration($registration);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                // Cek apakah email berubah
                $emailChanged = ($registration->email !== trim($model->email));
                $oldEmail = $registration->email;

                // Update registration data
                $registration->nama_lengkap = trim($model->nama_lengkap);
                $registration->email = trim($model->email);
                $registration->telp = trim($model->telp);
                $registration->nama_institusi = trim((string) $model->nama_institusi);
                $registration->pekerjaan_posisi = trim((string) $model->pekerjaan_posisi);
                $registration->alamat_user = trim($model->alamat_user);
                $registration->provinsi_id = (int) $model->provinsi_id;
                $registration->kabupaten_id = (int) $model->kabupaten_id;
                $registration->tujuan_akses = $model->tujuan_akses;
                $registration->tujuan_akses_lainnya = trim((string) $model->tujuan_akses_lainnya);
                $registration->updated_at = date('Y-m-d H:i:s');

                if ($registration->save()) {
                    // Update user data juga
                    $user = $registration->getUser()->one();
                    if ($user) {
                        $user->nama_lengkap = trim($model->nama_lengkap);
                        $user->email = trim($model->email);
                        $user->no_telpon = trim($model->telp);
                        $user->phone = trim($model->telp);
                        $user->alamat = trim($model->alamat_user);
                        $user->save(false);
                    }

                    // Jika email berubah, generate dan kirim OTP baru
                    if ($emailChanged) {
                        $otp = $registration->generateOtp();
                        $registration->otp_resend_count = 0;
                        $registration->save(false);

                        try {
                            $emailSent = RegistrationEmailService::sendOtp($registration, $otp);
                            if ($emailSent) {
                                Yii::$app->session->setFlash('success', 
                                    'Data berhasil diperbarui. Kode OTP baru telah dikirim ke email baru Anda.');
                                Yii::info('OTP email sent to new address: ' . $registration->email . ' for registration_id: ' . $registration->id, __METHOD__);
                            } else {
                                Yii::$app->session->setFlash('warning', 
                                    'Data diperbarui tapi gagal mengirim OTP ke email baru. Gunakan "Kirim Ulang OTP".');
                                Yii::error('Failed to send OTP to new email: ' . $registration->email . ' for registration_id: ' . $registration->id, __METHOD__);
                            }
                        } catch (\Throwable $e) {
                            Yii::$app->session->setFlash('warning', 
                                'Data diperbarui tapi terjadi kesalahan saat mengirim OTP. Silakan gunakan "Kirim Ulang OTP".');
                            Yii::error('Exception sending OTP: ' . $e->getMessage(), __METHOD__);
                        }
                    } else {
                        Yii::$app->session->setFlash('success', 'Data berhasil diperbarui.');
                    }

                    return $this->redirect(['verify-email', 'registration_id' => $registration->id]);
                } else {
                    Yii::$app->session->setFlash('error', 'Gagal menyimpan data. Silakan coba lagi.');
                }
            }
        }

        return $this->render('edit-registration', [
            'model' => $model,
            'registration' => $registration,
        ]);
    }

    /**
     * Halaman verifikasi email dengan OTP
     */
    public function actionVerifyEmail($registration_id = null)
    {
        if ($registration_id === null) {
            // Redirect ke halaman registrasi jika tidak ada registration_id
            return $this->redirect(['register']);
        }

        $registration = $this->findModel((int) $registration_id);

        // Jika sudah verified, redirect ke waiting approval
        if ($registration->status !== UserRegistration::STATUS_EMAIL_PENDING) {
            if ($registration->status === UserRegistration::STATUS_PENDING_APPROVAL) {
                return $this->redirect(['waiting-approval', 'registration_id' => $registration->id]);
            }
            Yii::$app->session->setFlash('warning', 'Email Anda sudah terverifikasi atau pendaftaran sudah ditentukan statusnya.');
            return $this->redirect(['register']);
        }

        $model = new \app\models\VerifyEmailForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($registration->validateOtp($model->otp)) {
                if ($registration->markEmailVerified()) {
                    Yii::$app->session->setFlash('success', 'Email Anda berhasil terverifikasi! Menunggu persetujuan admin.');
                    return $this->redirect(['waiting-approval', 'registration_id' => $registration->id]);
                } else {
                    Yii::$app->session->setFlash('error', 'Gagal memperbarui status verifikasi. Silakan coba lagi.');
                }
            } else {
                $model->addError('otp', 'Kode OTP salah atau sudah kadaluarsa.');
            }
        }

        // Cek apakah OTP sudah expired
        $otpExpired = false;
        if (!empty($registration->otp_expires_at) && strtotime($registration->otp_expires_at) < time()) {
            $otpExpired = true;
        }

        return $this->render('verify-email', [
            'model' => $model,
            'registration' => $registration,
            'otpExpired' => $otpExpired,
        ]);
    }

    /**
     * Resend OTP jika expired atau tidak terima
     */
    public function actionResendOtp()
    {
        $registration_id = (int) Yii::$app->request->post('registration_id', 0);
        if ($registration_id <= 0) {
            return $this->asJson(['success' => false, 'message' => 'Invalid registration ID']);
        }

        $registration = $this->findModel($registration_id);

        // Validasi status masih email_pending
        if ($registration->status !== UserRegistration::STATUS_EMAIL_PENDING) {
            return $this->asJson(['success' => false, 'message' => 'Pendaftaran Anda sudah diverifikasi.']);
        }

        // Cek limit resend OTP (max 10 kali)
        if ($registration->otp_resend_count >= 10) {
            return $this->asJson(['success' => false, 'message' => 'Batas resend OTP (10 kali) sudah tercapai. Silakan hubungi admin.']);
        }

        try {
            $otp = $registration->generateOtp();
            $registration->otp_resend_count = ($registration->otp_resend_count ?? 0) + 1;

            if ($registration->save(false)) {
                Yii::info('OTP generated: ' . $otp . ' for registration_id: ' . $registration_id, __METHOD__);
                
                $emailSent = RegistrationEmailService::sendOtp($registration, $otp);
                
                if ($emailSent) {
                    Yii::info('OTP email sent successfully to ' . $registration->email, __METHOD__);
                    return $this->asJson([
                        'success' => true,
                        'message' => 'Kode OTP berhasil dikirim ke email Anda. Berlaku selama 10 menit.',
                    ]);
                } else {
                    Yii::error('OTP email send returned FALSE for registration_id: ' . $registration_id . ', email: ' . $registration->email, __METHOD__);
                    return $this->asJson([
                        'success' => false,
                        'message' => 'Gagal mengirim email OTP. Silakan coba lagi.',
                    ]);
                }
            } else {
                Yii::error('Failed to save OTP resend count for registration_id: ' . $registration_id, __METHOD__);
                return $this->asJson([
                    'success' => false,
                    'message' => 'Gagal memperbarui data. Silakan coba lagi.',
                ]);
            }
        } catch (\Throwable $e) {
            Yii::error('Resend OTP gagal: ' . $e->getMessage(), __METHOD__);
            return $this->asJson(['success' => false, 'message' => 'Terjadi error. Silakan coba lagi.']);
        }

        return $this->asJson(['success' => false, 'message' => 'Gagal memperbarui data. Silakan coba lagi.']);
    }

    /**
     * Halaman menunggu persetujuan admin
     */
    public function actionWaitingApproval($registration_id = null)
    {
        if ($registration_id === null) {
            return $this->redirect(['register']);
        }

        $registration = $this->findModel((int) $registration_id);

        // Redirect jika status bukan pending_approval
        if ($registration->status !== UserRegistration::STATUS_PENDING_APPROVAL) {
            if ($registration->status === UserRegistration::STATUS_APPROVED) {
                Yii::$app->session->setFlash('success', 'Pengajuan Anda telah disetujui! Anda dapat login sekarang.');
                return $this->redirect(['/site/login']);
            } elseif ($registration->status === UserRegistration::STATUS_REJECTED) {
                $reason = $registration->rejection_reason ? 'Alasan: ' . $registration->rejection_reason : '';
                Yii::$app->session->setFlash('error', 'Pengajuan Anda telah ditolak. ' . $reason);
                return $this->redirect(['register']);
            } else {
                return $this->redirect(['verify-email', 'registration_id' => $registration->id]);
            }
        }

        return $this->render('waiting-approval', [
            'registration' => $registration,
        ]);
    }

    public function actionIndex($status = null)
    {
        $query = UserRegistration::find()->orderBy(['id' => SORT_DESC]);

        if ($status && array_key_exists($status, UserRegistration::statusLabels())) {
            $query->andWhere(['status' => $status]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'status' => $status,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        if ($model->status !== UserRegistration::STATUS_PENDING_APPROVAL) {
            Yii::$app->session->setFlash('warning', 'Pengajuan ini tidak dalam status menunggu approval.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $transaction = UserRegistration::getDb()->beginTransaction();
        try {
            $user = $model->user;
            if (!$user) {
                throw new \RuntimeException('User terkait tidak ditemukan.');
            }

            if ($user->hasAttribute('is_active')) {
                $user->is_active = true;
            }
            if ($user->hasAttribute('status')) {
                $user->status = 1;
            }
            if ($user->hasAttribute('updated_at')) {
                $user->updated_at = date('Y-m-d H:i:s');
            }

            // Hasilkan password sementara (8 karakter alfanumerik) dan kirimkan via email
            $plainPassword = strtoupper(substr(base_convert(bin2hex(random_bytes(6)), 16, 36), 0, 8));
            if ($user->hasAttribute('password_hash')) {
                $user->password_hash = Yii::$app->security->generatePasswordHash($plainPassword);
            }

            if (!$user->save(false)) {
                throw new \RuntimeException('Gagal mengaktifkan user.');
            }

            if (!$model->markApproved((int) Yii::$app->user->id)) {
                throw new \RuntimeException('Gagal menyimpan status approval.');
            }

            $transaction->commit();
            RegistrationEmailService::sendApproved($model, $plainPassword);
            Yii::$app->session->setFlash('success', 'Pengajuan berhasil di-approve. Email dengan kredensial telah dikirimkan.');
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error('Approve registrasi gagal: ' . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Gagal approve: ' . $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionReject($id)
    {
        $model = $this->findModel($id);
        if ($model->status !== UserRegistration::STATUS_PENDING_APPROVAL) {
            Yii::$app->session->setFlash('warning', 'Pengajuan ini tidak dalam status menunggu approval.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $reason = trim((string) Yii::$app->request->post('reason', ''));

        $transaction = UserRegistration::getDb()->beginTransaction();
        try {
            $user = $model->user;
            if ($user) {
                if ($user->hasAttribute('is_active')) {
                    $user->is_active = false;
                }
                if ($user->hasAttribute('status')) {
                    $user->status = 0;
                }
                if ($user->hasAttribute('updated_at')) {
                    $user->updated_at = date('Y-m-d H:i:s');
                }
                $user->save(false);
            }

            if (!$model->markRejected((int) Yii::$app->user->id, $reason !== '' ? $reason : null)) {
                throw new \RuntimeException('Gagal menyimpan status reject.');
            }

            $transaction->commit();
            $emailSent = RegistrationEmailService::sendRejected($model);
            Yii::$app->session->setFlash(
                $emailSent ? 'success' : 'warning',
                $emailSent
                    ? 'Pengajuan berhasil ditolak dan email informasi telah dikirim.'
                    : 'Pengajuan berhasil ditolak, tetapi email informasi gagal dikirim. Silakan cek konfigurasi email.'
            );
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error('Reject registrasi gagal: ' . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Gagal reject: ' . $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $user = $model->user;

        if ($user && (int) $user->getId() === (int) Yii::$app->user->id) {
            Yii::$app->session->setFlash('warning', 'Tidak bisa menghapus akun yang sedang digunakan untuk login.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $transaction = UserRegistration::getDb()->beginTransaction();
        try {
            if ($model->delete() === false) {
                throw new \RuntimeException('Gagal menghapus data pendaftaran.');
            }

            if ($user && $user->delete() === false) {
                throw new \RuntimeException('Gagal menghapus user terkait.');
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Data pendaftaran dan user terkait berhasil dihapus.');
            return $this->redirect(['index']);
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error('Delete registrasi gagal: ' . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Gagal menghapus data: ' . $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    protected function findModel($id): UserRegistration
    {
        $model = UserRegistration::findOne((int) $id);
        if (!$model) {
            throw new NotFoundHttpException('Data pendaftaran tidak ditemukan.');
        }

        return $model;
    }
}
