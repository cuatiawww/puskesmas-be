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
            [['key'], 'string', 'max' => 100],
            [['type', 'category'], 'string', 'max' => 50],
            [['label'], 'string', 'max' => 255],
            [['key'], 'unique'],
        ];
    }

    /**
     * Auto-set updated_at when the column exists.
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->hasAttribute('updated_at')) {
                $this->updated_at = date('Y-m-d H:i:s');
            }
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'key'        => 'Key',
            'value'      => 'Value',
            'label'      => 'Label',
            'type'       => 'Type',
            'category'   => 'Category',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
