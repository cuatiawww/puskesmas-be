<?php

namespace app\controllers;

use Yii;
use app\models\SystemSetting;
use yii\web\Response;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;
use app\models\file_upload\FileAsset;
use app\components\Helper;
use app\components\TimeHelper;

class SystemSettingController extends BaseController
{
    /**
     * serve-image harus bisa diakses tanpa login
     * (gambar tampil di halaman login yang belum auth)
     */
    public function isActionPublic(string $actionId): bool
    {
        return $actionId === 'serve-image';
    }

    private function storageEnabled(): bool
    {
        $storage = Yii::$app->params['storage'] ?? [];
        return Yii::$app->has('fs') && (($storage['driver'] ?? '') === 's3');
    }

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

        $useStorage = $this->storageEnabled();
        if ($useStorage) {
            try {
                if (Yii::$app->has('fs') && Yii::$app->fs->fileExists($relativePath)) {
                    $stream = Yii::$app->fs->readStream($relativePath);
                    if (is_resource($stream)) {
                        $mime = FileHelper::getMimeTypeByExtension($relativePath) ?: 'application/octet-stream';
                        Yii::$app->response->format = Response::FORMAT_RAW;
                        Yii::$app->response->headers->set('Content-Type', $mime);
                        Yii::$app->response->headers->set('Cache-Control', 'public, max-age=86400');
                        Yii::$app->response->headers->set('X-Content-Type-Options', 'nosniff');
                        return Yii::$app->response->sendStreamAsFile($stream, basename($relativePath), [
                            'mimeType' => $mime,
                            'inline' => true,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Yii::error("Gagal membaca file dari S3 di serve-image: " . $e->getMessage(), __METHOD__);
            }
            throw new \yii\web\NotFoundHttpException('File tidak ditemukan di storage server.');
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
        $settings = SystemSetting::find()->orderBy(['id' => SORT_ASC])->all();

        if (Yii::$app->request->isPost) {
            $post          = Yii::$app->request->post('Setting', []);
            $processedKeys = [];
            $success       = true;

            foreach ($settings as $setting) {
                if (isset($processedKeys[$setting->key])) {
                    Yii::warning("Duplicate system_setting key dilewati: {$setting->key} (id={$setting->id})", __METHOD__);
                    continue;
                }
                $processedKeys[$setting->key] = true;

                $hasChange = false;

                if ($setting->type === 'image') {
                    $uploadedFile = UploadedFile::getInstanceByName("SettingFile[{$setting->key}]");
                    if ($uploadedFile) {
                        $ext = strtolower($uploadedFile->extension);
                        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        if (in_array($ext, $allowedExts, true)) {
                            $mime = FileHelper::getMimeType($uploadedFile->tempName) ?: 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
                            
                            $safeName = uniqid('setting_', true) . '.' . $ext;
                            $uploadPath = '/uploads/system-setting';
                            $savedPath = $uploadPath;
                            
                            $useObjectStorage = $this->storageEnabled();
                            $storageKey = 'uploads/system-setting/' . $safeName;
                            
                            $uploadedSuccessfully = false;
                            $storedSize = $uploadedFile->size;
                            
                            if ($useObjectStorage) {
                                $stream = @fopen($uploadedFile->tempName, 'rb');
                                if ($stream) {
                                    try {
                                        Yii::$app->fs->writeStream($storageKey, $stream, ['visibility' => 'private']);
                                        $uploadedSuccessfully = true;
                                    } catch (\Throwable $e) {
                                        Yii::error("Gagal upload setting {$setting->key} ke S3: " . $e->getMessage(), __METHOD__);
                                    }
                                    if (is_resource($stream)) {
                                        fclose($stream);
                                    }
                                }
                            } else {
                                $directory = Yii::getAlias('@webroot' . $uploadPath . '/');
                                if (!is_dir($directory)) {
                                    FileHelper::createDirectory($directory);
                                }
                                $filePath = $directory . $safeName;
                                if ($uploadedFile->saveAs($filePath, false)) {
                                    $uploadedSuccessfully = true;
                                    $storedSize = @filesize($filePath) ?: $uploadedFile->size;
                                }
                            }
                            
                            if ($uploadedSuccessfully) {
                                $model_upload = new FileAsset();
                                $helper = new Helper();
                                $crypt = $helper->crypt_str();
                                
                                $hash = $crypt->encrypt($safeName);
                                $model_upload->hash = $hash;
                                $model_upload->file_name = $safeName;
                                $model_upload->file_path = $savedPath;
                                $model_upload->tipe_file = $mime;
                                $model_upload->ukuran = (string)$storedSize;
                                if (!Yii::$app->user->getIsGuest()) {
                                    $model_upload->id_user = Yii::$app->user->identity->id;
                                }
                                $model_upload->update_date = TimeHelper::now();
                                if ($model_upload->save(false)) {
                                    $setting->value = $hash;
                                    $hasChange = true;
                                    Yii::info("Setting {$setting->key} diperbarui dengan file baru: {$safeName} (hash={$hash})", __METHOD__);
                                } else {
                                    $success = false;
                                    Yii::error("Gagal menyimpan FileAsset untuk setting {$setting->key}", __METHOD__);
                                }
                            } else {
                                $success = false;
                                Yii::error("Gagal upload file untuk setting {$setting->key}", __METHOD__);
                            }
                        } else {
                            $success = false;
                            Yii::$app->session->setFlash('swal', [
                                'icon'  => 'error',
                                'title' => 'Gagal',
                                'text'  => 'Format file tidak diizinkan. Gunakan JPG, PNG, atau WebP.',
                            ]);
                        }
                    } else {
                        if (isset($post[$setting->key])) {
                            $val = $post[$setting->key];
                            if (is_array($val)) {
                                $val = \yii\helpers\Json::encode(array_values($val));
                            }
                            if ($setting->value !== $val) {
                                $setting->value = $val;
                                $hasChange = true;
                            }
                        }
                    }
                } else {
                    if (isset($post[$setting->key])) {
                        $val = $post[$setting->key];
                        if (is_array($val)) {
                            $val = \yii\helpers\Json::encode(array_values($val));
                        }
                        if ($setting->value !== $val) {
                            $setting->value = $val;
                            $hasChange = true;
                        }
                    }
                }

                if (!$hasChange) {
                    continue;
                }

                try {
                    $attributes = ['value'];
                    if ($setting->hasAttribute('updated_at')) {
                        $setting->updated_at = TimeHelper::now();
                        $attributes[] = 'updated_at';
                    }
                    $saved = $setting->save(false, $attributes);
                } catch (\Throwable $ex) {
                    $success = false;
                    Yii::error("Exception save setting [{$setting->key}]: " . $ex->getMessage() . ' | ' . $ex->getFile() . ':' . $ex->getLine(), __METHOD__);
                    continue;
                }

                if (!$saved) {
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
