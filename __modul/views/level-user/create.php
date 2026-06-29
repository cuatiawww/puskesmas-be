<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\level_user\LevelUser $model */

$this->title = 'Tambah Level User';
$this->params['breadcrumbs'][] = ['label' => 'Akses', 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => 'Level User', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="level-user-create">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center justify-content-between">
                <div class="col-sm-auto">
                    <div class="page-header-title">
                        <h5 class="mb-0 font-weight-600 fw-bold">TAMBAH LEVEL USER</h5>
                    </div>
                    <p>Form Pengelolaan Level User & Hak Akses</p>
                </div>
            </div>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
