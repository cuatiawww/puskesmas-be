<?php

namespace app\components;

use Yii;
use app\models\SystemSetting;

class SystemSettingHelper
{
    /**
     * Get system setting value by key
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        try {
            $setting = SystemSetting::findOne(['key' => $key]);
            if ($setting) {
                return $setting->value;
            }
        } catch (\Exception $e) {
            // fallback to default during setup or db issues
        }
        return $default;
    }

    /**
     * Get URL for image settings.
     * - If value is a file_asset hash (stored via FileUploadController), return render URL
     * - If value is a plain path (uploads/...), return web-accessible URL
     * - Otherwise return default
     *
     * @param string $key
     * @param string $defaultPath
     * @param bool $absolute Whether to return absolute URL (needed for emails)
     * @return string
     */
    public static function getAssetUrl($key, $defaultPath = '', $absolute = false)
    {
        $value = self::get($key);
        if (empty($value)) {
            $value = $defaultPath;
        }

        $baseUrl = rtrim(Yii::$app->params['base_url'] ?? '', '/');
        if (empty($baseUrl) && Yii::$app->has('request') && Yii::$app->request instanceof \yii\web\Request) {
            $baseUrl = rtrim(Yii::$app->request->baseUrl, '/');
        }

        if ($absolute) {
            if (!preg_match('~^(https?:)?//~i', $baseUrl)) {
                $hostInfo = '';
                if (Yii::$app->has('request') && Yii::$app->request instanceof \yii\web\Request) {
                    $hostInfo = rtrim(Yii::$app->request->hostInfo, '/');
                } else {
                    $hostInfo = 'http://localhost';
                }
                $baseUrl = $hostInfo . $baseUrl;
            }
        }

        // If it starts with http, https, or //, return as is
        if (preg_match('~^(https?:)?//~i', $value)) {
            return $value;
        }

        // Jika value adalah hash (dari FileUploadController dengan db=true)
        // Hash format: panjang > 30 karakter dan tidak mengandung slash
        if (!str_contains($value, '/') && strlen($value) > 20) {
            return $baseUrl . '/file-upload/render?inline=1&uxid=' . urlencode($value);
        }

        // Jika value adalah path file lokal (uploads/system-setting/xxx.jpg)
        // Serve via system-setting/serve-image untuk bypass Nginx routing
        if (str_starts_with($value, 'uploads/system-setting/')) {
            return $baseUrl . '/system-setting/serve-image?file=' . urlencode($value);
        }

        // Default: path statis (app_asset, dll)
        $value = '/' . ltrim($value, '/');
        if (!empty($baseUrl) && str_starts_with($value, $baseUrl . '/')) {
            // Jika value sudah mengandung baseUrl, tapi kita butuh absolute url
            if ($absolute && !str_starts_with($value, 'http')) {
                // Remove baseUrl dari awal value sebelum digabungkan dengan baseUrl absolute
                $value = substr($value, strlen($baseUrl));
                $value = '/' . ltrim($value, '/');
            } else {
                return $value;
            }
        }
        return $baseUrl . $value;
    }
}
