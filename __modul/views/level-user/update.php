<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\level_user\LevelUser $model */

$this->title = 'Edit Level User: ' . $model->nama_level;
$this->params['breadcrumbs'][] = ['label' => 'Akses', 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => 'Level User', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Edit';
?>
<div class="level-user-update">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center justify-content-between">
                <div class="col-sm-auto">
                    <div class="page-header-title">
                        <h5 class="mb-0 font-weight-600 fw-bold">EDIT LEVEL USER</h5>
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
