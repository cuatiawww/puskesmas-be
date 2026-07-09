<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var string $username */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var yii\base\DynamicModel $searchModel */

$this->title = 'Riwayat Aktivitas: ' . Html::encode($username);
$this->params['breadcrumbs'][] = ['label' => 'Log Aktivitas Pengguna', 'url' => ['index']];
$this->params['breadcrumbs'][] = $username;

$this->registerCss("
    .table thead th a {
        color: #fff !important;
    }
    .table thead th a:hover {
        color: #fff !important;
        text-decoration: underline;
    }
");
?>

<!-- Page Header (Flat Able style) -->
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center justify-content-between">
            <div class="col-sm-auto">
                <div class="page-header-title">
                    <h5 class="mb-0 font-weight-600 fw-bold">RIWAYAT AKTIVITAS USER</h5>
                </div>
                <p>Memantau riwayat log dan aksi oleh pengguna terpilih</p>
            </div>
            <div class="col-sm-auto">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ti ti-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= Url::to(['index']) ?>">LOG AKTIVITAS PENGGUNA</a></li>
                    <li class="breadcrumb-item"><?= Html::encode($username) ?></li>
                </ul>
            </div>
            <div style="margin-top: 10px;" class="d-flex col-12">
                <a class="btn btn-sm btn-primary rounded-pill px-2" role="button" href="javascript:void(0);">
                    <i class="ti ti-external-link me-1"></i> Info Selengkapnya
                </a>
            </div>
        </div>
    </div>
</div>

<div class="mb-3">
    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Kembali ke Matriks', ['index'], ['class' => 'btn btn-outline-secondary rounded-2 px-3']) ?>
</div>

<!-- Log Grid List -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5>RIWAYAT LOG AKTIVITAS</h5>
                    <p class="mb-0 text-muted">Daftar detail jejak log akses halaman dan modifikasi data oleh pengguna ini.</p>
                </div>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <?php Pjax::begin(['id' => 'pjax-user-activity-detail']); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'tableOptions' => ['class' => 'table table-striped table-hover'],
                        'layout' => "{items}\n<div class='py-3 px-4 d-flex justify-content-between align-items-center'>{summary}<div class='d-flex justify-content-end'>{pager}</div></div>",
                        'pager' => [
                            'options' => ['class' => 'pagination mb-0'],
                            'linkOptions' => ['class' => 'page-link'],
                            'disabledPageCssClass' => 'page-item disabled',
                            'activePageCssClass' => 'page-item active',
                            'prevPageCssClass' => 'page-item',
                            'nextPageCssClass' => 'page-item',
                            'pageCssClass' => 'page-item',
                        ],
                        'columns' => [
                            [
                                'class' => 'yii\grid\SerialColumn',
                                'header' => 'NO',
                                'contentOptions' => ['style' => 'width: 60px;', 'class' => 'text-center'],
                            ],
                            [
                                'attribute' => 'created_at',
                                'label' => 'TANGGAL & WAKTU',
                                'filter' => false,
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asDatetime($model->created_at, 'medium');
                                },
                                'contentOptions' => ['style' => 'width: 180px;'],
                            ],
                            [
                                'attribute' => 'action',
                                'label' => 'TINDAKAN',
                                'filter' => [
                                    'view' => 'VIEW',
                                    'create' => 'CREATE',
                                    'update' => 'UPDATE',
                                    'delete' => 'DELETE'
                                ],
                                'headerOptions' => ['class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center', 'style' => 'width: 140px;'],
                                'value' => function ($model) {
                                    switch ($model->action) {
                                        case 'create':
                                            return '<span class="badge bg-light-success px-3 py-2 fw-bold">CREATE</span>';
                                        case 'update':
                                            return '<span class="badge bg-light-warning text-dark px-3 py-2 fw-bold">UPDATE</span>';
                                        case 'delete':
                                            return '<span class="badge bg-light-danger px-3 py-2 fw-bold">DELETE</span>';
                                        case 'view':
                                        default:
                                            return '<span class="badge bg-light text-dark border px-3 py-2 fw-bold">VIEW</span>';
                                    }
                                },
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'route',
                                'label' => 'RUTE / MODUL',
                                'value' => function ($model) {
                                    $route = Html::encode($model->route);
                                    $module = $model->module ? '<span class="badge bg-secondary mr-1">' . Html::encode($model->module) . '</span>' : '';
                                    return $module . '<code>' . $route . '</code>';
                                },
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'url',
                                'label' => 'DETAIL URL',
                                'filter' => false,
                                'contentOptions' => ['style' => 'max-width: 250px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;'],
                                'value' => function ($model) {
                                    $truncated = \yii\helpers\StringHelper::truncate($model->url, 50);
                                    return Html::tag('span', Html::encode($truncated), ['title' => $model->url, 'style' => 'cursor: help;', 'class' => 'small text-muted']);
                                },
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'ip_address',
                                'label' => 'IP ADDRESS',
                                'filter' => false,
                                'value' => function ($model) {
                                    return '<strong class="text-primary">' . Html::encode($model->ip_address) . '</strong>';
                                },
                                'format' => 'raw',
                                'contentOptions' => ['style' => 'width: 130px;'],
                            ],
                            [
                                'label' => 'BROWSER / OS',
                                'filter' => false,
                                'value' => function ($model) {
                                    return '<span class="small">' . Html::encode($model->browser) . ' (' . Html::encode($model->platform) . ')</span>';
                                },
                                'format' => 'raw',
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'AKSI',
                                'template' => '{detail}',
                                'contentOptions' => ['class' => 'text-center', 'style' => 'width: 150px;'],
                                'buttons' => [
                                    'detail' => function ($url, $model) {
                                        $icon = $model->action === 'view' ? 'ti ti-info-circle' : 'ti ti-list-details';
                                        $label = $model->action === 'view' ? 'Akses Info' : 'Detail Data';
                                        $btnClass = $model->action === 'view' ? 'btn-outline-secondary' : 'btn-primary';
                                        
                                        return Html::a('<i class="' . $icon . ' me-1"></i> ' . $label, 
                                            Url::to(['detail', 'id' => $model->id]), 
                                            [
                                                'class' => 'btn btn-sm px-3 rounded-pill ' . $btnClass,
                                                'title' => 'Lihat Detail Perubahan',
                                                'data-pjax' => 0
                                            ]
                                        );
                                    }
                                ]
                            ]
                        ],
                    ]); ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
