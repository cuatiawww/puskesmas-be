<?php

namespace app\controllers;

use Yii;
use app\models\SystemSetting;
use yii\web\Response;
use yii\helpers\FileHelper;

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
                    if (isset($post[$setting->key]) && $setting->value !== $post[$setting->key]) {
                        $setting->value = $post[$setting->key];
                        $hasChange = true;
                    }
                } else {
                    if (isset($post[$setting->key]) && $setting->value !== $post[$setting->key]) {
                        $setting->value = $post[$setting->key];
                        $hasChange = true;
                    }
                }

                if (!$hasChange) {
                    continue;
                }

                try {
                    $attributes = ['value'];
                    if ($setting->hasAttribute('updated_at')) {
                        $setting->updated_at = date('Y-m-d H:i:s');
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
