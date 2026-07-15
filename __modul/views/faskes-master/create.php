<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Tambah ' . $jenisLabel;
$this->params['active_menu'] = $activeMenu;
?>

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center justify-content-between">
            <div class="col-sm-auto">
                <div class="page-header-title">
                    <h5 class="mb-0 fw-bold">TAMBAH <?= Html::encode(strtoupper($jenisLabel)) ?></h5>
                    <p>Menambahkan data faskes baru ke database lokal.</p>
                </div>
            </div>
            <div class="col-sm-auto">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= Url::to(['/site/index']) ?>">
                            <i class="ph-duotone ph-house"></i>
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= Url::to(['index']) ?>"><?= Html::encode(strtoupper($jenisLabel)) ?></a>
                    </li>
                    <li class="breadcrumb-item">TAMBAH</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>FORMULIR TAMBAH <?= Html::encode(strtoupper($jenisLabel)) ?></h5>
            </div>
            <div class="card-body">
                <?= $this->render('_form', [
                    'model' => $model,
                    'jenis' => $jenis,
                    'scope' => $scope,
                    'provinsiOptions' => $provinsiOptions,
                    'kabupatenOptions' => $kabupatenOptions,
                    'kecamatanOptions' => $kecamatanOptions,
                    'desaOptions' => $desaOptions,
                ]) ?>
            </div>
        </div>
    </div>
</div>
