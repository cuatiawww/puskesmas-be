<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Konfigurasi Navigasi';
$this->params['active_menu'] = 'konfigurasi-navigasi';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">KONFIGURASI NAVIGASI</h5>
          <p>Daftar modul dan sub-modul yang muncul di sidebar.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header"><h5>Daftar Navigasi</h5></div>
      <div class="card-body">
        <?php if (empty($menu)): ?>
          <div class="alert alert-info">Tidak ada modul ditemukan.</div>
        <?php endif; ?>

        <?php foreach ($menu as $mod): ?>
          <div class="mb-3">
            <h6 class="mb-1"><?= Html::encode($mod['label']) ?> <small class="text-muted">(<?= Html::encode($mod['nama_modul']) ?>)</small></h6>
            <p class="small text-muted"><?= Html::encode($mod['deskripsi']) ?></p>

            <?php if (!empty($mod['sub_modules'])): ?>
              <ul>
                <?php foreach ($mod['sub_modules'] as $sm): ?>
                  <li>
                    <strong><?= Html::encode($sm['label']) ?></strong>
                    &mdash; <code><?= Html::encode($sm['route']) ?></code>
                    <?php if (!empty($sm['children'])): ?>
                      <ul>
                        <?php foreach ($sm['children'] as $c): ?>
                          <li><?= Html::encode($c['label']) ?> &mdash; <code><?= Html::encode($c['route']) ?></code></li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="text-muted">(tidak ada sub-modul)</div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
