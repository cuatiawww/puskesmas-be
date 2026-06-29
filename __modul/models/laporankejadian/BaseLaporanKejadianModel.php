<?php

namespace app\models\laporankejadian;

use yii\db\ActiveRecord;

abstract class BaseLaporanKejadianModel extends ActiveRecord
{
    protected static function intFields(array $fields): array
    {
        return [$fields, 'integer'];
    }

    protected static function stringFields(array $fields, int $max = 255): array
    {
        return [$fields, 'string', 'max' => $max];
    }

    protected static function safeFields(array $fields): array
    {
        return [$fields, 'safe'];
    }
}

