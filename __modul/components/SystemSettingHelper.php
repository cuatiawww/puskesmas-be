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
     * Get setting URL for images/files
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

        // Add base_url
        $baseUrl = rtrim(Yii::$app->params['base_url'] ?? '', '/');
        $value = '/' . ltrim($value, '/');
        return $baseUrl . $value;
    }
}
