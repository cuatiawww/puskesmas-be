<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_activity_log".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $username
 * @property string $action
 * @property string|null $module
 * @property string $controller
 * @property string $action_id
 * @property string $route
 * @property string $url
 * @property string|null $target_model
 * @property string|null $target_id
 * @property string|null $changes
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $browser
 * @property string|null $platform
 * @property string $created_at
 */
class UserActivityLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_activity_log';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_user');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['action', 'controller', 'action_id', 'route', 'url'], 'required'],
            [['user_id'], 'integer'],
            [['url', 'changes', 'user_agent'], 'string'],
            [['created_at'], 'safe'],
            [['username', 'target_model', 'target_id'], 'string', 'max' => 255],
            [['action', 'browser', 'platform'], 'string', 'max' => 50],
            [['module', 'controller', 'action_id', 'ip_address'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'username' => 'Username',
            'action' => 'Tindakan',
            'module' => 'Modul',
            'controller' => 'Controller',
            'action_id' => 'Aksi ID',
            'route' => 'Rute',
            'url' => 'URL',
            'target_model' => 'Model Target',
            'target_id' => 'ID Target',
            'changes' => 'Perubahan',
            'ip_address' => 'Alamat IP',
            'user_agent' => 'User Agent',
            'browser' => 'Browser',
            'platform' => 'Sistem Operasi',
            'created_at' => 'Waktu Aktivitas',
        ];
    }
}
