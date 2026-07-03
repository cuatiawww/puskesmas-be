<?php

namespace app\controllers;

use Yii;
use app\models\SystemSetting;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\helpers\FileHelper;

class SystemSettingController extends BaseController
{
    private function storageEnabled(): bool
    {
        $storage = Yii::$app->params['storage'] ?? [];
        return Yii::$app->has('fs') && (($storage['driver'] ?? '') === 's3');
    }

    private function fsUploadFromLocal(string $localPath, string $key): bool
    {
        if (!is_file($localPath) || !is_readable($localPath)) {
            return false;
        }
        $stream = @fopen($localPath, 'rb');
        if (!$stream) {
            return false;
        }
        try {
            Yii::$app->fs->writeStream($key, $stream, ['visibility' => 'public']);
        } catch (\Throwable $e) {
            if (is_resource($stream)) fclose($stream);
            Yii::error('S3 upload error (SystemSetting): ' . $e->getMessage(), __METHOD__);
            return false;
        }
        if (is_resource($stream)) fclose($stream);
        return true;
    }

    /**
     * Display configuration form and handle updates
     */
    public function actionIndex()
    {
        $settings = SystemSetting::find()->all();

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post('Setting', []);
            $success = true;
            $useStorage = $this->storageEnabled();

            foreach ($settings as $setting) {
                if ($setting->type === 'image') {
                    $file = UploadedFile::getInstanceByName("SettingFile[{$setting->key}]");
                    if ($file) {
                        // Cek error upload PHP
                        if (!empty($file->error) && $file->error !== UPLOAD_ERR_OK) {
                            $success = false;
                            Yii::error("Upload error PHP [{$setting->key}]: code={$file->error}", __METHOD__);
                            continue;
                        }

                        $ext = strtolower($file->extension);
                        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        if (!in_array($ext, $allowedExts, true)) {
                            $success = false;
                            continue;
                        }

                        $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                        $uploadSubPath = 'uploads/system-setting';
                        $key = $uploadSubPath . '/' . $filename;

                        if ($useStorage) {
                            // Upload ke S3 via /tmp sebagai staging
                            $tmpDir = rtrim(sys_get_temp_dir(), '/') . '/yii_upload';
                            if (!is_dir($tmpDir)) {
                                @mkdir($tmpDir, 0775, true);
                            }
                            $tmpPath = $tmpDir . '/' . $filename;

                            if ($file->saveAs($tmpPath, false)) {
                                $uploaded = $this->fsUploadFromLocal($tmpPath, $key);
                                @unlink($tmpPath);

                                if ($uploaded) {
                                    $setting->value = $key;
                                } else {
                                    $success = false;
                                    Yii::error("S3 upload gagal [{$setting->key}]: key={$key}", __METHOD__);
                                    continue;
                                }
                            } else {
                                $success = false;
                                Yii::error("Gagal simpan ke tmp [{$setting->key}]: {$tmpPath}", __METHOD__);
                                continue;
                            }
                        } else {
                            // Fallback: simpan ke local filesystem
                            $dir = Yii::getAlias('@webroot/' . $uploadSubPath . '/');
                            if (!is_dir($dir)) {
                                FileHelper::createDirectory($dir);
                            }
                            $filePath = $dir . $filename;
                            if ($file->saveAs($filePath)) {
                                $setting->value = $uploadSubPath . '/' . $filename;
                            } else {
                                $success = false;
                                Yii::error("Gagal simpan ke lokal [{$setting->key}]: {$filePath}", __METHOD__);
                                continue;
                            }
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
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Konfigurasi sistem berhasil diperbarui',
                ]);
            } else {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'error',
                    'title' => 'Gagal',
                    'text' => 'Beberapa konfigurasi gagal diperbarui. Cek log untuk detail.',
                ]);
            }

            return $this->refresh();
        }

        return $this->render('index', [
            'settings' => $settings,
        ]);
    }
}
