<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "system_setting".
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $label
 * @property string $type
 * @property string|null $category
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class SystemSetting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'system_setting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key', 'label'], 'required'],
            [['value'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['key', 'type', 'category'], 'string', 'max' => 100],
            [['label'], 'string', 'max' => 255],
            [['key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'value' => 'Value',
            'label' => 'Label',
            'type' => 'Type',
            'category' => 'Category',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
