<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model for table "wilayah_provinsi"
 * @property string $code
 * @property string $name
 */
class WilayahProvinsi extends ActiveRecord
{
    public static function tableName()
    {
        return 'wilayah_provinsi';
    }

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function primaryKey()
    {
        return ['code'];
    }
}
