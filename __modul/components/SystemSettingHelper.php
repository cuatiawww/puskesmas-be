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
     * @return string
     */
    public static function getAssetUrl($key, $defaultPath = '')
    {
        $value = self::get($key);
        if (empty($value)) {
            $value = $defaultPath;
        }

        // If it starts with http, https, or //, return as is
        if (preg_match('~^(https?:)?//~i', $value)) {
            return $value;
        }

        // Jika value adalah hash (dari FileUploadController dengan db=true)
        // Hash format: panjang > 30 karakter dan tidak mengandung slash
        if (!str_contains($value, '/') && strlen($value) > 20) {
            $baseUrl = rtrim(Yii::$app->params['base_url'] ?? '', '/');
            return $baseUrl . '/file-upload/render?inline=1&uxid=' . urlencode($value);
        }

        // Jika value adalah path file lokal (uploads/system-setting/xxx.jpg)
        // Serve via system-setting/serve-image untuk bypass Nginx routing
        if (str_starts_with($value, 'uploads/system-setting/')) {
            $baseUrl = rtrim(Yii::$app->params['base_url'] ?? '', '/');
            return $baseUrl . '/system-setting/serve-image?file=' . urlencode($value);
        }

        // Default: path statis (app_asset, dll)
        $baseUrl = rtrim(Yii::$app->params['base_url'] ?? '', '/');
        $value = '/' . ltrim($value, '/');
        return $baseUrl . $value;
    }
}
