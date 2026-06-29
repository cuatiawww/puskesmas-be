<?php
namespace app\models;

use app\components\Helper;
use yii;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class UrlRule extends yii\web\UrlRule
{
    public $connectionID = 'db';
 
    public function init()
    {
        if ($this->name === null) {
            $this->name = __CLASS__;
        }
    }
 
    public function createUrl($manager, $route, $params)
    {
        $args='?';
        $idx = 0;
        
        foreach($params as $num=>$val){ 
            
			if(is_array($val) !=NULL){
                $id2 = 0;
                foreach($val as $key2=> $val2){
                    $ar = array();
                    $ar[$key2] = $val2;
                    //print_r(http_build_query($ar));                   
                    //echo "<br>";

                    $args .= $num.'['.$key2.']='.$val2; 
                    $id2++;
                    if($id2!=count($val)) $args .= '&';
                }
            }else{
                if($num=='id' || $num=='modul' || $num=='id_pasien'  || $num=='id_faskes' || $num=='set_edit' || $num=='id_kirim' || $num=='kegiatan' || $num=='id_kirim' || $num=='wilayah' || $num=='mobilisasi' || $num=='kab' || $num=='prov' || $num=='user' || $num=='tim' || $num=='id_penugasan' || $num=='id_kelompok' || $num=='jenis' || $num == 'profesi' || $num == 'id_jenis' || $num == 'id_profesi' || $num == 'kelompok_penugasan'){
                     
                     $helper = new Helper();
                     $val = urlencode($helper->encrypt_aes128cbc($val));
                    //echo "<br>";
                }
                if($num == '_pjax'){
                    $val=0;
                }

                $args .= $num . '=' . $val;
                //echo "<br>";
            }
            
            $idx++;
            if($idx!=count($params)) $args .= '&';
        }
        // print_r($args);
        // echo "<br>";
        $suffix = Yii::$app->urlManager->suffix;
        if ($args=='?') $args = '';
        return $route .$suffix. $args;
        return false;  // this rule does not apply
    }
 
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        Yii::info('UrlRule::parseRequest pathInfo: ' . $pathInfo, __METHOD__);

        // Skip custom processing untuk AJAX endpoints dan API endpoints
        $ajaxRoutes = [
            'api/',  // ← semua /api/* ditangani oleh explicit rules di urlManager
            'formulir-bencana/get-kabupaten', 
            'formulir-bencana/get-kecamatan', 
            'formulir-bencana/debug-provinsi', 
            'formulir-bencana/db-info', 
            'formulir-bencana/ping',
            'laporan-kejadian/get-kabupaten',
            'laporan-kejadian/get-kecamatan',
            'laporan-kejadian/test-ajax',
            'laporan-kejadian/public-list',
            'laporan-kejadian/public-detail',
            'kejadian-list/public-list',
            'kejadian-list/public-detail'
        ];
        foreach ($ajaxRoutes as $ajaxRoute) {
            if (strpos($pathInfo, $ajaxRoute) === 0) {
                Yii::info('Skipping custom UrlRule processing for: ' . $pathInfo, __METHOD__);
                return false;  // Let explicit urlManager rules handle this
            }
        }


        // Quick redirect helper: if a PHP file exists for the requested pretty path (example: /foo/bar -> /foo/bar.php),
        // redirect the browser to the .php file so legacy static pages like /flat-able-ver2/dist/data-keluhan work.
        try {
            $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;
            if ($docRoot) {
                $tryFile = rtrim($docRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($pathInfo, DIRECTORY_SEPARATOR) . '.php';
                if (is_file($tryFile) && is_readable($tryFile)) {
                    $qs = $request->getQueryString();
                    $location = '/' . ltrim($pathInfo, '/');
                    $location .= '.php';
                    if ($qs) $location .= '?' . $qs;
                    header('Location: ' . $location, true, 302);
                    exit;
                }
            }
        } catch (\Throwable $e) {
            Yii::warning('UrlRule::parseRequest redirect check failed: ' . $e->getMessage(), __METHOD__);
        }

        $url = $request->getUrl();
        $queryString = parse_url($url);
        if(isset($queryString['query'])){
            $queryString = $queryString['query'];
            $args = [];
            parse_str($queryString, $args);
            $params = [];

            // Routes that should NOT encrypt IDs (access management routes)
            $noEncryptRoutes = ['level-user', 'user-model'];
            $shouldEncryptId = true;
            foreach ($noEncryptRoutes as $route) {
                if (strpos($pathInfo, $route) !== false) {
                    $shouldEncryptId = false;
                    break;
                }
            }

            foreach($args as $num=>$val){
                if($num=='id' || $num=='modul' || $num=='id_pasien' || $num=='id_faskes' || $num=='set_edit' || $num=='id_kirim'  || $num=='kegiatan'  || $num=='id_kirim' || $num=='wilayah' || $num=='mobilisasi' || $num=='kab' || $num=='prov' || $num=='user' || $num=='tim' || $num=='id_penugasan' || $num=='id_kelompok' || $num=='jenis' || $num == 'profesi' || $num == 'id_jenis' || $num == 'id_profesi' || $num == 'kelompok_penugasan'){

                    // Skip encryption/decryption for access management routes
                    if ($num == 'id' && !$shouldEncryptId) {
                        $params[$num] = $val;
                        continue;
                    }

                    $helper = new Helper();

                    // Try JSON-aware decrypt first (used for numeric ids), then fall back to raw string decrypt
                    $maybe = $helper->decrypt_aes128cbc($val);
                    if ($maybe === false || $maybe === null) {
                        // fallback to string decrypt (preserves non-JSON string keys like '006.1')
                        $maybe = $helper->decrypt_aes128cbc_str($val);
                    }

                   if ($maybe === false || $maybe === null || $maybe === '') {
                        throw new NotFoundHttpException('Halaman tidak ditemukan.');
                   }

                   $val = $maybe; // assign decrypted value (string or numeric)

                }
                $params[$num]=$val;
            }
            $suffix = Yii::$app->urlManager->suffix;
            $route = str_replace($suffix,'',$pathInfo);

            // If the route from pathInfo is empty/index, but we have 'r' query parameter, use 'r' as the route.
            if (($route === '' || $route === 'index' || $route === 'index.php') && isset($args['r'])) {
                $route = $args['r'];
                unset($params['r']);
            }

            //print_r($route);
            //print_r($params);
            return [$route,$params];
        }
        return false;  // this rule does not apply
    }
}