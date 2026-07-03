<?php

namespace app\controllers;

use Yii;
use app\models\SystemSetting;
use yii\web\UploadedFile;

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
                        $dir = Yii::getAlias('@app/../uploads/system-setting/');
                        if (!is_dir($dir)) {
                            mkdir($dir, 0755, true);
                        }
                        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->name);
                        if ($file->saveAs($dir . $filename)) {
                            // Save path relative to web root or workspace root
                            $setting->value = 'uploads/system-setting/' . $filename;
                        } else {
                            $success = false;
                        }
                    }
                } else {
                    if (isset($post[$setting->key])) {
                        $setting->value = $post[$setting->key];
                    }
                }

                if (!$setting->save()) {
                    $success = false;
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
                    'text' => 'Beberapa konfigurasi gagal diperbarui',
                ]);
            }

            return $this->refresh();
        }

        return $this->render('index', [
            'settings' => $settings,
        ]);
    }
}
