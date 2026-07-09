<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Beranda extends ActiveRecord
{
    /**
     * Tidak memerlukan tabel khusus, hanya untuk query data beranda
     */
    public static function tableName()
    {
        return '{{%user}}'; // dummy table
    }

    /**
     * Get beranda statistics for User Management
     * @return array
     */
    public static function getStats()
    {
        try {
            $stats = [];

            // Total Active Users
            $stats['total_users'] = (int) Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM public."user" WHERE is_active = true'
            )->queryScalar();

            // Total Pending Registrations
            $stats['total_pending_registrations'] = (int) Yii::$app->db->createCommand(
                "SELECT COUNT(*) FROM public.user_registration WHERE status = 'pending_approval'"
            )->queryScalar();

            // Total Provinsi
            $stats['total_provinsi'] = (int) Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM public.wilayah_provinsi'
            )->queryScalar();

            // Total Kabupaten/Kota
            $stats['total_kabupaten'] = (int) Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM public.wilayah_kabupaten'
            )->queryScalar();

            // Total Kecamatan
            $stats['total_kecamatan'] = (int) Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM public.wilayah_kecamatan'
            )->queryScalar();

            // Total User Activities Logged Today
            $stats['total_activities_today'] = (int) Yii::$app->db->createCommand(
                "SELECT COUNT(*) FROM public.user_activity_log WHERE created_at::date = CURRENT_DATE"
            )->queryScalar();

            return $stats;
        } catch (\Exception $e) {
            Yii::error('Error getting beranda stats: ' . $e->getMessage());
            return [
                'total_users' => 0,
                'total_pending_registrations' => 0,
                'total_provinsi' => 0,
                'total_kabupaten' => 0,
                'total_kecamatan' => 0,
                'total_activities_today' => 0,
            ];
        }
    }
}
