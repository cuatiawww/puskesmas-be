<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model for table "wilayah_kabupaten"
 * @property string $code
 * @property string $name
 * @property string $parent_code
 */
class WilayahKabupaten extends ActiveRecord
{
    public static function tableName()
    {
        return 'wilayah_kabupaten';
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
