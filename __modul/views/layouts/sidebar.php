<?php 
use app\components\MenuService;
use app\components\ImageHelper;
use app\models\level_user\LevelUser;
use yii\helpers\Url;

$menu = MenuService::getMenu();

// Get user photo URL using helper
$foto_profil = Yii::$app->user->identity->foto_profil ?? null;

// Use Url helper for consistent URL generation
if (!empty($foto_profil)) {
    // Check if file really exists
    $filePath = Yii::getAlias('@app/../') . $foto_profil;
    if (file_exists($filePath)) {
        // Foto ada, gunakan path foto
        $foto_profil_url = Url::base() . '/' . ltrim($foto_profil, '/');
    } else {
        // Foto tidak ada, gunakan default
        $foto_profil_url = Url::base() . '/app_asset/images/user/avatar-1.jpg';
    }
} else {
    // Tidak ada foto, gunakan default
    $foto_profil_url = Url::base() . '/app_asset/images/user/avatar-1.jpg';
}

$user_name = Yii::$app->user->identity->username;

$user_level = LevelUser::findOne(Yii::$app->user->identity->level_user_id);
$user_role = $user_level['nama_level'];

$controllerId = Yii::$app->controller->id; 

function getCtrlFromRoute($route) {
  $r = trim((string)$route);
  if ($r === '' || $r === '#!' || $r === '#') return '';

  // kalau full url, ambil path + query
  if (preg_match('~^https?://~i', $r)) {
    $path  = parse_url($r, PHP_URL_PATH) ?? '';
    $query = parse_url($r, PHP_URL_QUERY) ?? '';
    $r = $path . ($query ? ('?' . $query) : '');
  }

  // buang base_url kalau ada
  $base = rtrim((string)Yii::$app->params['base_url'], '/');
  if ($base && strpos($r, $base) === 0) {
    $r = substr($r, strlen($base));
  }

  // handle query style: index.php?r=controller/action
  if (strpos($r, 'r=') !== false) {
    parse_str(parse_url($r, PHP_URL_QUERY) ?? '', $qs);
    if (!empty($qs['r'])) $r = $qs['r'];
  }

  // buang query string normal
  $r = explode('?', $r, 2)[0];

  $r = trim($r, '/');               // "/pemeriksaan/get-list-history" => "pemeriksaan/get-list-history"
  if ($r === '' || $r === 'index.php') return '';

  // kalau masih ada "index.php/..." buang index.php
  if (strpos($r, 'index.php/') === 0) $r = substr($r, 10);

  $parts = explode('/', $r);
  return $parts[0] ?? '';
}


$currentRoute = trim(Yii::$app->requestedRoute, '/'); // "pemeriksaan/get-list-history"

function normalizeRoute($route) {
  $r = trim((string)$route);
  if ($r === '' || $r === '#!' || $r === '#') return '';

  // kalau full url
  if (preg_match('~^https?://~i', $r)) {
    $path  = parse_url($r, PHP_URL_PATH) ?? '';
    $query = parse_url($r, PHP_URL_QUERY) ?? '';
    $r = $path . ($query ? ('?' . $query) : '');
  }

  // buang base_url kalau ada
  $base = rtrim((string)Yii::$app->params['base_url'], '/');
  if ($base && strpos($r, $base) === 0) {
    $r = substr($r, strlen($base));
  }

  // handle index.php?r=...
  if (strpos($r, 'r=') !== false) {
    parse_str(parse_url($r, PHP_URL_QUERY) ?? '', $qs);
    if (!empty($qs['r'])) $r = $qs['r'];
  }

  // buang query string
  $r = explode('?', $r, 2)[0];

  $r = trim($r, '/');
  if (strpos($r, 'index.php/') === 0) $r = substr($r, 10);

  return $r; // hasil: "pemeriksaan/get-list-history"
}

function getMenuUrl($route) {
  $r = trim((string)$route);
  if ($r === '' || $r === '#' || $r === '#!') {
    return $r;
  }
  if (preg_match('~^(https?:)?//~i', $r)) {
    return $r;
  }
  if (strpos($r, '/') === 0) {
    return $r;
  }
  $cleanRoute = '/' . ltrim($r, '/');
  return Url::to([$cleanRoute]);
}
?>

<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <a href="<?=Yii::$app->params['base_url']?>" class="b-brand text-primary">
        <!-- ========   Change your logo from here   ============ -->
        <img src="<?= \app\components\SystemSettingHelper::getAssetUrl('inner_logo', '/app_asset/images/logo-haji.png') ?>" 
             alt="logo image" 
             class="logo-md" 
             style="max-width: 240px; height: auto; margin-top: 20px" />
      </a>
    </div>
    <div class="card pc-user-card">
      <div class="card-body">
        <div class="nav-user-image">
          <a data-bs-toggle="collapse" href="#navuserlink">
            <img src="<?php echo htmlspecialchars($foto_profil_url); ?>" alt="user-image" class="user-avtar rounded-circle" />
          </a>
        </div>
        <div class="pc-user-collpsed collapse" id="navuserlink">
          <h4 class="mb-0"><?php echo htmlspecialchars($user_name); ?></h4>
          <span><?php echo htmlspecialchars($user_role); ?></span>
          <ul>
            <li
              ><a href="<?=Yii::$app->params['base_url']?>/user-model/update" class="pc-user-links">
                <i class="ph-duotone ph-user"></i>
                <span>AKUN PETUGAS</span>
              </a></li
            >
            <li
              ><a href="#" class="pc-user-links">
                <i class="ph-duotone ph-gear"></i>
                <span>PENGATURAN</span>
              </a></li
            > 
            <li
              ><a href="#" class="pc-user-links">
                <i class="ph-duotone ph-lock-key"></i>
                <span>UBAH PASSWORD</span>
              </a></li
            >
            <li
              ><a href="<?=Yii::$app->params['base_url']?>/site/logout" class="pc-user-links">
                <i class="ph-duotone ph-power"></i>
                <span>Logout</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="navbar-content">
      <?php foreach ($menu as $modul): ?>
      <ul class="pc-navbar">
        <li class="pc-item pc-caption">
          <label style="color: #000000"><?php echo htmlspecialchars($modul['label']); ?></label>
          <span><?php echo htmlspecialchars($modul['deskripsi']); ?></span>
          <ul class="pc-submenu"></ul>
        </li>
        <?php if (!empty($modul['sub_modules'])): ?>
          <?php
          // Check if any sub-module has children (for determining if parent should have pc-hasmenu class)
          $hasSubMenu = false;
          $subModuleNames = array_column($modul['sub_modules'], 'nama_sub_modul');
          ?>
          <?php foreach ($modul['sub_modules'] as $subModul): ?>
            <?php
            $isActive = isset($active_menu) && $active_menu == $subModul['nama_sub_modul'];
            $hasChildren = !empty($subModul['children']);

            $subCtrl = getCtrlFromRoute($subModul['route']);
            $parentActiveByController = ($subCtrl !== '' && $subCtrl === $controllerId);

            // Check if any child is active by controllerId (route)
            $childActive = false;
            if ($hasChildren) {
                foreach ($subModul['children'] as $child) {
                    $childCtrl = getCtrlFromRoute($child['route']);
                    if ($childCtrl !== '' && $childCtrl === $controllerId) {
                        $childActive = true;
                        break;
                    }
                }
            }

            // pc-trigger hanya kalau ada children DAN (parent aktif atau child aktif)
            $trigger = ($hasChildren && ($parentActiveByController || $childActive));
            
            // Parent should also be active if any child is active
            $parentShouldBeActive = $parentActiveByController || $childActive;
            ?>
            <li class="pc-item
                <?= $parentShouldBeActive ? 'active' : '' ?>
                <?= $hasChildren ? 'pc-hasmenu' : '' ?>
                <?= $trigger ? 'pc-trigger' : '' ?>
            ">
              <a href="<?php echo $hasChildren ? '#!' : htmlspecialchars(getMenuUrl($subModul['route'])); ?>" class="pc-link">
                <span class="pc-micon">
                  <i class="<?php echo htmlspecialchars($subModul['icon']); ?>"></i>
                </span>
                <span class="pc-mtext"><?php echo htmlspecialchars($subModul['label']); ?></span>
                <?php if ($hasChildren): ?>
                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                <?php endif; ?>
              </a>
              <?php if ($hasChildren): ?>
              <ul class="pc-submenu">
                <?php foreach ($subModul['children'] as $child): ?>
                   <?php
                    $childRoute = normalizeRoute($child['route']);
                    $isChildActive = ($childRoute !== '' && $childRoute === $currentRoute);
                  ?>
                  <li class="pc-item <?= $isChildActive ? 'active' : '' ?>">
                    <a class="pc-link" href="<?php echo htmlspecialchars(getMenuUrl($child['route'])); ?>">
                      <?php echo htmlspecialchars($child['label']); ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
      <?php endforeach; ?>
    </div>
  </div>
</nav>
<!-- [ Sidebar Menu ] end -->
