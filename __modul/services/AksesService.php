<?php

namespace app\services;

use Yii;
use app\models\User;
use app\models\HakAkses;

/**
 * Service untuk check hak akses user
 */
class AksesService
{
    /**
     * Check apakah user punya akses terhadap modul
     * 
     * @param int|null $userId - jika null, gunakan user yang login sekarang
     * @param int $modulId - ID modul
     * @param string $type - 'view', 'create', 'update', 'delete', 'print'
     * @return bool
     */
    public static function cekAkses($userId = null, $modulId, $type = 'view')
    {
        if ($userId === null) {
            if (Yii::$app->user->isGuest) {
                return false;
            }
            $userId = Yii::$app->user->id;
        }

        // Get user
        $user = User::findOne($userId);
        if (!$user) {
            return false;
        }

        // Get user's level
        $levelId = $user->level_user_id ?? $user->id_user_level;
        if (!$levelId) {
            return false;
        }

        // Get hak akses untuk level ini
        $hakAkses = HakAkses::findOne([
            'level_user_id' => $levelId,
            'modul_id' => $modulId
        ]);

        if (!$hakAkses) {
            return false;
        }

        // Check type
        $fieldMap = [
            'view' => 'akses_view',
            'create' => 'akses_create',
            'update' => 'akses_update',
            'delete' => 'akses_delete',
            'print' => 'akses_print',
        ];

        $field = $fieldMap[$type] ?? null;
        if (!$field) {
            return false;
        }

        return (bool)$hakAkses->$field;
    }

    /**
     * Get semua akses user untuk modul tertentu
     * 
     * @param int|null $userId
     * @param int $modulId
     * @return array - ['view' => bool, 'create' => bool, ...]
     */
    public static function getAkses($userId = null, $modulId)
    {
        if ($userId === null) {
            $userId = Yii::$app->user->id;
        }

        $user = User::findOne($userId);
        if (!$user) {
            return [];
        }

        $levelId = $user->level_user_id ?? $user->id_user_level;
        $hakAkses = HakAkses::findOne([
            'level_user_id' => $levelId,
            'modul_id' => $modulId
        ]);

        if (!$hakAkses) {
            return [
                'view' => false,
                'create' => false,
                'update' => false,
                'delete' => false,
                'print' => false,
            ];
        }

        return [
            'view' => (bool)$hakAkses->akses_view,
            'create' => (bool)$hakAkses->akses_create,
            'update' => (bool)$hakAkses->akses_update,
            'delete' => (bool)$hakAkses->akses_delete,
            'print' => (bool)$hakAkses->akses_print,
        ];
    }

    /**
     * Get semua modul yang bisa diakses oleh user
     * 
     * @param int|null $userId
     * @param string|null $type - filter hanya modul dengan akses tertentu
     * @return array - [modulId => modulName, ...]
     */
    public static function getModulAkses($userId = null, $type = null)
    {
        if ($userId === null) {
            $userId = Yii::$app->user->id;
        }

        $user = User::findOne($userId);
        if (!$user) {
            return [];
        }

        $levelId = $user->level_user_id ?? $user->id_user_level;

        $query = HakAkses::find()
            ->where(['level_user_id' => $levelId])
            ->joinWith('modul');

        if ($type) {
            $fieldMap = [
                'view' => 'akses_view',
                'create' => 'akses_create',
                'update' => 'akses_update',
                'delete' => 'akses_delete',
                'print' => 'akses_print',
            ];
            $field = $fieldMap[$type] ?? null;
            if ($field) {
                $query->andWhere(['hak_akses.' . $field => true]);
            }
        }

        $data = [];
        foreach ($query->all() as $item) {
            $data[$item->modul_id] = $item->modul->nama_modul;
        }

        return $data;
    }
}
