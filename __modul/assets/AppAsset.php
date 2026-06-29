<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [        
		//'bower_components/bootstrap/dist/css/bootstrap.min.css',
		'app_asset/fonts/tabler-icons.min.css',
		'app_asset/fonts/feather.css',
		'app_asset/fonts/fontawesome.css',
		'app_asset/css/style.css',
		'app_asset/css/style-preset.css',
		'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
		// 'app_asset/css/landing.css',			
		
    ];
    public $js = [
		
		//app_asset/js/plugins/apexcharts.min.js',
		//'app_asset/js/pages/dashboard-default.js',

		'https://code.jquery.com/jquery-3.6.0.min.js',
		'app_asset/js/plugins/popper.min.js',
		'app_asset/js/plugins/simplebar.min.js',
		'app_asset/js/plugins/bootstrap.min.js',
		'app_asset/js/fonts/custom-font.js',
		'app_asset/js/pcoded.js',
		'app_asset/js/plugins/feather.min.js',
		'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
		
		
    ];
    public $jsOptions = array(
		'position' => \yii\web\View::POS_HEAD
	);
    public $depends = [
        'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];
}
