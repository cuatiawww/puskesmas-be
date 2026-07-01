  <?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\models\member_modul\MemberModulModel;
use app\widgets\Alert;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => '@web/favicon.ico']);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    
	<script>
	const showLoadingMain = function(e) {
	Swal.fire({
	  title: "Mohon Tunggu",
	  text: "....",
	  allowEscapeKey: false,
	  allowOutsideClick: false,
	  showConfirmButton: false,
	  confirmButtonColor: '#870000',
	  showLoading: true,  
	  closeOnConfirm: false,	             
	  onOpen: () => {
		Swal.showLoading();
	  }
	},function() {
		  
		  });
		};
</script> 
	
<style>
  /* ===== Yii2 default pager (BS3-like) -> tampil BS5 ===== */
ul.pagination{
  display:flex;
  flex-wrap:wrap;
  padding-left:0;
  margin: 1rem 0;
  list-style:none;
  justify-content: flex-end; /* kanan */
}

ul.pagination > li{
  margin-left:-1px;
}

ul.pagination > li > a,
ul.pagination > li > span{
  position:relative;
  display:block;
  padding:.375rem .75rem;
  line-height:1.5;
  text-decoration:none;
  background-color:#fff;
  border:1px solid #dee2e6;
  color: var(--bs-link-color, #0d6efd);
}

ul.pagination > li > a:hover{
  z-index:2;
  background-color:#e9ecef;
  border-color:#dee2e6;
  color: var(--bs-link-hover-color, #0a58ca);
}

ul.pagination > li:first-child > a,
ul.pagination > li:first-child > span{
  margin-left:0;
  border-top-left-radius:.375rem;
  border-bottom-left-radius:.375rem;
}

ul.pagination > li:last-child > a,
ul.pagination > li:last-child > span{
  border-top-right-radius:.375rem;
  border-bottom-right-radius:.375rem;
}

/* active */
ul.pagination > li.active > a,
ul.pagination > li.active > span{
  z-index:3;
  color:#fff;
  background-color: #229799;
  border-color: #229799;
}

/* disabled */
ul.pagination > li.disabled > a,
ul.pagination > li.disabled > span{
  color:#6c757d;
  pointer-events:none;
  background-color:#fff;
  border-color:#dee2e6;
}

/* optional: kecilkan prev/next spacing biar rapi */
ul.pagination > li.prev > span,
ul.pagination > li.next > a{
  font-weight: 500;
}

/* Sidebar menu items hover and active styling */
.pc-sidebar .pc-navbar .pc-item:not(.pc-hasmenu) > .pc-link:hover,
.pc-sidebar .pc-navbar .pc-item:not(.pc-hasmenu).active > .pc-link,
.pc-sidebar .pc-navbar .pc-submenu .pc-item > .pc-link:hover,
.pc-sidebar .pc-navbar .pc-submenu .pc-item.active > .pc-link {
    background-color: #e0f2f1 !important; /* Light teal background */
    color: #176b87 !important; /* Dark teal text */
    border-radius: 8px !important;
    font-weight: 600 !important;
    transition: all 0.2s ease-in-out;
}

.pc-sidebar .pc-navbar .pc-item:not(.pc-hasmenu) > .pc-link:hover .pc-micon i,
.pc-sidebar .pc-navbar .pc-item:not(.pc-hasmenu).active > .pc-link .pc-micon i {
    color: #176b87 !important;
}

@media (min-width: 1025px) {
    .pc-sidebar {
        width: 285px !important;
    }
    .pc-container {
        margin-left: 285px !important;
    }
    .pc-header {
        left: 285px !important;
    }
}
</style>
	
	
	 <script>
		 function showLoading(){
			Swal.fire({
					title: 'Mohon Tunggu !',
					html: '.......',// add html attribute if you want or remove
					allowOutsideClick: false,
					showConfirmButton: false,
					onBeforeOpen: () => {
						Swal.showLoading()
					},
				});
		}
	 </script>



</head>
<body data-pc-preset="preset-1" data-pc-sidebar-theme="dark" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
<?php $this->beginBody() ?>

 <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
      <div class="pc-loader">
        <div class="loader-fill"></div>
      </div>
    </div>
    <!-- [ Pre-loader ] End -->



<?php  if(Yii::$app->user->getIsGuest()){ ?>
	

    <?= $content ?> 

<?php }else{ ?>

	<?= $this->renderFile(Yii::getAlias('@app/views/layouts/sidebar.php')) ?>
	<?= $this->renderFile(Yii::getAlias('@app/views/layouts/navbar.php')) ?>

	<!-- [ Main Content ] start -->
<div class="pc-container">
  <div class="pc-content">
		<?= $content ?> 
  </div>
</div>



<?php } ?>  


 <script>
      layout_change('light');
    </script>
    <script>
      layout_sidebar_change('dark');
    </script>
    <script>
      layout_header_change('dark');
    </script>
    <script>
      change_box_container('false');
    </script>
    <script>
      layout_caption_change('true');
    </script>
    <script>
      layout_rtl_change('false');
    </script>
    <script>
      preset_change('preset-1');
    </script>
    
     <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.45.0/apexcharts.min.js"></script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
