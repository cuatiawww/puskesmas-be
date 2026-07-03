<?php

namespace app\controllers;

use Yii;
use app\models\SystemSetting;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\helpers\FileHelper;

class SystemSettingController extends BaseController
{
    /**
     * Serve uploaded image via PHP (bypass Nginx static routing issue)
     * URL: /system-setting/serve-image?file=uploads/system-setting/xxx.jpg
     */
    public function actionServeImage()
    {
        $relativePath = Yii::$app->request->get('file', '');

        // Keamanan: hanya izinkan path di bawah uploads/system-setting/
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        if (!preg_match('#^uploads/system-setting/[a-zA-Z0-9._-]+$#', $relativePath)) {
            throw new \yii\web\ForbiddenHttpException('Akses ditolak.');
        }

        $filePath = rtrim(Yii::getAlias('@webroot'), '/') . '/' . $relativePath;
        if (!is_file($filePath)) {
            throw new \yii\web\NotFoundHttpException('File tidak ditemukan.');
        }

        $mime = FileHelper::getMimeType($filePath) ?: 'application/octet-stream';
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', $mime);
        Yii::$app->response->headers->set('Cache-Control', 'public, max-age=86400');
        Yii::$app->response->headers->set('X-Content-Type-Options', 'nosniff');
        return file_get_contents($filePath);
    }

    /**
     * Display configuration form and handle updates
     */
    public function actionIndex()
    {
        $settings = SystemSetting::find()->all();

        if (Yii::$app->request->isPost) {
            $post    = Yii::$app->request->post('Setting', []);
            $success = true;

            foreach ($settings as $setting) {
                if ($setting->type === 'image') {
                    $file = UploadedFile::getInstanceByName("SettingFile[{$setting->key}]");
                    if ($file) {
                        // Cek error PHP upload
                        if (!empty($file->error) && $file->error !== UPLOAD_ERR_OK) {
                            $success = false;
                            Yii::error("Upload error [{$setting->key}]: PHP error code {$file->error}", __METHOD__);
                            continue;
                        }

                        $ext     = strtolower($file->extension);
                        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        if (!in_array($ext, $allowed, true)) {
                            $success = false;
                            Yii::error("Ekstensi tidak diizinkan [{$setting->key}]: $ext", __METHOD__);
                            continue;
                        }

                        $filename  = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                        $uploadDir = rtrim(Yii::getAlias('@webroot'), '/') . '/uploads/system-setting/';

                        if (!is_dir($uploadDir)) {
                            if (!FileHelper::createDirectory($uploadDir, 0775, true)) {
                                Yii::error("Gagal buat folder: $uploadDir", __METHOD__);
                                $success = false;
                                continue;
                            }
                        }

                        $filePath = $uploadDir . $filename;

                        if ($file->saveAs($filePath, false)) {
                            // Simpan path relatif ke DB
                            $setting->value = 'uploads/system-setting/' . $filename;
                            Yii::info("File berhasil disimpan: $filePath", __METHOD__);
                        } else {
                            $success = false;
                            Yii::error("Gagal simpan file [{$setting->key}]: $filePath | PHP error: {$file->error}", __METHOD__);
                            continue;
                        }
                    }
                } else {
                    if (isset($post[$setting->key])) {
                        $setting->value = $post[$setting->key];
                    }
                }

                if (!$setting->save()) {
                    $success = false;
                    Yii::error("Gagal save setting [{$setting->key}]: " . json_encode($setting->errors), __METHOD__);
                }
            }

            if ($success) {
                Yii::$app->session->setFlash('swal', [
                    'icon'  => 'success',
                    'title' => 'Berhasil',
                    'text'  => 'Konfigurasi sistem berhasil diperbarui',
                ]);
            } else {
                Yii::$app->session->setFlash('swal', [
                    'icon'  => 'error',
                    'title' => 'Gagal',
                    'text'  => 'Beberapa konfigurasi gagal diperbarui. Cek log untuk detail.',
                ]);
            }

            return $this->refresh();
        }

        return $this->render('index', [
            'settings' => $settings,
        ]);
    }
}
