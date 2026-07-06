<?php

namespace app\controllers;

use Yii;
use app\models\User;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ProfilController extends BaseController
{
    public function actionIndex()
    {
        $id = Yii::$app->user->id;
        if (!$id) {
            return $this->redirect(['/site/login']);
        }
        $model = User::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('User tidak ditemukan.');
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    public function actionUpdate()
    {
        $id = Yii::$app->user->id;
        if (!$id) {
            return $this->redirect(['/site/login']);
        }
        $model = User::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('User tidak ditemukan.');
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            
            $namaLengkap = $post['nama_lengkap'] ?? '';
            $email = $post['email'] ?? '';
            $noTelpon = $post['no_telpon'] ?? '';
            $alamat = $post['alamat'] ?? '';
            $jenisKelamin = $post['jenis_kelamin'] ?? '';
            
            $valid = true;
            if (empty($namaLengkap)) {
                $model->addError('nama_lengkap', 'Nama Lengkap tidak boleh kosong.');
                $valid = false;
            }
            if (empty($email)) {
                $model->addError('email', 'Email tidak boleh kosong.');
                $valid = false;
            } else {
                $existingEmail = User::find()
                    ->where(['email' => $email])
                    ->andWhere(['not', ['id' => $model->id]])
                    ->one();
                if ($existingEmail) {
                    $model->addError('email', 'Email sudah terdaftar oleh pengguna lain.');
                    $valid = false;
                }
            }

            if ($valid) {
                $model->nama_lengkap = $namaLengkap;
                $model->email = $email;
                $model->no_telpon = $noTelpon;
                $model->alamat = $alamat;
                $model->jenis_kelamin = $jenisKelamin;

                // Handle file upload
                $file = UploadedFile::getInstanceByName('foto_profil');
                if ($file) {
                    if ($file->size > 2 * 1024 * 1024) {
                        $model->addError('foto_profil', 'Ukuran file foto maksimal adalah 2MB.');
                        $valid = false;
                    }
                    
                    if (!in_array(strtolower($file->extension), ['jpg', 'jpeg', 'png'], true)) {
                        $model->addError('foto_profil', 'Format file foto harus berupa JPG, JPEG, atau PNG.');
                        $valid = false;
                    }

                    if ($valid) {
                        $dir = Yii::getAlias('@app/../uploads/profile/');
                        if (!is_dir($dir)) {
                            mkdir($dir, 0777, true);
                        }
                        $fileName = uniqid() . '.' . $file->extension;
                        $filePath = $dir . $fileName;
                        
                        if ($file->saveAs($filePath)) {
                            // Delete old photo if exists
                            if (!empty($model->foto_profil)) {
                                $oldPath = Yii::getAlias('@app/../') . ltrim($model->foto_profil, '/');
                                if (file_exists($oldPath) && is_file($oldPath)) {
                                    @unlink($oldPath);
                                }
                            }
                            $model->foto_profil = 'uploads/profile/' . $fileName;
                        } else {
                            $model->addError('foto_profil', 'Gagal mengunggah foto profil.');
                            $valid = false;
                        }
                    }
                }

                if ($valid && $model->save(false)) {
                    Yii::$app->session->setFlash('swal', [
                        'icon' => 'success',
                        'title' => 'Berhasil',
                        'text' => 'Profil Anda berhasil diperbarui.',
                    ]);
                    return $this->redirect(['index']);
                } else {
                    Yii::$app->session->setFlash('swal', [
                        'icon' => 'error',
                        'title' => 'Gagal',
                        'text' => 'Gagal menyimpan pembaruan profil.',
                    ]);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionUbahPassword()
    {
        $id = Yii::$app->user->id;
        if (!$id) {
            return $this->redirect(['/site/login']);
        }
        $model = User::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('User tidak ditemukan.');
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $passwordLama = $post['password_lama'] ?? '';
            $passwordBaru = $post['password_baru'] ?? '';
            $passwordKonfirmasi = $post['password_konfirmasi'] ?? '';

            $valid = true;
            if (empty($passwordLama)) {
                $model->addError('password', 'Password lama wajib diisi.');
                $valid = false;
            } elseif (!$model->validatePassword($passwordLama)) {
                $model->addError('password', 'Password lama tidak sesuai.');
                $valid = false;
            }

            if (empty($passwordBaru)) {
                $model->addError('password', 'Password baru wajib diisi.');
                $valid = false;
            } elseif ($passwordBaru !== $passwordKonfirmasi) {
                $model->addError('password', 'Konfirmasi password baru tidak cocok.');
                $valid = false;
            } elseif (!$this->isStrongPassword($passwordBaru)) {
                $model->addError('password', $this->getStrongPasswordMessage());
                $valid = false;
            }

            if ($valid) {
                $model->password = Yii::$app->security->generatePasswordHash($passwordBaru);
                if ($model->save(false)) {
                    Yii::$app->user->logout(false);
                    Yii::$app->session->setFlash('swal', [
                        'icon' => 'success',
                        'title' => 'Berhasil',
                        'text' => 'Password Anda berhasil diperbarui. Silakan login kembali.',
                    ]);
                    return $this->redirect(['/site/login']);
                } else {
                    Yii::$app->session->setFlash('swal', [
                        'icon' => 'error',
                        'title' => 'Gagal',
                        'text' => 'Gagal memperbarui password.',
                    ]);
                }
            }
        }

        return $this->render('ubah-password', [
            'model' => $model,
        ]);
    }

    protected function isStrongPassword(string $password): bool
    {
        return (bool) preg_match(
            '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            $password
        );
    }

    protected function getStrongPasswordMessage(): string
    {
        return 'Password minimal 8 karakter, harus mengandung huruf besar, huruf kecil, angka, dan karakter khusus (@$!%*?&).';
    }
}
