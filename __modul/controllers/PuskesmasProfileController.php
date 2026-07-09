<?php

namespace app\controllers;

use app\components\TimeHelper;
use Yii;
use app\models\PuskesmasProfile;
use app\models\PuskesmasKinerja;
use app\models\PuskesmasPenyakit;
use app\services\WilayahService;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PuskesmasProfileController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'kinerja-delete' => ['POST'],
                    'penyakit-delete' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * Lists all Puskesmas profiles.
     */
    public function actionIndex()
    {
        $query = PuskesmasProfile::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => ['nama_puskesmas' => SORT_ASC],
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Puskesmas profile.
     */
    public function actionCreate()
    {
        $model = new PuskesmasProfile();
        $wilayahService = new WilayahService();

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $file = UploadedFile::getInstance($model, 'foto_puskesmas');
            if ($file) {
                if ($file->size <= 2 * 1024 * 1024 && in_array(strtolower($file->extension), ['jpg', 'jpeg', 'png'], true)) {
                    $dir = Yii::getAlias('@app/../uploads/puskesmas/');
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $fileName = uniqid() . '.' . $file->extension;
                    $filePath = $dir . $fileName;
                    if ($file->saveAs($filePath)) {
                        $model->foto_puskesmas = 'uploads/puskesmas/' . $fileName;
                    }
                } else {
                    $model->addError('foto_puskesmas', 'File foto harus berupa JPG, JPEG, atau PNG dan ukuran maksimal 2MB.');
                }
            }

            if (!$model->hasErrors() && $model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Profil Puskesmas berhasil disimpan.',
                ]);
                return $this->redirect(['index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'wilayahService' => $wilayahService,
        ]);
    }

    /**
     * Updates an existing Puskesmas profile.
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $wilayahService = new WilayahService();
        $oldPhoto = $model->foto_puskesmas;

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $file = UploadedFile::getInstance($model, 'foto_puskesmas');
            if ($file) {
                if ($file->size <= 2 * 1024 * 1024 && in_array(strtolower($file->extension), ['jpg', 'jpeg', 'png'], true)) {
                    $dir = Yii::getAlias('@app/../uploads/puskesmas/');
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $fileName = uniqid() . '.' . $file->extension;
                    $filePath = $dir . $fileName;
                    if ($file->saveAs($filePath)) {
                        if (!empty($oldPhoto)) {
                            $oldPath = Yii::getAlias('@app/../') . ltrim($oldPhoto, '/');
                            if (file_exists($oldPath) && is_file($oldPath)) {
                                @unlink($oldPath);
                            }
                        }
                        $model->foto_puskesmas = 'uploads/puskesmas/' . $fileName;
                    }
                } else {
                    $model->addError('foto_puskesmas', 'File foto harus berupa JPG, JPEG, atau PNG dan ukuran maksimal 2MB.');
                }
            } else {
                $model->foto_puskesmas = $oldPhoto;
            }

            if (!$model->hasErrors() && $model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Profil Puskesmas berhasil diupdate.',
                ]);
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'wilayahService' => $wilayahService,
        ]);
    }

    /**
     * Deletes an existing Puskesmas profile.
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('swal', [
            'icon' => 'success',
            'title' => 'Berhasil',
            'text' => 'Puskesmas berhasil dihapus.',
        ]);
        return $this->redirect(['index']);
    }

    /**
     * Manage performance reports for a specific Puskesmas.
     */
    public function actionKinerja($id)
    {
        $puskesmas = $this->findModel($id);
        $query = PuskesmasKinerja::find()->where(['puskesmas_id' => $id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['tahun' => SORT_DESC, 'periode_nilai' => SORT_DESC],
            ]
        ]);

        return $this->render('kinerja-index', [
            'puskesmas' => $puskesmas,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Create performance report for a specific Puskesmas.
     */
    public function actionKinerjaCreate($id)
    {
        $puskesmas = $this->findModel($id);
        $model = new PuskesmasKinerja();
        $model->puskesmas_id = $id;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                // Calculate PKP average
                $model->skor_pkp_total = ($model->skor_pkp_klaster1 + $model->skor_pkp_klaster2 + $model->skor_pkp_klaster3 + $model->skor_pkp_klaster4 + $model->skor_pkp_lintas_klaster) / 5;
                if ($model->save()) {
                    Yii::$app->session->setFlash('swal', [
                        'icon' => 'success',
                        'title' => 'Berhasil Disimpan',
                        'text' => 'Laporan kinerja berhasil dibuat. Lanjutkan melengkapi data pada tab berikutnya.',
                    ]);
                    // Redirect to update form so user can continue filling in all tabs
                    return $this->redirect(['kinerja-update', 'id' => $id, 'kinerja_id' => $model->id]);
                }
            }
        } else {
            $model->tahun = TimeHelper::year();
            $model->periode_tipe = 'Tahunan';
            $model->periode_nilai = TimeHelper::year();
        }

        return $this->render('kinerja-create', [
            'puskesmas' => $puskesmas,
            'model' => $model,
        ]);
    }

    /**
     * Update performance report.
     */
    public function actionKinerjaUpdate($id, $kinerja_id)
    {
        $puskesmas = $this->findModel($id);
        $model = PuskesmasKinerja::findOne(['id' => $kinerja_id, 'puskesmas_id' => $id]);
        if (!$model) {
            throw new NotFoundHttpException('Laporan kinerja tidak ditemukan.');
        }

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->skor_pkp_total = ($model->skor_pkp_klaster1 + $model->skor_pkp_klaster2 + $model->skor_pkp_klaster3 + $model->skor_pkp_klaster4 + $model->skor_pkp_lintas_klaster) / 5;
                if ($model->save()) {
                    Yii::$app->session->setFlash('swal', [
                        'icon' => 'success',
                        'title' => 'Berhasil',
                        'text' => 'Laporan kinerja berhasil diupdate.',
                    ]);
                    return $this->redirect(['kinerja', 'id' => $id]);
                }
            }
        }

        return $this->render('kinerja-update', [
            'puskesmas' => $puskesmas,
            'model' => $model,
        ]);
    }

    /**
     * Delete performance report.
     */
    public function actionKinerjaDelete($id, $kinerja_id)
    {
        $model = PuskesmasKinerja::findOne(['id' => $kinerja_id, 'puskesmas_id' => $id]);
        if ($model) {
            $model->delete();
            Yii::$app->session->setFlash('swal', [
                'icon' => 'success',
                'title' => 'Berhasil',
                'text' => 'Laporan kinerja berhasil dihapus.',
            ]);
        }
        return $this->redirect(['kinerja', 'id' => $id]);
    }

    /**
     * Manage SDM / Nakes details for a performance report.
     */
    public function actionNakes($id, $kinerja_id)
    {
        $puskesmas = $this->findModel($id);
        $kinerja = PuskesmasKinerja::findOne(['id' => $kinerja_id, 'puskesmas_id' => $id]);
        if (!$kinerja) {
            throw new NotFoundHttpException('Laporan kinerja tidak ditemukan.');
        }

        $masterNakes = (new \yii\db\Query())->from('master_nakes')->orderBy(['id' => SORT_ASC])->all();
        $db = Yii::$app->db;

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post('nakes_qty', []);
            foreach ($post as $nakesId => $qty) {
                $qty = (int)$qty;
                $db->createCommand("
                    INSERT INTO public.puskesmas_nakes_detail (puskesmas_id, tahun, periode_tipe, periode_nilai, nakes_id, jumlah)
                    VALUES (:pusk_id, :tahun, :tipe, :nilai, :nakes_id, :qty)
                    ON CONFLICT (puskesmas_id, tahun, periode_tipe, periode_nilai, nakes_id) DO UPDATE
                    SET jumlah = EXCLUDED.jumlah
                ", [
                    ':pusk_id' => $id,
                    ':tahun' => $kinerja->tahun,
                    ':tipe' => $kinerja->periode_tipe,
                    ':nilai' => $kinerja->periode_nilai,
                    ':nakes_id' => $nakesId,
                    ':qty' => $qty
                ])->execute();
            }

            // Calculate completeness
            $details = $db->createCommand("
                SELECT nd.nakes_id, nd.jumlah, mn.is_dli_9, mn.is_dli_11
                FROM public.puskesmas_nakes_detail nd
                JOIN public.master_nakes mn ON mn.id = nd.nakes_id
                WHERE nd.puskesmas_id = :pusk_id AND nd.tahun = :tahun AND nd.periode_tipe = :tipe AND nd.periode_nilai = :nilai
            ", [
                ':pusk_id' => $id,
                ':tahun' => $kinerja->tahun,
                ':tipe' => $kinerja->periode_tipe,
                ':nilai' => $kinerja->periode_nilai,
            ])->queryAll();

            $dli9Complete = true;
            $dli11Complete = true;

            foreach ($masterNakes as $n) {
                $nId = (int)$n['id'];
                $isDli9 = filter_var($n['is_dli_9'], FILTER_VALIDATE_BOOLEAN);
                $isDli11 = filter_var($n['is_dli_11'], FILTER_VALIDATE_BOOLEAN);

                $foundQty = 0;
                foreach ($details as $d) {
                    if ((int)$d['nakes_id'] === $nId) {
                        $foundQty = (int)$d['jumlah'];
                        break;
                    }
                }

                if ($isDli9 && $foundQty <= 0) {
                    $dli9Complete = false;
                }
                if ($isDli11 && $foundQty <= 0) {
                    $dli11Complete = false;
                }
            }

            $kinerja->nakes_9_jenis = $dli9Complete;
            $kinerja->nakes_11_jenis = $dli11Complete;
            $kinerja->save(false);

            Yii::$app->session->setFlash('swal', [
                'icon' => 'success',
                'title' => 'Berhasil',
                'text' => 'Data SDM Kesehatan berhasil diperbarui.',
            ]);
            return $this->redirect(['kinerja', 'id' => $id]);
        }

        // Fetch current values
        $currentValues = [];
        $currentRows = $db->createCommand("
            SELECT nakes_id, jumlah FROM public.puskesmas_nakes_detail
            WHERE puskesmas_id = :pusk_id AND tahun = :tahun AND periode_tipe = :tipe AND periode_nilai = :nilai
        ", [
            ':pusk_id' => $id,
            ':tahun' => $kinerja->tahun,
            ':tipe' => $kinerja->periode_tipe,
            ':nilai' => $kinerja->periode_nilai,
        ])->queryAll();

        foreach ($currentRows as $row) {
            $currentValues[(int)$row['nakes_id']] = (int)$row['jumlah'];
        }

        return $this->render('nakes', [
            'puskesmas' => $puskesmas,
            'kinerja' => $kinerja,
            'masterNakes' => $masterNakes,
            'currentValues' => $currentValues,
        ]);
    }

    /**
     * Manage Obat details for a performance report.
     */
    public function actionObat($id, $kinerja_id)
    {
        $puskesmas = $this->findModel($id);
        $kinerja = PuskesmasKinerja::findOne(['id' => $kinerja_id, 'puskesmas_id' => $id]);
        if (!$kinerja) {
            throw new NotFoundHttpException('Laporan kinerja tidak ditemukan.');
        }

        $masterObat = (new \yii\db\Query())->from('master_obat')->orderBy(['id' => SORT_ASC])->all();
        $db = Yii::$app->db;

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post('obat_available', []);
            
            // Delete old details for this period
            $db->createCommand("
                DELETE FROM public.puskesmas_obat_detail 
                WHERE puskesmas_id = :pusk_id AND tahun = :tahun AND periode_tipe = :tipe AND periode_nilai = :nilai
            ", [
                ':pusk_id' => $id,
                ':tahun' => $kinerja->tahun,
                ':tipe' => $kinerja->periode_tipe,
                ':nilai' => $kinerja->periode_nilai,
            ])->execute();

            $availableCount = 0;
            foreach ($masterObat as $o) {
                $oId = (int)$o['id'];
                $isAvailable = isset($post[$oId]) && (bool)$post[$oId];
                if ($isAvailable) {
                    $availableCount++;
                }

                $db->createCommand("
                    INSERT INTO public.puskesmas_obat_detail (puskesmas_id, tahun, periode_tipe, periode_nilai, obat_id, is_tersedia)
                    VALUES (:pusk_id, :tahun, :tipe, :nilai, :obat_id, :avail)
                ", [
                    ':pusk_id' => $id,
                    ':tahun' => $kinerja->tahun,
                    ':tipe' => $kinerja->periode_tipe,
                    ':nilai' => $kinerja->periode_nilai,
                    ':obat_id' => $oId,
                    ':avail' => $isAvailable ? 'true' : 'false'
                ])->execute();
            }

            // Update main kinerja record
            $kinerja->jumlah_obat_esensial = $availableCount;
            $kinerja->persen_obat_esensial = count($masterObat) > 0 ? ($availableCount / count($masterObat)) * 100 : 0;
            $kinerja->save(false);

            Yii::$app->session->setFlash('swal', [
                'icon' => 'success',
                'title' => 'Berhasil',
                'text' => 'Data Ketersediaan Obat Esensial diperbarui.',
            ]);
            return $this->redirect(['kinerja', 'id' => $id]);
        }

        // Fetch current values
        $currentValues = [];
        $currentRows = $db->createCommand("
            SELECT obat_id, is_tersedia FROM public.puskesmas_obat_detail
            WHERE puskesmas_id = :pusk_id AND tahun = :tahun AND periode_tipe = :tipe AND periode_nilai = :nilai
        ", [
            ':pusk_id' => $id,
            ':tahun' => $kinerja->tahun,
            ':tipe' => $kinerja->periode_tipe,
            ':nilai' => $kinerja->periode_nilai,
        ])->queryAll();

        foreach ($currentRows as $row) {
            $currentValues[(int)$row['obat_id']] = filter_var($row['is_tersedia'], FILTER_VALIDATE_BOOLEAN);
        }

        return $this->render('obat', [
            'puskesmas' => $puskesmas,
            'kinerja' => $kinerja,
            'masterObat' => $masterObat,
            'currentValues' => $currentValues,
        ]);
    }

    /**
     * Manage Alkes details for a performance report.
     */
    public function actionAlkes($id, $kinerja_id)
    {
        $puskesmas = $this->findModel($id);
        $kinerja = PuskesmasKinerja::findOne(['id' => $kinerja_id, 'puskesmas_id' => $id]);
        if (!$kinerja) {
            throw new NotFoundHttpException('Laporan kinerja tidak ditemukan.');
        }

        $masterAlkes = (new \yii\db\Query())->from('master_alkes')->orderBy(['id' => SORT_ASC])->all();
        $db = Yii::$app->db;

        if (Yii::$app->request->isPost) {
            $postAvail = Yii::$app->request->post('alkes_available', []);
            $postBaik = Yii::$app->request->post('alkes_baik', []);

            // Delete old details
            $db->createCommand("
                DELETE FROM public.puskesmas_alkes_detail 
                WHERE puskesmas_id = :pusk_id AND tahun = :tahun AND periode_tipe = :tipe AND periode_nilai = :nilai
            ", [
                ':pusk_id' => $id,
                ':tahun' => $kinerja->tahun,
                ':tipe' => $kinerja->periode_tipe,
                ':nilai' => $kinerja->periode_nilai,
            ])->execute();

            $availableCount = 0;
            foreach ($masterAlkes as $a) {
                $aId = (int)$a['id'];
                $isAvailable = isset($postAvail[$aId]) && (bool)$postAvail[$aId];
                $isBaik = isset($postBaik[$aId]) && (bool)$postBaik[$aId];

                if ($isAvailable && $isBaik) {
                    $availableCount++;
                }

                $db->createCommand("
                    INSERT INTO public.puskesmas_alkes_detail (puskesmas_id, tahun, periode_tipe, periode_nilai, alkes_id, is_tersedia, kondisi_baik)
                    VALUES (:pusk_id, :tahun, :tipe, :nilai, :alkes_id, :avail, :baik)
                ", [
                    ':pusk_id' => $id,
                    ':tahun' => $kinerja->tahun,
                    ':tipe' => $kinerja->periode_tipe,
                    ':nilai' => $kinerja->periode_nilai,
                    ':alkes_id' => $aId,
                    ':avail' => $isAvailable ? 'true' : 'false',
                    ':baik' => $isBaik ? 'true' : 'false'
                ])->execute();
            }

            // Update main record
            $kinerja->persen_alkes = count($masterAlkes) > 0 ? ($availableCount / count($masterAlkes)) * 100 : 0;
            $kinerja->save(false);

            Yii::$app->session->setFlash('swal', [
                'icon' => 'success',
                'title' => 'Berhasil',
                'text' => 'Data Ketersediaan Alkes diperbarui.',
            ]);
            return $this->redirect(['kinerja', 'id' => $id]);
        }

        // Fetch current values
        $currentAvail = [];
        $currentBaik = [];
        $currentRows = $db->createCommand("
            SELECT alkes_id, is_tersedia, kondisi_baik FROM public.puskesmas_alkes_detail
            WHERE puskesmas_id = :pusk_id AND tahun = :tahun AND periode_tipe = :tipe AND periode_nilai = :nilai
        ", [
            ':pusk_id' => $id,
            ':tahun' => $kinerja->tahun,
            ':tipe' => $kinerja->periode_tipe,
            ':nilai' => $kinerja->periode_nilai,
        ])->queryAll();

        foreach ($currentRows as $row) {
            $alkesId = (int)$row['alkes_id'];
            $currentAvail[$alkesId] = filter_var($row['is_tersedia'], FILTER_VALIDATE_BOOLEAN);
            $currentBaik[$alkesId] = filter_var($row['kondisi_baik'], FILTER_VALIDATE_BOOLEAN);
        }

        return $this->render('alkes', [
            'puskesmas' => $puskesmas,
            'kinerja' => $kinerja,
            'masterAlkes' => $masterAlkes,
            'currentAvail' => $currentAvail,
            'currentBaik' => $currentBaik,
        ]);
    }

    /**
     * Manage Top 10 Diseases.
     */
    public function actionPenyakit($id)
    {
        $puskesmas = $this->findModel($id);
        $query = PuskesmasPenyakit::find()->where(['puskesmas_id' => $id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['tahun' => SORT_DESC, 'bulan' => SORT_DESC, 'ranking' => SORT_ASC],
            ]
        ]);

        $model = new PuskesmasPenyakit();
        $model->puskesmas_id = $id;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $maxRank = (int)PuskesmasPenyakit::find()
                    ->where(['puskesmas_id' => $id, 'tahun' => $model->tahun, 'bulan' => $model->bulan])
                    ->max('ranking');
                $model->ranking = $maxRank + 1;
                
                if ($model->save()) {
                    Yii::$app->session->setFlash('swal', [
                        'icon' => 'success',
                        'title' => 'Berhasil',
                        'text' => 'Penyakit berhasil ditambahkan.',
                    ]);
                    return $this->redirect(['penyakit', 'id' => $id]);
                }
            }
        } else {
            $model->tahun = (int) TimeHelper::year();
            $model->bulan = (int) TimeHelper::month();
            $model->ranking = 1;
        }

        return $this->render('penyakit', [
            'puskesmas' => $puskesmas,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }

    /**
     * Delete Disease record.
     */
    public function actionPenyakitDelete($id, $penyakit_id)
    {
        $model = PuskesmasPenyakit::findOne(['id' => $penyakit_id, 'puskesmas_id' => $id]);
        if ($model) {
            $model->delete();
            Yii::$app->session->setFlash('swal', [
                'icon' => 'success',
                'title' => 'Berhasil',
                'text' => 'Penyakit berhasil dihapus.',
            ]);
        }
        return $this->redirect(['penyakit', 'id' => $id]);
    }

    /**
     * Bulk Excel Import for Puskesmas Kinerja.
     */
    public function actionImportKinerja()
    {
        if (Yii::$app->request->isPost) {
            $file = UploadedFile::getInstanceByName('excel_file');
            if ($file) {
                try {
                    $spreadsheet = IOFactory::load($file->tempName);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();

                    $successCount = 0;
                    $errorCount = 0;
                    $errors = [];

                    // Skip header row (index 0)
                    for ($i = 1; $i < count($rows); $i++) {
                        $row = $rows[$i];
                        if (empty($row[0]) || empty($row[1])) {
                            continue; // Skip empty rows
                        }

                        $kodeFaskes = trim((string)$row[0]);
                        $namaPusk = trim((string)$row[1]);
                        $tahun = (int)$row[2];
                        $periodeTipe = trim((string)$row[3]);
                        $periodeNilai = (int)$row[4];

                        // Find or Create Puskesmas Profile
                        $profile = PuskesmasProfile::findOne(['kode_faskes' => $kodeFaskes]);
                        if (!$profile) {
                            $profile = new PuskesmasProfile();
                            $profile->kode_faskes = $kodeFaskes;
                            $profile->nama_puskesmas = $namaPusk;
                            $profile->status_aktif = true;
                            if (!$profile->save()) {
                                $errorCount++;
                                $errors[] = "Baris " . ($i + 1) . ": Gagal membuat profil Puskesmas ($kodeFaskes - $namaPusk)";
                                continue;
                            }
                        }

                        // Find or Create Kinerja Record
                        $kinerja = PuskesmasKinerja::findOne([
                            'puskesmas_id' => $profile->id,
                            'tahun' => $tahun,
                            'periode_tipe' => $periodeTipe,
                            'periode_nilai' => $periodeNilai
                        ]);
                        if (!$kinerja) {
                            $kinerja = new PuskesmasKinerja();
                            $kinerja->puskesmas_id = $profile->id;
                            $kinerja->tahun = $tahun;
                            $kinerja->periode_tipe = $periodeTipe;
                            $kinerja->periode_nilai = $periodeNilai;
                        }

                        // Parse SDM/Alkes/BLUD/ILP/PKP/Pembiayaan fields from column 5 onwards
                        $kinerja->dokter_tersedia = filter_var($row[5] ?? false, FILTER_VALIDATE_BOOLEAN);
                        $kinerja->nakes_9_jenis = filter_var($row[6] ?? false, FILTER_VALIDATE_BOOLEAN);
                        $kinerja->nakes_11_jenis = filter_var($row[7] ?? false, FILTER_VALIDATE_BOOLEAN);
                        $kinerja->persen_alkes = (float)($row[8] ?? 0);
                        $kinerja->persen_spa = (float)($row[9] ?? 0);
                        $kinerja->jumlah_obat_esensial = (int)($row[10] ?? 0);
                        $kinerja->bmhp_tersedia = filter_var($row[11] ?? false, FILTER_VALIDATE_BOOLEAN);
                        $kinerja->status_blud = filter_var($row[12] ?? false, FILTER_VALIDATE_BOOLEAN);
                        $kinerja->status_ilp = filter_var($row[13] ?? false, FILTER_VALIDATE_BOOLEAN);
                        $kinerja->skor_pkp_klaster1 = (float)($row[14] ?? 0);
                        $kinerja->skor_pkp_klaster2 = (float)($row[15] ?? 0);
                        $kinerja->skor_pkp_klaster3 = (float)($row[16] ?? 0);
                        $kinerja->skor_pkp_klaster4 = (float)($row[17] ?? 0);
                        $kinerja->skor_pkp_lintas_klaster = (float)($row[18] ?? 0);
                        $kinerja->skor_pkp_total = ($kinerja->skor_pkp_klaster1 + $kinerja->skor_pkp_klaster2 + $kinerja->skor_pkp_klaster3 + $kinerja->skor_pkp_klaster4 + $kinerja->skor_pkp_lintas_klaster) / 5;
                        $kinerja->alokasi_bok = (float)($row[19] ?? 0);
                        $kinerja->realisasi_bok = (float)($row[20] ?? 0);

                        if ($kinerja->save()) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errs = implode(', ', array_map(function($e) { return $e[0]; }, $kinerja->getErrors()));
                            $errors[] = "Baris " . ($i + 1) . ": Gagal menyimpan kinerja ($errs)";
                        }
                    }

                    if ($errorCount > 0) {
                        Yii::$app->session->setFlash('swal', [
                            'icon' => 'warning',
                            'title' => 'Import Selesai dengan Peringatan',
                            'text' => "$successCount data berhasil di-import, $errorCount gagal. Detail: " . implode(" | ", array_slice($errors, 0, 3)),
                        ]);
                    } else {
                        Yii::$app->session->setFlash('swal', [
                            'icon' => 'success',
                            'title' => 'Import Sukses',
                            'text' => "Semua ($successCount) data kinerja berhasil di-import.",
                        ]);
                    }

                } catch (\Throwable $e) {
                    Yii::$app->session->setFlash('swal', [
                        'icon' => 'error',
                        'title' => 'Error',
                        'text' => 'Gagal membaca file Excel: ' . $e->getMessage(),
                    ]);
                }
            } else {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'File Excel tidak ditemukan.',
                ]);
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the PuskesmasProfile model based on its primary key value.
     */
    protected function findModel($id)
    {
        if (($model = PuskesmasProfile::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
