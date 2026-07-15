<?php

namespace app\controllers;

use app\models\level_user\LevelUser;
use app\models\HakAkses;
use app\models\Modul;
use app\models\SubModul;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;

/**
 * LevelUserController implements the CRUD actions for LevelUser model.
 */
class LevelUserController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Get list of level users (JSON for AJAX/DataTables)
     */
    public function actionGetList()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $levelUsers = LevelUser::find()->orderBy(['id' => SORT_DESC])->all();

        $data = [];
        foreach ($levelUsers as $level) {
            // Hitung jumlah user yang menggunakan level ini
            $jumlahUser = \app\models\User::find()->where(['level_user_id' => $level->id])->count();

            $data[] = [
                'id' => $level->id,
                'nama_level' => $level->nama_level,
                'deskripsi' => $level->deskripsi,
                'jumlah_user' => $jumlahUser,
                'is_active' => $level->is_active ?? true,
            ];
        }

        return ['success' => true, 'data' => $data];
    }

    /**
     * Lists all LevelUser models.
     */
    public function actionIndex()
    {
        $query = LevelUser::find()->orderBy(['id' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single LevelUser model.
     */
    public function actionView($id)
    {
        // If AJAX request, return JSON
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $model = LevelUser::findOne($id);

            if (!$model) {
                return ['success' => false, 'message' => 'Level user tidak ditemukan'];
            }

            $jumlahUser = \app\models\User::find()->where(['level_user_id' => $model->id])->count();

            return [
                'success' => true,
                'data' => [
                    'id' => $model->id,
                    'nama_level' => $model->nama_level,
                    'deskripsi' => $model->deskripsi,
                    'jumlah_user' => $jumlahUser,
                    'is_active' => $model->is_active ?? true,
                ]
            ];
        }

        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new LevelUser model.
     */
    public function actionCreate()
    {
        // Support AJAX request
        if (\Yii::$app->request->isPost && \Yii::$app->request->isAjax) {
            return $this->actionSave();
        }

        $model = new LevelUser();

        if ($this->request->isPost) {
            $post = $this->request->post();

            if (isset($post['LevelUser'])) {
                $model->load($post);
            } else {
                $model->setAttributes($post, false);
            }

            if ($model->save()) {
                \Yii::$app->session->setFlash('success', 'Level user berhasil ditambahkan.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing LevelUser model.
     */
    public function actionUpdate($id)
    {
        // Support AJAX request
        if (\Yii::$app->request->isPost && \Yii::$app->request->isAjax) {
            return $this->actionSave();
        }

        $model = $this->findModel($id);

        if ($this->request->isPost) {
            $post = $this->request->post();

            if (isset($post['LevelUser'])) {
                $model->load($post);
            } else {
                $model->setAttributes($post, false);
            }

            if ($model->save()) {
                \Yii::$app->session->setFlash('success', 'Level user berhasil diperbarui.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * AJAX Save handler (compatibility with old UI)
     */
    public function actionSave()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req = \Yii::$app->request;

        if (!$req->isPost) {
            return ['success' => false, 'message' => 'Invalid request method'];
        }

        try {
            $id = $req->post('id');
            $namaLevel = $req->post('nama_level');
            $deskripsi = $req->post('deskripsi');
            $isActive = $req->post('is_active', 1);

            // Validasi
            if (!$namaLevel) {
                return ['success' => false, 'message' => 'Nama level tidak boleh kosong'];
            }

            if (!$id) {
                // CREATE
                // Cek nama level sudah ada
                $existing = LevelUser::findOne(['nama_level' => $namaLevel]);
                if ($existing) {
                    return ['success' => false, 'message' => 'Nama level sudah terdaftar'];
                }

                $model = new LevelUser();
            } else {
                // UPDATE
                $model = LevelUser::findOne($id);
                if (!$model) {
                    return ['success' => false, 'message' => 'Level user tidak ditemukan'];
                }

                // Cek nama level sudah ada (selain level ini)
                $existing = LevelUser::findOne(['nama_level' => $namaLevel]);
                if ($existing && $existing->id !== $model->id) {
                    return ['success' => false, 'message' => 'Nama level sudah terdaftar'];
                }
            }

            $model->nama_level = $namaLevel;
            $model->deskripsi = $deskripsi;
            $model->is_active = (bool)$isActive;

            if ($model->save()) {
                return [
                    'success' => true,
                    'message' => $id ? 'Level user berhasil diperbarui' : 'Level user berhasil ditambahkan',
                    'data' => [
                        'id' => $model->id,
                        'nama_level' => $model->nama_level
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan level user: ' . implode(', ', $model->getFirstErrors())
                ];
            }
        } catch (\Exception $e) {
            \Yii::error('LevelUserController::actionSave - ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Set hak akses for an existing LevelUser model.
     */
    public function actionHakAkses($id)
    {
        $model = $this->findModel($id);
        return $this->render('hak_akses', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing LevelUser model.
     */
    public function actionDelete($id)
    {
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            try {
                $db = \app\models\level_user\LevelUser::getDb();
                $tx = $db->beginTransaction();
                try {
                    $jumlahUser = \app\models\User::find()->where(['level_user_id' => $id])->count();
                    \app\models\HakAkses::deleteAll(['level_user_id' => $id]);
                    \app\models\User::deleteAll(['level_user_id' => $id]);
                    $this->findModel($id)->delete();

                    $tx->commit();
                    $msg = $jumlahUser > 0
                        ? 'Data berhasil dihapus. ' . $jumlahUser . ' user terkait juga dihapus.'
                        : 'Data berhasil dihapus.';
                    return ['success' => true, 'message' => $msg];
                } catch (\Exception $e) {
                    $tx->rollBack();
                    throw $e;
                }
            } catch (\Exception $e) {
                return ['success' => false, 'message' => 'Gagal menghapus data: ' . $e->getMessage()];
            }
        }

        $db = \app\models\level_user\LevelUser::getDb();
        $tx = $db->beginTransaction();
        try {
            $jumlahUser = \app\models\User::find()->where(['level_user_id' => $id])->count();
            \app\models\HakAkses::deleteAll(['level_user_id' => $id]);
            \app\models\User::deleteAll(['level_user_id' => $id]);
            $this->findModel($id)->delete();
            $tx->commit();
            $msg = $jumlahUser > 0
                ? 'Data berhasil dihapus. ' . $jumlahUser . ' user terkait juga dihapus.'
                : 'Data berhasil dihapus.';
            \Yii::$app->session->setFlash('success', $msg);
        } catch (\Exception $e) {
            $tx->rollBack();
            \Yii::$app->session->setFlash('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
        return $this->redirect(['index']);
    }

    /**
     * Get module structure with sub-modules (JSON)
     */
    public function actionGetModulStructure()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $navigasis = Modul::find()
            ->where(['is_active' => true])
            ->orderBy(['urutan' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $data = [];
        foreach ($navigasis as $navigasi) {
            $items = SubModul::find()
                ->where(['modul_id' => $navigasi->id, 'is_active' => true])
                ->orderBy(['urutan' => SORT_ASC, 'id' => SORT_ASC])
                ->all();

            $modules = [];
            $childrenByParent = [];

            foreach ($items as $item) {
                if ($item->parent_id === null) {
                    $modules[$item->id] = [
                        'id' => $item->id,
                        'modul_id' => $navigasi->id,
                        'nama_sub_modul' => $item->nama_sub_modul,
                        'label' => $item->label,
                        'route' => $item->route,
                        'icon' => $item->icon,
                        'urutan' => $item->urutan,
                        'children' => [],
                    ];
                    continue;
                }

                $childrenByParent[$item->parent_id][] = [
                    'id' => $item->id,
                    'modul_id' => $navigasi->id,
                    'nama_sub_modul' => $item->nama_sub_modul,
                    'label' => $item->label,
                    'route' => $item->route,
                    'icon' => $item->icon,
                    'urutan' => $item->urutan,
                    'parent_id' => $item->parent_id,
                ];
            }

            foreach ($childrenByParent as $parentId => $children) {
                if (isset($modules[$parentId])) {
                    $modules[$parentId]['children'] = $children;
                }
            }

            $data[] = [
                'id' => $navigasi->id,
                'nama_modul' => $navigasi->nama_modul,
                'label' => $navigasi->label,
                'icon' => $navigasi->icon,
                'modules' => array_values($modules),
            ];
        }

        return ['success' => true, 'data' => $data];
    }

    /**
     * Get hak akses for a level user (JSON)
     */
    public function actionGetHakAkses($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $levelUser = LevelUser::findOne($id);
        if (!$levelUser) {
            return ['success' => false, 'message' => 'Level user tidak ditemukan'];
        }

        $hakAkses = HakAkses::find()
            ->where(['level_user_id' => $id])
            ->all();

        $data = [];
        foreach ($hakAkses as $hak) {
            $data[] = [
                'id' => $hak->id,
                'modul_id' => $hak->modul_id,
                'sub_modul_id' => $hak->sub_modul_id,
                'can_view' => (bool)$hak->can_view,
                'can_create' => (bool)$hak->can_create,
                'can_update' => (bool)$hak->can_update,
                'can_delete' => (bool)$hak->can_delete,
            ];
        }

        return ['success' => true, 'data' => $data];
    }

    /**
     * Save hak akses for a level user
     */
    public function actionSaveHakAkses()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req = \Yii::$app->request;

        if (!$req->isPost) {
            return ['success' => false, 'message' => 'Invalid request method'];
        }

        try {
            $levelUserId = $req->post('level_user_id');
            $permissions = $req->post('permissions', []); // Array of permission objects

            if (!$levelUserId) {
                return ['success' => false, 'message' => 'Level user ID tidak boleh kosong'];
            }

            $levelUser = LevelUser::findOne($levelUserId);
            if (!$levelUser) {
                return ['success' => false, 'message' => 'Level user tidak ditemukan'];
            }

            // Hak akses disimpan di db_user, jadi transaksi harus pakai koneksi yang sama.
            $transaction = HakAkses::getDb()->beginTransaction();

            try {
                // Hapus semua hak akses lama untuk level ini
                HakAkses::deleteAll(['level_user_id' => $levelUserId]);

                // Simpan hak akses baru
                foreach ($permissions as $perm) {
                    // Skip jika tidak ada modul_id atau sub_modul_id
                    if (empty($perm['sub_modul_id'])) {
                        continue;
                    }

                    $hakAkses = new HakAkses();
                    $hakAkses->level_user_id = $levelUserId;
                    
                    // Set sub_modul_id (primary for new system)
                    $hakAkses->sub_modul_id = $perm['sub_modul_id'];
                    
                    // Set modul_id if provided (for backwards compatibility)
                    if (!empty($perm['modul_id'])) {
                        $hakAkses->modul_id = $perm['modul_id'];
                    }

                    $isTrue = function($val) {
                        return $val === true || $val === 'true' || $val === 1 || $val === '1';
                    };
                    $granted = isset($perm['granted']) && $isTrue($perm['granted']);
                    $hakAkses->can_view = isset($perm['can_view']) ? $isTrue($perm['can_view']) : $granted;
                    $hakAkses->can_create = isset($perm['can_create']) ? $isTrue($perm['can_create']) : $granted;
                    $hakAkses->can_update = isset($perm['can_update']) ? $isTrue($perm['can_update']) : $granted;
                    $hakAkses->can_delete = isset($perm['can_delete']) ? $isTrue($perm['can_delete']) : $granted;

                    if (!$hakAkses->save()) {
                        throw new \Exception('Gagal menyimpan hak akses: ' . implode(', ', $hakAkses->getFirstErrors()));
                    }
                }

                $transaction->commit();

                return [
                    'success' => true,
                    'message' => 'Hak akses berhasil disimpan'
                ];
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Yii::error('LevelUserController::actionSaveHakAkses - ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Finds the LevelUser model based on its primary key value.
     */
    protected function findModel($id)
    {
        if (($model = LevelUser::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
