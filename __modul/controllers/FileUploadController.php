<?php

namespace app\controllers;

use app\components\Helper;
use app\models\file_upload\FileAsset;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\web\HttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class FileUploadController extends BaseController
{
    public function isActionPublic(string $actionId): bool
    {
        return in_array($actionId, ['render', 'lihat-pdf'], true);
    }

    protected function csrfExemptActions(): array
    {
        return ['upload-file', 'verify-visa-photo'];
    }

    private function storageEnabled(): bool
    {
        $storage = Yii::$app->params['storage'] ?? [];
        return Yii::$app->has('fs') && (($storage['driver'] ?? '') === 's3');
    }

    private function storageDbPath(string $uploadPath): string
    {
        $path = trim(str_replace('\\', '/', $uploadPath));
        $path = trim($path, '/');
        return $path === '' ? '/' : '/' . $path;
    }

    private function storageKey(string $uploadPath, string $fileName): string
    {
        $path = trim(str_replace('\\', '/', $uploadPath));
        $path = trim($path, '/');
        $name = ltrim(str_replace('\\', '/', $fileName), '/');
        return $path === '' ? $name : $path . '/' . $name;
    }

    private function localFilePath(string $uploadPath, string $fileName): string
    {
        return Yii::getAlias('@webroot/' . trim($uploadPath, '/\\') . DIRECTORY_SEPARATOR . $fileName);
    }

    private function fsUploadFromLocal(string $localPath, string $key, ?string $visibility = null): array
    {
        if ($localPath === '' || !is_file($localPath) || !is_readable($localPath)) {
            return ['ok' => false, 'error' => 'Local file tidak bisa dibaca.'];
        }

        $stream = @fopen($localPath, 'rb');
        if (!$stream) {
            return ['ok' => false, 'error' => 'Gagal membuka stream lokal.'];
        }

        try {
            $config = [];
            if ($visibility !== null) {
                $config['visibility'] = $visibility;
            }
            Yii::$app->fs->writeStream($key, $stream, $config);
        } catch (\Throwable $e) {
            if (is_resource($stream)) {
                fclose($stream);
            }
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return ['ok' => true];
    }

    private function fsReadStream(string $key)
    {
        try {
            if (Yii::$app->has('fs') && Yii::$app->fs->fileExists($key)) {
                return Yii::$app->fs->readStream($key);
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

     public function beforeAction($action)
    {
        if ($action->id === 'upload-file' || $action->id === 'verify-visa-photo') {

            // ✅ wajib login
            if (Yii::$app->user->isGuest) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                Yii::$app->response->statusCode = 401; // unauthorized
                Yii::$app->response->data = ['error' => 'Mohon Maaf Sesi Login Sudah Berakhir. Silahkan Reload Halaman Dan Login Ulang'];
                return false;
            }

        }

        return parent::beforeAction($action);
    }


   

    public function actionIndex()
    {
        throw new HttpException('403', 'Metode Tidak Diizinkan');
        return $this->render('index');
    }

    public function actionLihatPdf($uxid)
    {
        return $this->actionRender((string)$uxid, 1);
    }

    

    public function actionRender(string $uxid, int $inline = 0)
    {
        /** @var \yii\web\Response $response */
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;

        // 1) Dekrips UID → ambil model
        $helper = new Helper();
        $id = $uxid;
        // $id = $helper->decrypt_aes128cbc($uid);
        // if (!$id || !is_numeric($id)) {
        //     throw new HttpException(404, 'File tidak ditemukan.');
        // }
        
        /** @var FileAsset $fa */
        $fa = FileAsset::findOne(['hash' => $id]);
        if (!$fa) {
            throw new HttpException(404, 'File tidak ditemukan.');
        }

        $useStorage = $this->storageEnabled();
        $storageKey = $this->storageKey((string)$fa->file_path, (string)$fa->file_name);
        $real = null;
        $stream = null;
        $mime = $fa->tipe_file ?: null;

        if ($useStorage) {
            $stream = $this->fsReadStream($storageKey);
            if (!is_resource($stream)) {
                throw new HttpException(404, 'Berkas tidak ada di storage.');
            }
        } else {
            $base = realpath(Yii::getAlias('@webroot' . $fa['file_path']));
            $real = $base . DIRECTORY_SEPARATOR . $fa['file_name'];

            if (!$real || !$base || strpos($real, $base) !== 0 || !is_file($real)) {
                throw new HttpException(404, 'File tidak valid / tidak ada.');
            }
        }

        // 3) Tentukan nama file & MIME
        $downloadName = $fa->file_name ?: ($real ? basename($real) : basename($storageKey));
        if (!$mime) {
            $mime = $real ? (FileHelper::getMimeType($real) ?: 'application/octet-stream') : 'application/octet-stream';
        }

        // 4) Header keamanan dasar
        $headers = $response->headers;
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'DENY');
        $headers->set('X-XSS-Protection', '0');
        $headers->set('Cache-Control', 'private, max-age=0, must-revalidate');
        $headers->set('Pragma', 'no-cache');

        // 5) Inline hanya jika diminta & tipe aman (image/pdf). Default: force download (attachment)
        $canInline = preg_match('#^(image/|application/pdf$)#', $mime);
        $asAttachment = $inline ? !$canInline : true;

        // 6) (Opsional) gunakan X-Sendfile / X-Accel jika tersedia
        $useXSendfile = (!$useStorage) && (Yii::$app->params['useXSendfile'] ?? false);
        if ($useXSendfile && $real) {
            // Apache: mod_xsendfile
            $headers->set('X-Sendfile', $real);
            $headers->set('Content-Type', $mime);
            $headers->set('Content-Disposition', ($asAttachment ? 'attachment' : 'inline')
                . '; filename="' . addslashes($downloadName) . '"');
            return $response;
        }

        // 7) Fallback standar Yii (langsung kirim file)
        if ($useStorage && is_resource($stream)) {
            return $response->sendStreamAsFile($stream, $downloadName, [
                'mimeType' => $mime,
                'inline' => !$asAttachment,
            ]);
        }

        return $response->sendFile($real, $downloadName, [
            'mimeType' => $mime,
            'inline'   => !$asAttachment,
        ]);
    }


    public function actionUploadFile($modelName = 'DmtOrganisasi', $attribute = 'file_upload', $upload_path = "/app_asset/dokumen/", $tipe_file = 'image', $resize = false, $resize_width = 0, $resize_height = 0, $db = false, $id=0, $min_width = 0, $min_height =0, $max_width =0, $max_height =0)
    {
        //Yii::$app->response->format = Response::FORMAT_JSON;

        // $this->enableCsrfValidation = false;

        // if (Yii::$app->user->isGuest) {
        //     return Json::encode(['error' => 'Mohon Maaf Sesi Login Sudah Berakhir. Silahkan Reload Halaman Dan Login Ulang']);
        // }

        $modelName = str_replace('\\\\', '\\', $modelName);

        // Tambahkan namespace default jika tidak lengkap
        if (strpos($modelName, '\\') === false) {
            $modelName = 'app\\models\\' . $modelName;
        }

        if (strpos($modelName, 'app\\models\\') !== 0) {
            return Json::encode(['error' => 'Model upload tidak valid.']);
        }

        // Validasi model dinamis
        if (!class_exists($modelName)) {
            return Json::encode(['error' => 'Invalid model class.']);
        }

        $model = new $modelName();
        $file = UploadedFile::getInstanceByName($model->formName() . '[' . $attribute . ']');

        if (!$file) {
            return Json::encode(['error' => 'No file uploaded.']);
        }

        if (!empty($file->error) && $file->error !== UPLOAD_ERR_OK) {
            $map = [
                UPLOAD_ERR_INI_SIZE   => 'File melebihi batas upload server.',
                UPLOAD_ERR_FORM_SIZE  => 'File melebihi batas form upload.',
                UPLOAD_ERR_PARTIAL    => 'Upload terputus sebagian.',
                UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang diupload.',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary upload tidak tersedia.',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file upload ke disk.',
                UPLOAD_ERR_EXTENSION  => 'Upload dihentikan oleh ekstensi PHP.',
            ];
            return Json::encode(['error' => $map[$file->error] ?? ('Upload error code: ' . $file->error)]);
        }

        $useObjectStorage = $this->storageEnabled();

        if (empty($file->tempName) || !is_file($file->tempName) || !is_readable($file->tempName)) {
            return Json::encode(['error' => 'File temporary tidak tersedia. Silakan upload ulang.']);
        }

        $ext = strtolower($file->extension);
        $mime = FileHelper::getMimeType($file->tempName);

        if($tipe_file == 'image'){
            $allowedTypes = [
            // Images
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp'
            ];

        }elseif($tipe_file == 'pdf'){
            $allowedTypes = [
            // PDF
            'pdf'  => 'application/pdf',
            ];

        }else{
            // Daftar ekstensi & MIME type yang diizinkan
            $allowedTypes = [
                // Images
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',

                // Documents
                'pdf'  => 'application/pdf',
                'doc'  => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls'  => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

                // Archives & data
                'zip'  => 'application/zip',
                'rar'  => 'application/vnd.rar',
                'csv'  => 'text/csv',
            ];
        }
        $allowedExtList = implode(', ', array_keys($allowedTypes));
        // Validasi ekstensi dan mime
        $acceptedMime = $allowedTypes[$ext] ?? null;
        $mimeAliases = [
            'image/jpeg' => ['image/jpeg', 'image/pjpeg'],
            'image/png' => ['image/png', 'image/x-png'],
            'image/gif' => ['image/gif'],
            'image/webp' => ['image/webp'],
            'application/pdf' => ['application/pdf', 'application/x-pdf'],
            'text/csv' => ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'],
            'application/vnd.ms-excel' => ['application/vnd.ms-excel', 'application/octet-stream'],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        ];
        $acceptedMimes = $acceptedMime ? ($mimeAliases[$acceptedMime] ?? [$acceptedMime]) : [];

        if (!isset($allowedTypes[$ext]) || !in_array($mime, $acceptedMimes, true)) {
            //return Json::encode(['error' => 'Tipe File Tidak Diizinkan.']);
           
            return Json::encode([
                'error' => 'Tipe file tidak diizinkan. Pastikan File Yang Anda Upload Bertipe: ' . $allowedExtList
            ]);
        }

        //$isImage = in_array($ext, ['jpg','jpeg','png','gif','webp'], true);
        $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp'], true);
        $contents = '';
        $dangerousPatterns = [];

        // Validasi getimagesize hanya untuk gambar
        if ($isImage) {
            $info = @getimagesize($file->tempName);
            if ($info === false) {
                return Json::encode(['error' => 'File Bukan Image Yang Valid.']);
            }
        }else{ ///kalau selain image
            // 💣 Anti Webshell: Scan semua file, bukan hanya PDF
            $contents = file_get_contents($file->tempName);
            $textExtensions = ['php', 'html', 'js', 'csv', 'txt'];
            if (in_array($ext, $textExtensions)) { ///check kalau basenya 
                $tipe = 1;
                $dangerousPatterns = [
                    '/<\?php/i',
                    '/<\?/i',
                    '/eval\s*\(/i',
                    '/base64_decode\s*\(/i',
                    '/system\s*\(/i',
                    '/shell_exec\s*\(/i',
                    '/passthru\s*\(/i',
                    '/exec\s*\(/i',
                    '/assert\s*\(/i',
                    '/preg_replace\s*\(.*\/e/i',
                    '/`.*?`/i', // backtick shell execution
                ];
            }else{
                $tipe = 2;
                $dangerousPatterns = [
                    '/<\?php/i',
                    // '/<\?/i',
                    '/eval\s*\(/i',
                    '/base64_decode\s*\(/i',
                    '/system\s*\(/i',
                    '/shell_exec\s*\(/i',
                    '/passthru\s*\(/i',
                    '/exec\s*\(/i',
                    '/assert\s*\(/i',
                    '/preg_replace\s*\(.*\/e/i',
                    
                ];
            }
        }
       
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $contents)) {
                return Json::encode(['error' => 'File Tidak Diizinkan. Pastikan File Yang Anda Upload Bertipe  : ' . $allowedExtList]);
            }
        }

        // Buat folder upload
        // Simpan file dengan nama acak
        $safeName = uniqid('upload', true) . '.' . $ext;
        $savedPath = $this->storageDbPath($upload_path);
        $storageKey = $this->storageKey($upload_path, $safeName);
        $directory = $useObjectStorage
            ? rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'yii_upload' . DIRECTORY_SEPARATOR
            : Yii::getAlias('@webroot/' . $upload_path . DIRECTORY_SEPARATOR);
        if (!is_dir($directory)) {
            FileHelper::createDirectory($directory);
        }
        $filePath = $directory . $safeName;


        if ($isImage && ($min_width > 0 || $min_height > 0 || $max_width > 0 || $max_height > 0)) {
            [$origW, $origH] = getimagesize($file->tempName);
            if ($min_width > 0 && $origW < $min_width) {
                return Json::encode([
                    'error' => "Ukuran lebar gambar terlalu kecil. Dibutuhkan minimal {$min_width}px. Ukuran saat ini: {$origW}px."
                ]);
            }
            if ($min_height > 0 && $origH < $min_height) {
                return Json::encode([
                    'error' => "Ukuran tinggi gambar terlalu kecil. Dibutuhkan minimal {$min_height}px. Ukuran saat ini: {$origH}px."
                ]);
            }
            if ($max_width > 0 && $origW > $max_width) {
                return Json::encode([
                    'error' => "Ukuran lebar gambar terlalu besar. Maksimal lebar {$max_width}px. Ukuran saat ini: {$origW}px."
                ]);
            }
            if ($max_height > 0 && $origH > $max_height) {
                return Json::encode([
                    'error' => "Ukuran tinggi gambar terlalu besar. Maksimal tinggi {$max_height}px. Ukuran saat ini: {$origH}px."
                ]);
            }
        }

        // ====== RESIZE (jika image & diminta) ======      
        if ($isImage && $resize && ($resize_width > 0 || $resize_height > 0)) {

            [$origW, $origH] = getimagesize($file->tempName);

            // ❌ Langsung tolak jika lebih kecil dari target (tanpa upscaling)
            $tooSmall =
                (($resize_width  > 0) && ($origW < $resize_width)) ||
                (($resize_height > 0) && ($origH < $resize_height));

            if ($tooSmall) {
                // Pesan kebutuhan minimal
                if ($resize_width > 0 && $resize_height > 0) {
                    $need = "minimal {$resize_width}x{$resize_height}px";
                } elseif ($resize_width > 0) {
                    $need = "lebar minimal {$resize_width}px";
                } else { // $resize_height > 0
                    $need = "tinggi minimal {$resize_height}px";
                }

                return Json::encode([
                    'error' => "Resolusi gambar terlalu kecil. Dibutuhkan {$need}. Ukuran saat ini: {$origW}x{$origH}px."
                ]);
            }

            // Hitung target size (proporsional jika salah satu 0)
            $targetW = (int)$resize_width;
            $targetH = (int)$resize_height;
            if ($targetW > 0 && $targetH === 0) {
                $ratio   = $targetW / $origW;
                $targetH = max(1, (int)round($origH * $ratio));
            } elseif ($targetH > 0 && $targetW === 0) {
                $ratio   = $targetH / $origH;
                $targetW = max(1, (int)round($origW * $ratio));
            } elseif ($targetW === 0 && $targetH === 0) {
                $targetW = $origW; $targetH = $origH;
            }

            $saved = false;

            // Prefer yii2-imagine bila ada; fallback ke GD
            if (class_exists('\yii\imagine\Image')) {
                try {
                    $options = [];
                    if ($mime === 'image/jpeg') {
                        $options['quality'] = 85;
                    } elseif ($mime === 'image/png') {
                        $options['png_compression_level'] = 9;
                    } elseif ($mime === 'image/webp') {
                        $options['webp_quality'] = 80;
                    }

                    $imagine = \yii\imagine\Image::getImagine();
                    $image   = $imagine->open($file->tempName);

                    // Koreksi orientasi EXIF utk JPEG
                    if (in_array($ext, ['jpg','jpeg'], true)) {
                        $exif = @exif_read_data($file->tempName);
                        if (!empty($exif['Orientation'])) {
                            switch ((int)$exif['Orientation']) {
                                case 3: $image = $image->rotate(180); break;
                                case 6: $image = $image->rotate(90);  break;
                                case 8: $image = $image->rotate(-90); break;
                            }
                        }
                    }

                    if ($resize_width > 0 && $resize_height > 0) {
                        // Fit dalam box (tanpa crop)
                        $thumb = $image->thumbnail(
                            new \Imagine\Image\Box($resize_width, $resize_height),
                            \Imagine\Image\ManipulatorInterface::THUMBNAIL_INSET
                        );
                        $thumb->save($filePath, $options);
                    } else {
                        $resized = $image->resize(new \Imagine\Image\Box($targetW, $targetH));
                        $resized->save($filePath, $options);
                    }
                    $saved = true;
                } catch (\Throwable $e) {
                    // Fallback GD
                    $saved = $this->gdResizeSave($file->tempName, $filePath, $targetW, $targetH, $mime, $ext);
                }
            } else {
                $saved = $this->gdResizeSave($file->tempName, $filePath, $targetW, $targetH, $mime, $ext);
            }

            if (!$saved) {
                return Json::encode(['error' => 'Gagal memproses resize image.']);
            }

            $storedMime = $mime ?: FileHelper::getMimeType($filePath) ?: '';
            $storedSize = @filesize($filePath) ?: 0;

            if ($useObjectStorage) {
                $uploadResult = $this->fsUploadFromLocal($filePath, $storageKey, 'private');
                if (($uploadResult['ok'] ?? false) !== true) {
                    @unlink($filePath);
                    return Json::encode(['error' => 'Failed to upload file to storage.']);
                }
            }

            //////////////////SIMPAN KE DB////////////////////
            if($db){
                $model_upload = $id != 0 ? FileAsset::findOne($id) : null;
                if(!$model_upload){
                    $model_upload = new FileAsset();
                }
                $helper = new Helper();
                $crypt = $helper->crypt_str();

                $hash = $crypt->encrypt($safeName);
                $model_upload->hash = $hash;
                $model_upload->file_name = $safeName;
                $model_upload->file_path = $savedPath;
                $model_upload->tipe_file = $storedMime;
                $model_upload->ukuran = $storedSize;
                if ( !Yii::$app->user->getIsGuest() ){
                    $model_upload->id_user = Yii::$app->user->identity->id;
                }
                $model_upload->update_date = date('Y-m-d H:i:s');
                $model_upload->save(false);

                if ($useObjectStorage) {
                    @unlink($filePath);
                }

                return Json::encode([
                    'files' => [[
                        'name' => $hash,
                        'size' => $storedSize,
                        'uid' => $helper->encrypt_aes128cbc($model_upload->id),
                    ]],
                ]);
            }
            //////////////////SIMPAN KE DB////////////////////

            if ($useObjectStorage) {
                @unlink($filePath);
            }

            return Json::encode([
                'files' => [[
                    'name' => $safeName,
                    'size' => $storedSize,
                ]],
            ]);
        }///end resize image

        if ($file->saveAs($filePath, false)) {
            $storedMime = $mime;
            $storedSize = (int)$file->size;

            if ($useObjectStorage) {
                $uploadResult = $this->fsUploadFromLocal($filePath, $storageKey, 'private');
                @unlink($filePath);
                if (($uploadResult['ok'] ?? false) !== true) {
                    return Json::encode(['error' => 'Failed to upload file to storage.']);
                }
            } else {
                $storedMime = $mime ?: FileHelper::getMimeType($filePath) ?: '';
                $storedSize = @filesize($filePath) ?: (int)$file->size;
            }
            
             ///////////////////SIMPAN KE DB////////////////////
            if($db){
                $model_upload = $id != 0 ? FileAsset::findOne($id) : null;
                if(!$model_upload){
                    $model_upload = new FileAsset();
                }
                $helper = new Helper();
                $crypt = $helper->crypt_str();

                $hash = $crypt->encrypt($safeName);
                $model_upload->hash = $hash;
                $model_upload->file_name = $safeName;
                $model_upload->file_path = $savedPath;
                $model_upload->tipe_file = $storedMime;
                $model_upload->ukuran = $storedSize;
                if ( !Yii::$app->user->getIsGuest() ){
                    $model_upload->id_user = Yii::$app->user->identity->id;
                }
                $model_upload->update_date = date('Y-m-d H:i:s');
                $model_upload->save(false);

                return Json::encode([
                    'files' => [[
                        'name' => $hash,
                        'size' => $storedSize,
                        'uid' => $helper->encrypt_aes128cbc($model_upload->id),
                    ]],
                ]);
                
            }
            //////////////////SIMPAN KE DB////////////////////

            return Json::encode([
                    'files' => [
                        [
                            'name' => $safeName,
                            'size' => $storedSize,
                        ],
                    ],
                ]);
        }

        return Json::encode(['error' => 'Failed to save file.']);
    }


    private function gdResizeSave(string $src, string $dst, int $targetW, int $targetH, string $mime, string $ext): bool
    {
        try {
            switch ($mime) {
                case 'image/jpeg': $im = imagecreatefromjpeg($src); break;
                case 'image/png':  $im = imagecreatefrompng($src);  break;
                case 'image/gif':  $im = imagecreatefromgif($src);  break;
                case 'image/webp':
                    if (!function_exists('imagecreatefromwebp')) return false;
                    $im = imagecreatefromwebp($src);
                    break;
                default: return false;
            }
            if (!$im) return false;

            // EXIF orientasi utk JPEG
            if (in_array($ext, ['jpg','jpeg'], true) && function_exists('exif_read_data')) {
                $exif = @exif_read_data($src);
                if (!empty($exif['Orientation'])) {
                    switch ((int)$exif['Orientation']) {
                        case 3: $im = imagerotate($im, 180, 0); break;
                        case 6: $im = imagerotate($im, -90, 0); break;
                        case 8: $im = imagerotate($im, 90, 0);  break;
                    }
                }
            }

            $origW = imagesx($im);
            $origH = imagesy($im);

            // Jika salah satu 0, sesuaikan proporsi
            if ($targetW > 0 && $targetH === 0) {
                $ratio   = $targetW / $origW;
                $targetH = max(1, (int)round($origH * $ratio));
            } elseif ($targetH > 0 && $targetW === 0) {
                $ratio   = $targetH / $origH;
                $targetW = max(1, (int)round($origW * $ratio));
            } elseif ($targetW === 0 && $targetH === 0) {
                $targetW = $origW; $targetH = $origH;
            }

            $dstIm = imagecreatetruecolor($targetW, $targetH);

            // Transparansi untuk PNG/GIF
            if (in_array($mime, ['image/png','image/gif'], true)) {
                imagecolortransparent($dstIm, imagecolorallocatealpha($dstIm, 0, 0, 0, 127));
                imagealphablending($dstIm, false);
                imagesavealpha($dstIm, true);
            }

            imagecopyresampled($dstIm, $im, 0, 0, 0, 0, $targetW, $targetH, $origW, $origH);

            $ok = false;
            switch ($mime) {
                case 'image/jpeg': $ok = imagejpeg($dstIm, $dst, 85); break;
                case 'image/png':  $ok = imagepng($dstIm, $dst, 9);   break;
                case 'image/gif':  $ok = imagegif($dstIm, $dst);      break;
                case 'image/webp':
                    if (!function_exists('imagewebp')) { $ok = false; break; }
                    $ok = imagewebp($dstIm, $dst, 80);
                    break;
            }
            imagedestroy($im);
            imagedestroy($dstIm);
            return (bool)$ok;
        } catch (\Throwable $e) {
            return false;
        }
    }

}
