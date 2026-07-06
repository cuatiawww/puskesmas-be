 <?php

use app\models\level_user\LevelUser;

 $foto_profil_url = Yii::$app->user->identity->foto_profil !=NULL ? Yii::$app->params['base_url'].'/'. Yii::$app->user->identity->foto_profil : Yii::$app->params['base_url'].'/app_asset/images/user/avatar-1.jpg';
$user_name = Yii::$app->user->identity->username;

$user_level = LevelUser::findOne(Yii::$app->user->identity->level_user_id);
$user_role = $user_level['nama_level'];


 ?>
 
 <!-- [ Header Topbar ] start -->
<header class="pc-header">
  <div class="header-wrapper"> <!-- [Mobile Media Block] start -->
<div class="me-auto pc-mob-drp">
  <ul class="list-unstyled">
    <!-- ======= Menu collapse Icon ===== -->
    <li class="pc-h-item pc-sidebar-collapse">
      <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
        <i class="ph-duotone ph-list"></i>
      </a>
    </li>
    <li class="pc-h-item pc-sidebar-popup">
      <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
        <i class="ph-duotone ph-list"></i>
      </a>
    </li>
    <li class="dropdown pc-h-item">
      <a
        class="pc-head-link dropdown-toggle arrow-none m-0 trig-drp-search"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        aria-expanded="false"
      >
        <i class="ph-duotone ph-magnifying-glass"></i>
      </a>
      <div class="dropdown-menu pc-h-dropdown drp-search">
        <form class="px-1">
          <div class="mb-0 d-flex align-items-center">
            <input type="search" class="form-control border-0 shadow-none" placeholder="Search here. . ." />
            <button class="btn btn-light-secondary btn-search">Search</button>
          </div>
        </form>
      </div>
    </li>
  </ul>
</div>
<!-- [Mobile Media Block end] -->
<div class="ms-auto">
  <ul class="list-unstyled">
    <li class="dropdown pc-h-item">
      <a
        class="pc-head-link dropdown-toggle arrow-none me-0"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        aria-expanded="false"
      >
        <i class="ph-duotone ph-diamonds-four"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
        <a href="<?= \yii\helpers\Url::to(['/profil/index']) ?>" class="dropdown-item">
          <i class="ph-duotone ph-user"></i>
          <span>Akun Saya</span>
        </a>
        <a href="#" class="dropdown-item">
          <i class="ph-duotone ph-gear"></i>
          <span>Pengaturan</span>
        </a>
        <a href="#" class="dropdown-item">
          <i class="ph-duotone ph-lifebuoy"></i>
          <span>Bantuan</span>
        </a>
        <a href="<?= \yii\helpers\Url::to(['/profil/ubah-password']) ?>" class="dropdown-item">
          <i class="ph-duotone ph-lock-key"></i>
          <span>Ubah Password</span>
        </a>
        <a href="<?= Yii::$app->params['base_url'] ?>/site/logout" class="dropdown-item">
          <i class="ph-duotone ph-power"></i>
          <span>Logout</span>
        </a>
      </div>
    </li>
    <li class="dropdown pc-h-item">
      <a
        class="pc-head-link dropdown-toggle arrow-none me-0"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        aria-expanded="false"
      >
        <i class="ph-duotone ph-bell"></i>
        <span class="badge bg-danger pc-h-badge">3</span>
      </a>
      
       <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
             <div class="dropdown-header d-flex align-items-center justify-content-between">
               <h4 class="m-0">Notifikasi</h4>
               <ul class="list-inline ms-auto mb-0">
                 <li class="list-inline-item">
                   <a href="#" class="avtar avtar-s btn-link-hover-primary">
                     <i class="ti ti-arrows-diagonal f-18"></i>
                   </a>
                 </li>
                 <li class="list-inline-item">
                   <a href="#" class="avtar avtar-s btn-link-hover-danger">
                     <i class="ti ti-x f-18"></i>
                   </a>
                 </li>
               </ul>
             </div>

             <div class="dropdown-body text-wrap header-notification-scroll position-relative"
               style="max-height: calc(100vh - 235px)">
               <ul class="list-group list-group-flush">
                 <!-- <li class="list-group-item border-0 pb-0">
                   <p class="text-span mb-2">Hari ini</p>
                 </li> -->
                 <li class="list-group-item">
                   <div class="d-flex">
                     <div class="flex-shrink-0">
                       <i class="ph-duotone ph-first-aid-kit f-18"></i>
                     </div>

                     <div class="flex-grow-1 ms-3">
                       <div class="d-flex align-items-center mb-1">
                         <h5 class="mb-0 flex-grow-1">
                           Deteksi Dini 
                         </h5>
                         <span class="text-sm text-muted">
                           1 menit yang lalu
                         </span>
                       </div>

                       <p class="text-muted mb-2">
                         Deteksi dini terkait dengan temuan kasus dilapangan
                       </p>

                       <span class="badge bg-danger bg-opacity-10 border border-danger text-danger">
                         25 Kasus
                       </span>
                     </div>
                     
                   </div>
                 </li>
                 <li class="list-group-item">
                   <div class="d-flex">
                     <div class="flex-shrink-0">
                       <i class="ph-duotone ph-chats f-18"></i>
                     </div>

                     <div class="flex-grow-1 ms-3">
                       <div class="d-flex align-items-center mb-1">
                         <h5 class="mb-0 flex-grow-1">
                           Monitoring Jamaah
                         </h5>
                         <span class="text-sm text-muted">
                           2 menit yang lalu
                         </span>
                       </div>

                       <p class="text-muted mb-2">
                         Pengingat kegiatan visitasi jamaah terkait pelaporan
                         hasil visitasi jamaah.
                       </p>

                       <span class="badge bg-light-primary border border-primary">
                         12 Jamaah
                       </span>
                     </div>
                     
                   </div>
                 </li>  

                 <li class="list-group-item">
                   <div class="d-flex">
                     <div class="flex-shrink-0">
                       <div class="avtar avtar-s bg-light-primary">
                         <i class="ph-duotone ph-chats-teardrop f-18"></i>
                       </div>
                     </div>

                     <div class="flex-grow-1 ms-3">
                       <div class="d-flex align-items-center mb-1">
                         <h5 class="mb-0 flex-grow-1">
                           Pesan
                         </h5>
                         <span class="text-sm text-muted">
                           1 hari yang lalu
                         </span>
                       </div>

                       <p class="text-muted mb-0">
                         Anda mempunyai 12 pesan belum dilihat
                       </p>
                     </div>
                   </div>
                 </li>
               </ul>
             </div>
           </div>
    </li>
        <li class="dropdown pc-h-item header-user-profile">
      <a
        class="pc-head-link dropdown-toggle arrow-none me-0"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        data-bs-auto-close="outside"
        aria-expanded="false"
      >
        <img src="<?php echo htmlspecialchars($foto_profil_url); ?>" alt="user-image" class="user-avtar" />
      </a>
      <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
        <div class="dropdown-header d-flex align-items-center justify-content-between">
          <h4 class="m-0">Profile</h4>
        </div>
        <div class="dropdown-body">
          <div class="profile-notification-scroll position-relative" style="max-height: calc(100vh - 225px)">
            <ul class="list-group list-group-flush w-100">
              <li class="list-group-item">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <img src="<?php echo htmlspecialchars($foto_profil_url); ?>" alt="user-image" class="wid-50 rounded-circle" />
                  </div>
                  <div class="flex-grow-1 mx-3">
                    <h5 class="mb-0"><?php echo strtoupper($user_name); ?></h5>
                    <small class="text-muted"><?php echo $user_role; ?></small>
                  </div>
                </div>
              </li>
              <li class="list-group-item">
                <a href="<?= \yii\helpers\Url::to(['/profil/ubah-password']) ?>" class="dropdown-item">
                  <span class="d-flex align-items-center">
                    <i class="ph-duotone ph-key"></i>
                    <span>Ganti password</span>
                  </span>
                </a>
                <a href="#" class="dropdown-item" id="btnDownloadData">
                  <span class="d-flex align-items-center">
                    <i class="ph-duotone ph-arrow-circle-down"></i>
                    <span>Download</span>
                  </span>
                </a>
                <a href="<?= \yii\helpers\Url::to(['/profil/index']) ?>" class="dropdown-item">
                  <span class="d-flex align-items-center">
                    <i class="ph-duotone ph-user-circle"></i>
                    <span>Akun Saya</span>
                  </span>
                </a>
                <a href="<?= Yii::$app->params['base_url'] ?>/site/logout" class="dropdown-item">
                  <span class="d-flex align-items-center">
                    <i class="ph-duotone ph-power"></i>
                    <span>Logout</span>
                  </span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </li>
  </ul>
</div>
 </div>
</header>
<!-- [ Header ] end -->
