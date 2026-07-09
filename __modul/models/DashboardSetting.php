<?php

namespace app\models;

use Yii;
use yii\helpers\Json;
use app\models\SystemSetting;

class DashboardSetting
{
    /**
     * Get visible sections array for a given level ID
     */
    public static function getVisibleSections($levelId)
    {
        $setting = SystemSetting::findOne(['key' => "dashboard_setting_level_{$levelId}"]);
        if ($setting && $setting->value) {
            try {
                $sections = Json::decode($setting->value);
                if (is_array($sections)) {
                    return $sections;
                }
            } catch (\Exception $e) {
                // Fallback below
            }
        }
        return ['welcome_profile', 'user_detail', 'system_stats', 'quick_config', 'user_activities_stats'];
    }

    /**
     * Get quick access sub-modul IDs for a given level ID
     */
    public static function getQuickAccessModules($levelId)
    {
        $setting = SystemSetting::findOne(['key' => "quick_access_level_{$levelId}"]);
        if ($setting && $setting->value) {
            try {
                $ids = Json::decode($setting->value);
                if (is_array($ids)) {
                    return array_map('intval', $ids);
                }
            } catch (\Exception $e) {
                // Fallback below
            }
        }
        // Fallback for level 1: Persetujuan Akun (156), Level User (37), Navigasi (1), Modul (2)
        if ((int)$levelId === 1) {
            return [156, 37, 1, 2];
        }
        return [];
    }
}
