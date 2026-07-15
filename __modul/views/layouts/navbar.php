<?php

use app\models\level_user\LevelUser;
use app\models\UserRegistration;

$foto_profil_url = Yii::$app->user->identity->foto_profil != NULL ? Yii::$app->params['base_url'].'/'. Yii::$app->user->identity->foto_profil : Yii::$app->params['base_url'].'/app_asset/images/user/avatar-1.jpg';
$user_name = Yii::$app->user->identity->username;

$user_level = LevelUser::findOne(Yii::$app->user->identity->level_user_id);
$user_role = $user_level['nama_level'];

$isAdmin = (Yii::$app->user->identity->level_user_id == 1);
$notifications = [];
$unreadCount = 0;

if ($isAdmin) {
    try {
        $pendingRegistrations = UserRegistration::find()
            ->where(['status' => UserRegistration::STATUS_PENDING_APPROVAL])
            ->orderBy(['id' => SORT_DESC])
            ->all();
            
        if (!empty($pendingRegistrations)) {
            foreach ($pendingRegistrations as $reg) {
                $id = 'reg_' . $reg->id;
                $isRead = Yii::$app->session->get('notif_read_' . $id, false);
                
                $notifications[] = [
                    'id' => $id,
                    'title' => 'Persetujuan Akun Baru',
                    'description' => 'User ' . htmlspecialchars($reg->nama_lengkap) . ' (' . htmlspecialchars($reg->username) . ') mengajukan akses ' . htmlspecialchars($reg->kategori_akses) . '.',
                    'time' => Yii::$app->formatter->asRelativeTime($reg->created_at),
                    'badge' => 'Menunggu Approval',
                    'badge_class' => 'bg-light-info border border-info text-info',
                    'icon' => 'ph-duotone ph-user-circle-plus text-info f-18',
                    'status' => $isRead ? 'Dibaca' : 'Belum Dibaca',
                    'url' => \yii\helpers\Url::to(['/user-registration/view', 'id' => $reg->id]),
                ];
                
                if (!$isRead) {
                    $unreadCount++;
                }
            }
        }
    } catch (\Throwable $e) {
        // Fallback silently if DB/table issue
    }
}
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
      <div class="dropdown-menu pc-h-dropdown drp-search" style="min-width: 320px; overflow: visible;">
        <form class="px-1" onsubmit="return false;">
          <div class="mb-0 d-flex align-items-center position-relative w-100">
            <input type="search" id="menu-search-input" class="form-control border-0 shadow-none" placeholder="Search here. . ." autocomplete="off" />
            <button class="btn btn-light-secondary btn-search" type="button" style="display: none;">Search</button>
          </div>
          <div id="menu-search-suggestions" class="dropdown-menu show w-100 border-0 shadow" style="display: none; position: absolute; top: 100%; left: 0; max-height: 350px; overflow-y: auto; z-index: 1100; padding: 5px 0; background: #ffffff;">
            <!-- Suggestions will be generated here -->
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
        <?php if ($unreadCount > 0): ?>
            <span class="badge bg-danger pc-h-badge"><?= $unreadCount ?></span>
        <?php endif; ?>
      </a>
      
       <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
             <div class="dropdown-header d-flex align-items-center justify-content-between">
                <h4 class="m-0">Notifikasi</h4>
                <ul class="list-inline ms-auto mb-0">
                  <li class="list-inline-item">
                    <a href="<?= \yii\helpers\Url::to(['/alert-notifikasi/index']) ?>" class="avtar avtar-s btn-link-hover-primary" title="Perbesar Notifikasi">
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
                  <?php if (empty($notifications)): ?>
                      <li class="list-group-item text-center text-muted py-4">
                          Tidak ada notifikasi baru.
                      </li>
                  <?php else: ?>
                      <?php foreach ($notifications as $item): ?>
                      <li class="list-group-item">
                        <div class="d-flex">
                          <div class="flex-shrink-0">
                            <i class="<?= $item['icon'] ?>"></i>
                          </div>

                          <div class="flex-grow-1 ms-3">
                            <div class="d-flex align-items-center mb-1">
                              <h5 class="mb-0 flex-grow-1">
                                <?php if (!empty($item['url']) && $item['url'] !== '#'): ?>
                                    <a href="<?= \yii\helpers\Url::to(['/alert-notifikasi/read', 'id' => $item['id'], 'url' => $item['url']]) ?>" class="text-dark fw-bold text-decoration-none"><?= htmlspecialchars($item['title']) ?></a>
                                <?php else: ?>
                                    <a href="<?= \yii\helpers\Url::to(['/alert-notifikasi/read', 'id' => $item['id']]) ?>" class="text-dark fw-bold text-decoration-none"><?= htmlspecialchars($item['title']) ?></a>
                                <?php endif; ?>
                                <?php if ($item['status'] === 'Belum Dibaca'): ?>
                                    <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">Baru</span>
                                <?php endif; ?>
                              </h5>
                              <span class="text-sm text-muted">
                                <?= htmlspecialchars($item['time']) ?>
                              </span>
                            </div>

                            <p class="text-muted mb-2 mb-0">
                              <?= htmlspecialchars($item['description']) ?>
                            </p>

                            <?php if (!empty($item['url']) && $item['url'] !== '#'): ?>
                                <a href="<?= \yii\helpers\Url::to(['/alert-notifikasi/read', 'id' => $item['id'], 'url' => $item['url']]) ?>" class="badge <?= $item['badge_class'] ?>">
                                  <?= htmlspecialchars($item['badge']) ?>
                                </a>
                            <?php else: ?>
                                <span class="badge <?= $item['badge_class'] ?>">
                                  <?= htmlspecialchars($item['badge']) ?>
                                </span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </li>
                      <?php endforeach; ?>
                  <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('menu-search-input');
    const suggestionsContainer = document.getElementById('menu-search-suggestions');
    let debounceTimer;

    if (!searchInput || !suggestionsContainer) return;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            suggestionsContainer.style.display = 'none';
            suggestionsContainer.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(function() {
            const url = '<?= Yii::$app->params['base_url'] ?>/site/search-menu?q=' + encodeURIComponent(query);
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    suggestionsContainer.innerHTML = '';
                    if (data && data.length > 0) {
                        data.forEach(item => {
                            const a = document.createElement('a');
                            a.className = 'dropdown-item d-flex flex-column align-items-start py-2 px-3';
                            a.href = item.route;
                            a.style.borderBottom = '1px solid #f1f5f9';
                            a.style.whiteSpace = 'normal';
                            a.style.cursor = 'pointer';
                            
                            const labelSpan = document.createElement('span');
                            labelSpan.className = 'fw-bold text-dark';
                            labelSpan.textContent = item.label;
                            
                            const categorySpan = document.createElement('small');
                            categorySpan.className = 'text-muted text-uppercase';
                            categorySpan.style.fontSize = '0.7rem';
                            categorySpan.style.marginTop = '2px';
                            categorySpan.textContent = item.category;

                            a.appendChild(labelSpan);
                            a.appendChild(categorySpan);
                            suggestionsContainer.appendChild(a);
                        });
                        suggestionsContainer.style.display = 'block';
                    } else {
                        const div = document.createElement('div');
                        div.className = 'text-center text-muted py-3 px-3';
                        div.textContent = 'Menu tidak ditemukan';
                        suggestionsContainer.appendChild(div);
                        suggestionsContainer.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Search error:', err);
                });
        }, 300);
    });

    // Close suggestions dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });

    // Prevent closing search dropdown when clicking suggestions container
    suggestionsContainer.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});
</script>
