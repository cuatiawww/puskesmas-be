<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Tutorial Sistem';
$this->params['active_menu'] = 'tutorial';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">TUTORIAL SISTEM</h5>
          <p>Panduan penggunaan aplikasi Siskohatkes</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="<?= Url::to(['/site/index']) ?>">
              <i class="ph-duotone ph-house"></i>
            </a>
          </li>
          <li class="breadcrumb-item">Tutorial</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h5>DAFTAR TUTORIAL</h5>
        <p>Dokumen dan video tutorial penggunaan sistem</p>
      </div>

      <div class="card-body table-border-style">
        <div class="table-responsive">

          <table class="table table-bordered align-middle">
            <thead class="text-center">
              <tr>
                <th width="5%">NO</th>
                <th width="25%">Nama Tutorial</th>
                <th>Keterangan</th>
                <th width="12%">Tutorial</th>
                <th width="12%">Lihat Video</th>
              </tr>
            </thead>
            <tbody>

              <tr>
                <td class="text-center">1</td>
                <td>
                  Akses dan Pengaturan Sistem Siskohatkes
                </td>
                <td>
                  Tatakelola akses dan pengaturan sistem siskohatkes mulai
                  dari edit profil sampai dengan manajemen password
                </td>
                <td class="text-center">
                  <?= Html::a(
                    '<i class="ti ti-download"></i> Download',
                    Url::to(['/file/tutorial/akses-pengaturan.pdf']),
                    ['class' => 'btn btn-sm btn-primary']
                  ) ?>
                </td>
                <td class="text-center">
                  <?= Html::a(
                    '<i class="ti ti-player-play"></i> Lihat',
                    Url::to(['/tutorial/video', 'id' => 1]),
                    ['class' => 'btn btn-sm btn-success']
                  ) ?>
                </td>
              </tr>

              <tr>
                <td class="text-center">2</td>
                <td>
                  Formulir Pencatatan Pemeriksaan
                </td>
                <td>
                  Tatakelola pencatatan dan pelaporan pemeriksaan rawat jalan
                  mulai dari pendataan pasien sampai dengan kesimpulan rujukan
                  dan lainnya
                </td>
                <td class="text-center">
                  <?= Html::a(
                    '<i class="ti ti-download"></i> Download',
                    Url::to(['/file/tutorial/pencatatan-pemeriksaan.pdf']),
                    ['class' => 'btn btn-sm btn-primary']
                  ) ?>
                </td>
                <td class="text-center">
                  <?= Html::a(
                    '<i class="ti ti-player-play"></i> Lihat',
                    Url::to(['/tutorial/video', 'id' => 2]),
                    ['class' => 'btn btn-sm btn-success']
                  ) ?>
                </td>
              </tr>

              <tr>
                <td class="text-center">3</td>
                <td>
                  Formulir Pencatatan Kegawatdaruratan (UCC)
                </td>
                <td>
                  Tatakelola pencatatan dan pelaporan jamaah saat dilakukan
                  penanganan kegawatdaruratan
                </td>
                <td class="text-center">
                  <?= Html::a(
                    '<i class="ti ti-download"></i> Download',
                    Url::to(['/file/tutorial/ucc.pdf']),
                    ['class' => 'btn btn-sm btn-primary']
                  ) ?>
                </td>
                <td class="text-center">
                  <?= Html::a(
                    '<i class="ti ti-player-play"></i> Lihat',
                    Url::to(['/tutorial/video', 'id' => 3]),
                    ['class' => 'btn btn-sm btn-success']
                  ) ?>
                </td>
              </tr>

            </tbody>
          </table>

        </div>
      </div>
    </div>
  </div>
</div>
