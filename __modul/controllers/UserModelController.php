<?php

namespace app\controllers;

use app\models\MasterWilayah;
use app\models\User;
use app\models\level_user\LevelUser;
use app\services\RegistrationEmailService;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;

/**
 * UserModelController implements the CRUD actions for User model.
 */
class UserModelController extends BaseController
{
    public $enableCsrfValidation = false;

    protected function csrfExemptActions(): array
    {
        return ['delete', 'test-delete', 'get-list', 'get-master-wilayah', 'get-master-wilayah-detail'];
    }

    protected function shouldSanitizeRequest(string $actionId): bool
    {
        if (in_array($actionId, ['delete', 'test-delete', 'get-list', 'get-master-wilayah', 'get-master-wilayah-detail'], true)) {
            return false;
        }
        return true;
    }

    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Decode ID yang mungkin ter-enkripsi oleh BaseController/middleware.
     */
    protected function decodeId($id): ?int
    {
        if ($id === null || $id === '') {
            return null;
        }

        if (is_numeric($id)) {
            return (int) $id;
        }

        try {
            $decoded = \Yii::$app->security->decryptByKey(
                base64_decode($id),
                \Yii::$app->request->cookieValidationKey
            );
            if ($decoded !== false && is_numeric($decoded)) {
                return (int) $decoded;
            }
        } catch (\Throwable $e) {
            // bukan encrypted string Yii, lanjut
        }

        $rawId = \Yii::$app->request->get('id');
        if ($rawId !== null && is_numeric($rawId)) {
            return (int) $rawId;
        }

        return null;
    }

    public function actionGetList()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $request          = \Yii::$app->request;
            $draw             = (int) $request->get('draw', 0);
            $start            = max(0, (int) $request->get('start', 0));
            $length           = (int) $request->get('length', 10);
            $length           = $length > 0 ? min($length, 100) : 10;
            $search           = trim((string) ($request->get('search')['value'] ?? ''));
            $orderColumnIndex = (int) ($request->get('order')[0]['column'] ?? 0);
            $orderDirection   = strtolower((string) ($request->get('order')[0]['dir'] ?? 'desc')) === 'asc' ? SORT_ASC : SORT_DESC;

            $baseQuery = User::find()
                ->alias('u')
                ->leftJoin(
                    LevelUser::tableName() . ' lu',
                    'lu.id = COALESCE(u.id_user_level, u.level_user_id)'
                )
                ->select([
                    'u.id',
                    'u.username',
                    'u.nama_lengkap',
                    'u.email',
                    'u.kd_prop',
                    'u.kd_kab',
                    'u.is_active',
                    'level_user_nama' => 'lu.nama_level',
                ]);

            $totalCount = (clone $baseQuery)->count('u.id');

            if ($search !== '') {
                $baseQuery->andFilterWhere([
                    'or',
                    ['like', 'u.username', $search],
                    ['like', 'u.nama_lengkap', $search],
                    ['like', 'u.email', $search],
                    ['like', 'lu.nama_level', $search],
                ]);
            }

            $filteredCount = (clone $baseQuery)->count('u.id');

            $sortableColumns = [
                0 => 'u.id',
                1 => 'u.username',
                2 => 'u.nama_lengkap',
                3 => 'u.email',
                4 => 'lu.nama_level',
                5 => 'u.kd_prop',
                6 => 'u.kd_kab',
                7 => 'u.is_active',
            ];
            $orderByColumn = $sortableColumns[$orderColumnIndex] ?? 'u.id';

            $users = (clone $baseQuery)
                ->orderBy([$orderByColumn => $orderDirection, 'u.id' => SORT_DESC])
                ->offset($start)
                ->limit($length)
                ->asArray()
                ->all();

            $wilayahCache   = [];
            $allWilayahIds  = [];
            foreach ($users as $user) {
                if ($user['kd_prop']) $allWilayahIds[] = $user['kd_prop'];
                if ($user['kd_kab'])  $allWilayahIds[] = $user['kd_kab'];
            }

            if (!empty($allWilayahIds)) {
                $wilayahRecords = MasterWilayah::find()
                    ->where(['id' => array_unique($allWilayahIds)])
                    ->asArray()
                    ->all();
                foreach ($wilayahRecords as $w) {
                    $wilayahCache[$w['id']] = $w['nama_wilayah'];
                }
            }

            $data = [];
            foreach ($users as $user) {
                $data[] = [
                    'id'              => $user['id'] ?? null,
                    'username'        => $user['username'] ?? '',
                    'nama_lengkap'    => $user['nama_lengkap'] ?? '',
                    'email'           => $user['email'] ?? '',
                    'level_user_nama' => $user['level_user_nama'] ?? '-',
                    'provinsi_nama'   => $wilayahCache[$user['kd_prop'] ?? ''] ?? '-',
                    'kabupaten_nama'  => $wilayahCache[$user['kd_kab']  ?? ''] ?? '-',
                    'is_active'       => (bool) ($user['is_active'] ?? false),
                ];
            }

            return [
                'success'         => true,
                'draw'            => $draw,
                'recordsTotal'    => (int) $totalCount,
                'recordsFiltered' => (int) $filteredCount,
                'data'            => $data,
            ];

        } catch (\Exception $e) {
            \Yii::error('UserModelController::actionGetList - ' . $e->getMessage());
            return [
                'success'         => false,
                'message'         => 'Error: ' . $e->getMessage(),
                'draw'            => (int) \Yii::$app->request->get('draw', 0),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            ];
        }
    }

    public function actionIndex()
    {
        $query        = User::find()->orderBy(['id' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 50],
            'sort'       => [
                'attributes'   => [
                    'username',
                    'nama_lengkap',
                    'email',
                    'id' => [
                        'asc'     => ['id' => SORT_ASC],
                        'desc'    => ['id' => SORT_DESC],
                        'default' => SORT_DESC,
                        'label'   => 'ID',
                    ],
                ],
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionView($id)
    {
        $id = $this->decodeId($id);

        if (!$id) {
            throw new NotFoundHttpException('ID tidak valid');
        }

        if (\Yii::$app->request->isGet && !\Yii::$app->request->isAjax) {
            $model = User::findOne($id);
            if (!$model) throw new NotFoundHttpException('User tidak ditemukan');
            return $this->render('view', ['model' => $model]);
        }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = User::find()->where(['id' => $id])->one();

        if (!$model) return ['success' => false, 'message' => 'User tidak ditemukan'];

        return [
            'success' => true,
            'data'    => [
                'id'                  => $model->getId(),
                'username'            => $model->username,
                'nama_lengkap'        => $model->nama_lengkap ?? '',
                'email'               => $model->email,
                'level_user_id'       => $model->id_user_level ?? $model->level_user_id ?? null,
                'id_user_level'       => $model->id_user_level ?? $model->level_user_id ?? null,
                'level_user_nama'     => '',
                'master_wilayah_id'   => $model->master_wilayah_id ?? null,
                'master_wilayah_nama' => $model->masterWilayah->nama_wilayah ?? '',
                'foto_profil'         => $model->foto_profil,
                'kd_prop'             => $model->hasAttribute('kd_prop') ? $model->getAttribute('kd_prop') : null,
                'kd_kab'              => $model->hasAttribute('kd_kab') ? $model->getAttribute('kd_kab') : null,
                'kd_kecamatan'        => $model->hasAttribute('kd_kecamatan') ? $model->getAttribute('kd_kecamatan') : null,
                'is_active'           => $model->is_active ?? null,
            ],
        ];
    }

    public function actionSave()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req = \Yii::$app->request;

        if (!$req->isPost) {
            return ['success' => false, 'message' => 'Invalid request method'];
        }

        try {
            $postId          = $req->post('id'); // FIX: simpan terpisah supaya tidak tertukar
            $username        = $req->post('username');
            $namaLengkap     = $req->post('nama_lengkap');
            $email           = $req->post('email');
            $password        = $req->post('password_plain', $req->post('password'));
            $passwordConfirm = $req->post('password_confirm');
            $levelUserId     = $req->post('id_user_level', $req->post('level_user_id'));
            $kdProp          = $req->post('kd_prop');
            $kdKab           = $req->post('kd_kab');
            $kdKecamatan     = $req->post('kd_kecamatan');
            $isActive        = $req->post('is_active', 1);
            $masterWilayahId = $this->resolveMasterWilayahIdFromSelection(
                $levelUserId,
                $req->post('master_wilayah_id'),
                $kdProp,
                $kdKab
            );

            if (!$username || !$email) {
                return ['success' => false, 'message' => 'Username dan email tidak boleh kosong'];
            }
            if (!$namaLengkap) {
                return ['success' => false, 'message' => 'Nama lengkap tidak boleh kosong'];
            }
            if (!$levelUserId) {
                return ['success' => false, 'message' => 'Level user wajib dipilih'];
            }
            if (!$this->isWilayahOptionalForLevel($levelUserId) && !$masterWilayahId) {
                return ['success' => false, 'message' => 'Wilayah wajib dipilih untuk level user ini'];
            }
            if (!$this->isValidWilayahSelection($masterWilayahId, $levelUserId)) {
                return ['success' => false, 'message' => 'Wilayah tidak sesuai dengan level user yang dipilih'];
            }

            $isCreate = empty($postId); // FIX: gunakan $postId, bukan $id

            if ($isCreate) {
                // ── CREATE ──────────────────────────────────────────────
                if (!$password) {
                    return ['success' => false, 'message' => 'Password tidak boleh kosong'];
                }
                if ($password !== $passwordConfirm) {
                    return ['success' => false, 'message' => 'Password dan konfirmasi password tidak cocok'];
                }
                if (!$this->isStrongPassword($password)) {
                    return ['success' => false, 'message' => $this->getStrongPasswordMessage()];
                }
                $existingUser = User::findOne(['username' => $username]);
                if ($existingUser) {
                    return ['success' => false, 'message' => 'Username sudah terdaftar'];
                }
                $existingEmail = User::findOne(['email' => $email]);
                if ($existingEmail) {
                    return ['success' => false, 'message' => 'Email sudah terdaftar. Silakan gunakan email lain.'];
                }

                $model               = new User();
                $model->username     = $username;
                $model->nama_lengkap = $namaLengkap;
                $model->email        = $email;
                $model->password     = \Yii::$app->security->generatePasswordHash($password);
                $model->id_user_level= $levelUserId;
                $model->is_active    = (bool) $isActive;
                $model->join_date    = date('Y-m-d H:i:s');

            } else {
                // ── UPDATE ──────────────────────────────────────────────
                $decodedId = $this->decodeId($postId);
                $model     = User::findOne($decodedId);

                if (!$model) {
                    return ['success' => false, 'message' => 'User tidak ditemukan'];
                }

                $existingUser = User::findOne(['username' => $username]);
                if ($existingUser && (int) $existingUser->getId() !== (int) $model->getId()) {
                    return ['success' => false, 'message' => 'Username sudah terdaftar'];
                }
                $existingEmail = User::findOne(['email' => $email]);
                if ($existingEmail && (int) $existingEmail->getId() !== (int) $model->getId()) {
                    return ['success' => false, 'message' => 'Email sudah terdaftar. Silakan gunakan email lain.'];
                }

                if ($password) {
                    if ($password !== $passwordConfirm) {
                        return ['success' => false, 'message' => 'Password dan konfirmasi password tidak cocok'];
                    }
                    if (!$this->isStrongPassword($password)) {
                        return ['success' => false, 'message' => $this->getStrongPasswordMessage()];
                    }
                    $model->password = \Yii::$app->security->generatePasswordHash($password);
                }

                $model->username      = $username;
                $model->nama_lengkap  = $namaLengkap;
                $model->email         = $email;
                $model->id_user_level = $levelUserId;
                $model->is_active     = (bool) $isActive;
            }

            // Sync fields yang mungkin ada di tabel
            if ($model->hasAttribute('id_user_level'))     $model->id_user_level     = $levelUserId;
            if ($model->hasAttribute('level_user_id'))     $model->level_user_id     = $levelUserId;
            if ($model->hasAttribute('master_wilayah_id')) $model->master_wilayah_id = $masterWilayahId ?: null;
            if ($model->hasAttribute('kd_prop'))           $model->setAttribute('kd_prop',      $kdProp      ?: null);
            if ($model->hasAttribute('kd_kab'))            $model->setAttribute('kd_kab',       $kdKab       ?: null);
            if ($model->hasAttribute('kd_kecamatan'))      $model->setAttribute('kd_kecamatan', $kdKecamatan ?: null);
            if ($model->hasAttribute('status'))            $model->status = (bool) $isActive;

            if ($model->save()) {
                // FIX: gunakan $isCreate, bukan $postId / $id yang sudah berubah
                $emailSent = true;
                if ($isCreate && !empty(trim((string) $model->getAttribute('email')))) {
                    $emailSent = RegistrationEmailService::sendAdminCreatedAccount($model, $password);
                    Yii::info('Email create user - sent: ' . ($emailSent ? 'yes' : 'no') . ' to: ' . $model->getAttribute('email'), __METHOD__);
                }

                return [
                    'success'    => true,
                    'message'    => !$isCreate
                        ? 'User berhasil diperbarui'
                        : ($emailSent
                            ? 'User berhasil ditambahkan dan notifikasi email berhasil dikirim'
                            : 'User berhasil ditambahkan, tetapi notifikasi email gagal dikirim'),
                    'email_sent' => $emailSent,
                    'data'       => ['id' => $model->getId(), 'username' => $model->username],
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan user: ' . implode(', ', $model->getFirstErrors()),
                ];
            }

        } catch (\Exception $e) {
            \Yii::error('UserModelController::actionSave - ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function actionCreate()
    {
        if (\Yii::$app->request->isPost && \Yii::$app->request->isAjax) {
            return $this->actionSave();
        }

        $model = new User();

        if ($this->request->isPost) {
            $post            = $this->request->post();
            $password        = $post['password'] ?? null;
            $passwordConfirm = $post['password_confirm'] ?? null;

            if (isset($post['User'])) {
                $model->load($post);
            } else {
                $model->setAttributes($post, false);
            }

            foreach (['nama_lengkap', 'kd_prop', 'kd_kab', 'kd_kecamatan'] as $field) {
                if (isset($post[$field]) && $model->hasAttribute($field)) {
                    $model->setAttribute($field, $post[$field] ?: null);
                }
            }

            $levelUserId     = $model->id_user_level ?? $model->level_user_id ?? null;
            $masterWilayahId = $this->resolveMasterWilayahIdFromSelection(
                $levelUserId,
                $model->master_wilayah_id ?? null,
                $model->getAttribute('kd_prop'),
                $model->getAttribute('kd_kab')
            );

            if (empty($model->nama_lengkap)) {
                $model->addError('nama_lengkap', 'Nama lengkap tidak boleh kosong');
            }

            if ($password && $passwordConfirm) {
                if ($password !== $passwordConfirm) {
                    $model->addError('password', 'Password dan konfirmasi tidak cocok');
                } elseif (!$this->isStrongPassword($password)) {
                    $model->addError('password', $this->getStrongPasswordMessage());
                } else {
                    $model->password = \Yii::$app->security->generatePasswordHash($password);
                }
            } elseif (!$password) {
                $model->addError('password', 'Password tidak boleh kosong');
            }

            if (!$levelUserId) {
                $model->addError('id_user_level', 'Level user wajib dipilih');
            }

            if (!$this->isWilayahOptionalForLevel($levelUserId) && !$masterWilayahId) {
                $model->addError('master_wilayah_id', 'Wilayah wajib dipilih untuk level user ini');
            } elseif (!$this->isValidWilayahSelection($masterWilayahId, $levelUserId)) {
                $model->addError('master_wilayah_id', 'Wilayah tidak sesuai dengan level user yang dipilih');
            }

            $this->syncUserLegacyFields($model, $levelUserId, $masterWilayahId);

            $existingUser = User::findOne(['username' => $model->username]);
            if ($existingUser) {
                $model->addError('username', 'Username sudah terdaftar');
            }
            $existingEmail = User::findOne(['email' => $model->email]);
            if ($existingEmail) {
                $model->addError('email', 'Email sudah terdaftar. Silakan gunakan email lain.');
            }

            if (!$model->hasErrors() && $model->save()) {
                // FIX: ambil email via getAttribute supaya tidak kena magic getter
                $emailAddress = trim((string) $model->getAttribute('email'));
                $emailSent    = true;

                if ($emailAddress !== '') {
                    $emailSent = RegistrationEmailService::sendAdminCreatedAccount($model, $password);
                    Yii::info('actionCreate email - sent: ' . ($emailSent ? 'yes' : 'no') . ' to: ' . $emailAddress, __METHOD__);
                }

                \Yii::$app->session->setFlash('swal', [
                    'icon'  => $emailSent ? 'success' : 'warning',
                    'title' => 'Berhasil',
                    'text'  => $emailSent
                        ? 'Data pengguna berhasil dibuat dan notifikasi email sudah dikirim.'
                        : 'Data pengguna berhasil dibuat, tetapi notifikasi email gagal dikirim.',
                ]);
                return $this->redirect(['index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model'         => $model,
            'levelOptions'  => $this->getLevelOptions(),
            'provinsiList'  => $this->getProvinsiOptions(),
            'kabupatenList' => $this->getKabupatenByProvince($model->getAttribute('kd_prop')),
        ]);
    }

    public function actionUpdate($id = null)
    {
        if (\Yii::$app->request->isPost && \Yii::$app->request->isAjax && !isset($_FILES)) {
            return $this->actionSave();
        }

        if ($id === null) {
            $id = $this->request->post('id')
                ?? $this->request->post('id_user')
                ?? ($this->request->post('User')['id'] ?? null);
        }

        $id = $this->decodeId($id) ?? $this->decodeId(Yii::$app->user->id ?? null);

        if (!$id) {
            throw new NotFoundHttpException('ID tidak ditemukan');
        }

        $model = $this->findModel($id);

        if (!$model->getAttribute('kd_prop') && !$model->getAttribute('kd_kab') && $model->master_wilayah_id) {
            $wilayah = MasterWilayah::findOne($model->master_wilayah_id);
            if ($wilayah) {
                if ($wilayah->level_wilayah == 1) {
                    $model->setAttribute('kd_prop', $wilayah->id);
                } elseif ($wilayah->level_wilayah == 2) {
                    $model->setAttribute('kd_kab',  $wilayah->id);
                    $model->setAttribute('kd_prop', $wilayah->parent_master_wilayah_id);
                }
            }
        }

        if (!$this->request->isPost) {
            return $this->render('update', [
                'model'         => $model,
                'levelOptions'  => $this->getLevelOptions(),
                'provinsiList'  => $this->getProvinsiOptions(),
                'kabupatenList' => $this->getKabupatenByProvince($model->getAttribute('kd_prop')),
            ]);
        }

        $post = $this->request->post();
        if (isset($post['User'])) {
            $model->load($post);
        } else {
            $model->setAttributes($post, false);
        }

        foreach (['nama_lengkap', 'kd_prop', 'kd_kab', 'kd_kecamatan'] as $field) {
            if (isset($post[$field]) && $model->hasAttribute($field)) {
                $model->setAttribute($field, $post[$field] ?: null);
            }
        }

        $password        = $post['password'] ?? null;
        $passwordConfirm = $post['password_confirm'] ?? null;
        $levelUserId     = $model->id_user_level ?? $model->level_user_id ?? null;
        $masterWilayahId = $this->resolveMasterWilayahIdFromSelection(
            $levelUserId,
            $model->master_wilayah_id ?? null,
            $model->getAttribute('kd_prop'),
            $model->getAttribute('kd_kab')
        );

        if (empty($model->nama_lengkap)) {
            $model->addError('nama_lengkap', 'Nama lengkap tidak boleh kosong');
        }

        if (!empty($password) || !empty($passwordConfirm)) {
            if ($password !== $passwordConfirm) {
                $model->addError('password', 'Password dan konfirmasi tidak cocok');
            } elseif (!$this->isStrongPassword((string) $password)) {
                $model->addError('password', $this->getStrongPasswordMessage());
            } else {
                $model->password = \Yii::$app->security->generatePasswordHash($password);
            }
        }

        if (!$levelUserId) {
            $model->addError('id_user_level', 'Level user wajib dipilih');
        }

        if (!$this->isWilayahOptionalForLevel($levelUserId) && !$masterWilayahId) {
            $model->addError('master_wilayah_id', 'Wilayah wajib dipilih untuk level user ini');
        } elseif (!$this->isValidWilayahSelection($masterWilayahId, $levelUserId)) {
            $model->addError('master_wilayah_id', 'Wilayah tidak sesuai dengan level user yang dipilih');
        }

        $this->syncUserLegacyFields($model, $levelUserId, $masterWilayahId);

        $existingUser = User::findOne(['username' => $model->username]);
        if ($existingUser && (int) $existingUser->getId() !== (int) $model->getId()) {
            $model->addError('username', 'Username sudah terdaftar');
        }
        $existingEmail = User::findOne(['email' => $model->email]);
        if ($existingEmail && (int) $existingEmail->getId() !== (int) $model->getId()) {
            $model->addError('email', 'Email sudah terdaftar. Silakan gunakan email lain.');
        }

        if (!$model->hasErrors() && $model->save()) {
            \Yii::$app->session->setFlash('swal', [
                'icon'  => 'success',
                'title' => 'Berhasil',
                'text'  => 'Data pengguna berhasil diperbarui.',
            ]);
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model'         => $model,
            'levelOptions'  => $this->getLevelOptions(),
            'provinsiList'  => $this->getProvinsiOptions(),
            'kabupatenList' => $this->getKabupatenByProvince($model->getAttribute('kd_prop')),
        ]);
    }

    public function actionDelete($id = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            if ($id === null) {
                $id = \Yii::$app->request->post('id') ?? \Yii::$app->request->get('id');
            }

            $id = $this->decodeId($id);

            if (!$id) {
                return ['success' => false, 'message' => 'ID user tidak valid'];
            }

            $model  = $this->findModel($id);
            $result = $model->delete();

            if ($result !== false) {
                return ['success' => true, 'message' => 'Data berhasil dihapus'];
            } else {
                return ['success' => false, 'message' => 'Gagal menghapus data dari database'];
            }

        } catch (\yii\web\NotFoundHttpException $e) {
            return ['success' => false, 'message' => 'User tidak ditemukan'];
        } catch (\Exception $e) {
            \Yii::error('Delete error: ' . $e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function actionTestDelete()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'success'        => true,
            'message'        => 'Test action works',
            'request_method' => \Yii::$app->request->method,
            'post_data'      => \Yii::$app->request->post(),
            'get_data'       => \Yii::$app->request->get(),
            'raw_body'       => \Yii::$app->request->rawBody,
        ];
    }

    public function actionUbahPassword()
    {
        if (\Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $userId = \Yii::$app->user->id;
        $req    = \Yii::$app->request;

        if ($req->isPost) {
            $old     = $req->post('old_password');
            $new     = $req->post('new_password');
            $confirm = $req->post('confirm_password');

            if ($new === null || $old === null || $confirm === null) {
                \Yii::$app->session->setFlash('error', 'Parameter tidak lengkap');
                return $this->redirect(['user-model/ubah-password']);
            }
            if (!$this->isStrongPassword($new)) {
                \Yii::$app->session->setFlash('error', $this->getStrongPasswordMessage());
                return $this->redirect(['user-model/ubah-password']);
            }
            if ($new !== $confirm) {
                \Yii::$app->session->setFlash('error', 'Konfirmasi password tidak cocok');
                return $this->redirect(['user-model/ubah-password']);
            }

            $model = $this->findModel($userId);
            if (!$model->validatePassword($old)) {
                \Yii::$app->session->setFlash('error', 'Password lama salah');
                return $this->redirect(['user-model/ubah-password']);
            }

            $model->password = \Yii::$app->getSecurity()->generatePasswordHash($new);
            if ($model->save(false)) {
                if (!\Yii::$app->user->isGuest && (int) \Yii::$app->user->id === (int) $model->getId()) {
                    \Yii::$app->user->logout(false);
                }
                \Yii::$app->session->setFlash('swal_password_changed', [
                    'icon'  => 'success',
                    'title' => 'Berhasil',
                    'text'  => 'Password berhasil diubah. Silakan klik OK untuk login kembali.',
                ]);
                return $this->redirect(['user-model/ubah-password']);
            }

            \Yii::$app->session->setFlash('error', 'Gagal menyimpan password baru');
            return $this->redirect(['user-model/ubah-password']);
        }

        return $this->render('@app/views/data_user_akses/ubah-password', ['user_id' => $userId]);
    }

    protected function findModel($id)
    {
        $id = $this->decodeId($id);

        if ($id && ($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionGetMasterWilayah($level = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!$level) return [];
        return $this->getMasterWilayahOptions($level);
    }

    public function actionGetMasterWilayahDetail($id = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!$id) return ['success' => false, 'message' => 'ID wilayah tidak ditemukan'];

        $wilayah = MasterWilayah::findOne((int) $id);
        if (!$wilayah) return ['success' => false, 'message' => 'Data wilayah tidak ditemukan'];

        return [
            'success' => true,
            'data'    => [
                'id'              => $wilayah->id,
                'nama_wilayah'    => $wilayah->nama_wilayah,
                'level_wilayah'   => $wilayah->level_wilayah,
                'username_legacy' => $wilayah->username_legacy,
                'email_legacy'    => $wilayah->email_legacy,
            ],
        ];
    }

    protected function getLevelOptions(): array
    {
        return LevelUser::find()
            ->select(['nama_level', 'id'])
            ->where(['is_active' => true])
            ->orderBy(['id' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }

    protected function getMasterWilayahOptions($level): array
    {
        $list = [];
        if (!$level || $this->isWilayahOptionalForLevel($level)) return $list;
        try {
            $rows = MasterWilayah::find()
                ->select(['id', 'nama_wilayah'])
                ->where(['level_wilayah' => (int) $level])
                ->orderBy(['nama_wilayah' => SORT_ASC])
                ->asArray()
                ->all();
            foreach ($rows as $row) {
                $list[$row['id']] = $row['nama_wilayah'];
            }
        } catch (\Throwable $e) {
            Yii::warning('Gagal memuat master wilayah: ' . $e->getMessage(), __METHOD__);
        }
        return $list;
    }

    protected function isWilayahOptionalForLevel($level): bool
    {
        return $this->isSuperAdminLevel($level) || $this->isMasyarakatLevel($level);
    }

    protected function isValidWilayahSelection($masterWilayahId, $level): bool
    {
        if (!$masterWilayahId) return $this->isWilayahOptionalForLevel($level);

        $expectedWilayahLevel = $this->getExpectedWilayahLevelForUserLevel($level);
        if ($expectedWilayahLevel === null) return $this->isWilayahOptionalForLevel($level);

        return MasterWilayah::find()
            ->where(['id' => (int) $masterWilayahId, 'level_wilayah' => $expectedWilayahLevel])
            ->exists();
    }

    protected function syncUserLegacyFields(User $model, $levelUserId, $masterWilayahId): void
    {
        if ($model->hasAttribute('id_user_level'))     $model->id_user_level     = $levelUserId     ?: null;
        if ($model->hasAttribute('level_user_id'))     $model->level_user_id     = $levelUserId     ?: null;
        if ($model->hasAttribute('master_wilayah_id')) $model->master_wilayah_id = $masterWilayahId ?: null;
    }

    protected function resolveMasterWilayahIdFromSelection($levelUserId, $masterWilayahId, $kdProp, $kdKab)
    {
        if ($masterWilayahId) return (int) $masterWilayahId;
        if ($this->isKabKotaLevel($levelUserId))    return $kdKab  ? (int) $kdKab  : null;
        if ($this->isProvinsiLevel($levelUserId))   return $kdProp ? (int) $kdProp : null;
        if ($this->isMasyarakatLevel($levelUserId)) return $kdKab  ? (int) $kdKab  : ($kdProp ? (int) $kdProp : null);
        return null;
    }

    protected function getExpectedWilayahLevelForUserLevel($level): ?int
    {
        if ($this->isProvinsiLevel($level))                                         return 2;
        if ($this->isKabKotaLevel($level) || $this->isMasyarakatLevel($level))     return 3;
        return null;
    }

    protected function isSuperAdminLevel($level): bool
    {
        if ((int) $level === 1) return true;
        $n = $this->getLevelNameById($level);
        return $n !== null && strpos($n, 'super admin') !== false;
    }

    protected function isProvinsiLevel($level): bool
    {
        $n = $this->getLevelNameById($level);
        return $n !== null && strpos($n, 'provinsi') !== false;
    }

    protected function isKabKotaLevel($level): bool
    {
        $n = $this->getLevelNameById($level);
        return $n !== null && (strpos($n, 'kab') !== false || strpos($n, 'kota') !== false);
    }

    protected function isMasyarakatLevel($level): bool
    {
        $n = $this->getLevelNameById($level);
        return $n !== null && strpos($n, 'masyarakat') !== false;
    }

    protected function getLevelNameById($level): ?string
    {
        if (!$level) return null;
        try {
            $m = LevelUser::findOne((int) $level);
            if ($m && isset($m->nama_level)) return strtolower(trim((string) $m->nama_level));
        } catch (\Throwable $e) {
            Yii::warning('Error checking level name: ' . $e->getMessage(), __METHOD__);
        }
        return null;
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

    public function getProvinsiOptions(): array
    {
        $list = [];
        try {
            $rows = MasterWilayah::find()
                ->select(['id', 'nama_wilayah'])
                ->where(['level_wilayah' => 2])
                ->orderBy(['nama_wilayah' => SORT_ASC])
                ->asArray()->all();
            foreach ($rows as $row) $list[$row['id']] = $row['nama_wilayah'];
        } catch (\Throwable $e) {
            Yii::warning('Gagal memuat provinsi: ' . $e->getMessage(), __METHOD__);
        }
        return $list;
    }

    public function getKabupatenByProvince($provinsiId): array
    {
        $list = [];
        if (!$provinsiId) return $list;
        try {
            $rows = MasterWilayah::find()
                ->select(['id', 'nama_wilayah'])
                ->where(['level_wilayah' => 3, 'parent_master_wilayah_id' => (int) $provinsiId])
                ->orderBy(['nama_wilayah' => SORT_ASC])
                ->asArray()->all();
            foreach ($rows as $row) $list[$row['id']] = $row['nama_wilayah'];
        } catch (\Throwable $e) {
            Yii::warning('Gagal memuat kabupaten: ' . $e->getMessage(), __METHOD__);
        }
        return $list;
    }

    public function actionGetProvinsi()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $this->getProvinsiOptions();
    }

    public function actionGetKabupaten($provinsi_id = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!$provinsi_id) return [];
        return $this->getKabupatenByProvince($provinsi_id);
    }

    public function actionMigrateWilayahData()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (\Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Harus login sebagai Super Admin'];
        }

        $currentUser = User::findOne(\Yii::$app->user->id);
        if (!$currentUser || !($currentUser->id_user_level == 1 || $currentUser->level_user_id == 1)) {
            return ['success' => false, 'message' => 'Hanya Super Admin yang bisa melakukan migrasi'];
        }

        try {
            $db        = User::getDb();
            $userTable = User::tableName();
            $users     = User::find()
                ->where(['not', ['master_wilayah_id' => null]])
                ->orWhere(['is', 'kd_prop', null])
                ->asArray()->all();

            $updated = $skipped = 0;
            foreach ($users as $user) {
                $mwId = $user['master_wilayah_id'];
                if (!$mwId) continue;

                $wilayah = MasterWilayah::findOne($mwId);
                if (!$wilayah) { $skipped++; continue; }

                $updateData = [];
                if ($wilayah->level_wilayah == 1) {
                    $updateData['kd_prop'] = $mwId;
                } elseif ($wilayah->level_wilayah == 2) {
                    $updateData['kd_kab']  = $mwId;
                    $updateData['kd_prop'] = $wilayah->parent_master_wilayah_id;
                } else {
                    $skipped++; continue;
                }

                $db->createCommand()->update($userTable, $updateData, ['id' => $user['id']])->execute();
                $updated++;
            }

            return [
                'success' => true,
                'message' => "Migrasi berhasil: $updated user diupdate, $skipped dilewati",
                'updated' => $updated,
                'skipped' => $skipped,
            ];

        } catch (\Exception $e) {
            \Yii::error('Migration error: ' . $e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

}