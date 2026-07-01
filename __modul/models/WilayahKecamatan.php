<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model for table "wilayah_kecamatan"
 * @property string $code
 * @property string $name
 * @property string $parent_code
 */
class WilayahKecamatan extends ActiveRecord
{
    public static function tableName()
    {
        return 'wilayah_kecamatan';
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
