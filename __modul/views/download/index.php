<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Download';
$this->params['active_menu'] = 'download';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">DOWNLOAD</h5>
          <p>Perangkat lunak pendukung penggunaan Sistem Informasi Pusat Krisis Kesehatan (SIPKK)</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="<?= Url::to(['/site/index']) ?>">
              <i class="ph-duotone ph-house"></i>
            </a>
          </li>
          <li class="breadcrumb-item">Download</li>
        </ul>
      </div>
      <div style="margin-top: 10px;" class="d-flex">
        <a class="btn btn-sm btn-primary rounded-pill px-2" role="button" href="javascript:void(0);">
          <i class="ti ti-external-link me-1"></i> Info Selengkapnya
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h5>DAFTAR DOWNLOAD</h5>
        <p>Daftar aplikasi yang direkomendasikan untuk mengakses sistem</p>
      </div>

      <div class="card-body table-border-style">
        <div class="table-responsive">

          <table class="table table-bordered align-middle">
            <thead class="text-center">
              <tr>
                <th width="5%">NO</th>
                <th width="35%">Nama Download</th>
                <th width="30%">Kategori / Fungsi</th>
                <th width="15%">Download</th>
              </tr>
            </thead>
            <tbody>

              <tr>
                <td class="text-center">1</td>
                <td>Chrome Browser</td>
                <td>Browser</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-primary" >
                    <i class="ti ti-download"></i> Download
                  </button>
                </td>
              </tr>

              <tr>
                <td class="text-center">2</td>
                <td>Firefox Browser</td>
                <td>Browser</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-primary" >
                    <i class="ti ti-download"></i> Download
                  </button>
                </td>
              </tr>

            </tbody>
          </table>

        </div>
      </div>
    </div>
  </div>
</div>
