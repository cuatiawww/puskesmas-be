<?php

namespace app\models;

use Yii;

class SubModul extends \yii\db\ActiveRecord
{
    const SCENARIO_MODUL = 'modul';
    const SCENARIO_SUB_MODUL = 'sub-modul';

    public static function tableName()
    {
        return 'sub_modul';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_user');
    }

    public function rules()
    {
        return [
            [['modul_id', 'nama_sub_modul', 'label'], 'required'],
            [['parent_id'], 'required', 'on' => self::SCENARIO_SUB_MODUL, 'message' => 'Modul wajib dipilih.'],
            [['modul_id', 'parent_id', 'urutan'], 'integer'],
            [['route', 'icon', 'label', 'nama_sub_modul'], 'string'],
            [['is_active'], 'boolean'],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_MODUL] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'modul_id' => 'Navigasi',
            'parent_id' => 'Modul',
            'nama_sub_modul' => 'Nama',
            'label' => 'Label',
            'route' => 'Route',
            'icon' => 'Ikon',
            'urutan' => 'Urutan',
            'is_active' => 'Status Aktif',
        ];
    }

    /**
     * Get parent modul
     */
    public function getModul()
    {
        return $this->hasOne(Modul::class, ['id' => 'modul_id']);
    }

    /**
     * Get parent sub-modul if exists
     */
    public function getParentSubModul()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    /**
     * Get children sub-modul
     */
    public function getChildren()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id']);
    }

    public function getNamaParent()
    {
        return $this->parentSubModul ? $this->parentSubModul->label : null;
    }
}
