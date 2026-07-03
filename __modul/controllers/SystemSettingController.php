<?php

namespace app\controllers;

use Yii;
use app\models\SystemSetting;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class SystemSettingController extends BaseController
{
    /**
     * Display configuration form and handle updates
     */
    public function actionIndex()
    {
        $settings = SystemSetting::find()->all();

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post('Setting', []);
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

                        $ext      = strtolower($file->extension);
                        $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        if (!in_array($ext, $allowed, true)) {
                            $success = false;
                            Yii::error("Ekstensi tidak diizinkan [{$setting->key}]: $ext", __METHOD__);
                            continue;
                        }

                        $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

                        // Simpan langsung ke /var/www/html/uploads/system-setting/
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
                            // Berhasil simpan ke local
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
