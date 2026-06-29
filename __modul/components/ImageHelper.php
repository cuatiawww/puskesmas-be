<?php

namespace app\components;

use Yii;

class ImageHelper
{
    /**
     * Get profile photo URL
     * @param string|null $fotoPath The file path stored in database (e.g., 'uploads/profile-photos/profile_1_1234567890.jpg')
     * @return string The complete URL to display
     */
    public static function getProfilePhotoUrl($fotoPath)
    {
        // Get base URL - ensure it has protocol
        $baseUrl = Yii::$app->params['base_url'] ?? '';
        if (strpos($baseUrl, '//') === 0) {
            $baseUrl = 'http:' . $baseUrl;
        }
        $baseUrl = rtrim($baseUrl, '/');
        
        $defaultPhoto = $baseUrl . '/app_asset/images/user/avatar-1.jpg';
        
        // If no foto path provided, return default
        if (empty($fotoPath)) {
            return $defaultPhoto;
        }
        
        // Normalize path - trim whitespace
        $fotoPath = trim((string)$fotoPath);
        if (empty($fotoPath)) {
            return $defaultPhoto;
        }
        
        // Remove leading slash for consistency
        $fotoPath = ltrim($fotoPath, '/');
        
        // Try to verify file exists
        $appRoot = dirname(dirname(Yii::getAlias('@app')));
        $filePath = $appRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $fotoPath);
        
        // If file doesn't exist, return default
        if (!@file_exists($filePath)) {
            return $defaultPhoto;
        }
        
        // Build URL: baseUrl + / + fotoPath (with forward slashes)
        return $baseUrl . '/' . $fotoPath;
    }
}
