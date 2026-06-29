<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string|null $email
 * @property string|null $nama_lengkap
 * @property string|null $no_telpon
 * @property string|null $foto_profil
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $level_user_id
 * @property int|null $id_user_level
 * @property int|null $master_wilayah_id
 * @property int|null $kd_prop
 * @property int|null $kd_kab
 * @property int|null $kd_kecamatan
 * @property bool|null $is_active
 * @property int|null $status
 * @property string|null $kode_kloter
 * @property string|null $embarkasi
 * @property int|null $tbl_wilayah_id
 * @property string|null $password_reset_otp
 * @property string|null $password_reset_otp_expires_at
 * @property string|null $password_reset_requested_at
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * FIX: Eksplisit primary key supaya getId() dan save() bekerja benar
     */
    public static function primaryKey()
    {
        return ['id'];
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
            [['username', 'password'], 'required'],
            ['username', 'unique', 'message' => 'Username sudah terdaftar.'],
            ['email', 'unique', 'message' => 'Email sudah terdaftar. Silakan gunakan email lain.'],
            [['username', 'password', 'email', 'nama_lengkap', 'no_telpon', 'created_at', 'updated_at', 'foto_profil'], 'string'],
            [['level_user_id', 'id_user_level', 'master_wilayah_id', 'tbl_wilayah_id'], 'integer'],
            [['kd_prop', 'kd_kab', 'kd_kecamatan'], 'safe'],
            [['is_active'], 'boolean'],
            [['kode_kloter', 'embarkasi'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'username'       => 'Username',
            'password'       => 'Password',
            'email'          => 'Email',
            'nama_lengkap'   => 'Nama Lengkap',
            'no_telpon'      => 'No Telepon',
            'foto_profil'    => 'Foto Profil',
            'created_at'     => 'Created At',
            'updated_at'     => 'Updated At',
            'level_user_id'  => 'Level User',
            'id_user_level'  => 'Level User',
            'master_wilayah_id' => 'Master Wilayah',
            'kd_prop'        => 'Kd Prop',
            'kd_kab'         => 'Kd Kab',
            'kd_kecamatan'   => 'Kd Kecamatan',
            'is_active'      => 'Status Aktif',
            'status'         => 'Status',
            'kode_kloter'    => 'Kode Kloter',
            'embarkasi'      => 'Embarkasi',
            'tbl_wilayah_id' => 'Wilayah',
        ];
    }

    // =========================================================
    // FIX: id_user_level & level_user_id getter/setter tetap
    // =========================================================

    public function getIdUserLevel()
    {
        if ($this->hasAttribute('id_user_level')) {
            return $this->getAttribute('id_user_level');
        }
        return $this->hasAttribute('level_user_id') ? $this->getAttribute('level_user_id') : null;
    }

    public function setIdUserLevel($value): void
    {
        if ($this->hasAttribute('id_user_level')) {
            $this->setAttribute('id_user_level', $value);
        }
        if ($this->hasAttribute('level_user_id')) {
            $this->setAttribute('level_user_id', $value);
        }
    }

    public function getLevelUserId()
    {
        return $this->getIdUserLevel();
    }

    public function setLevelUserId($value): void
    {
        $this->setIdUserLevel($value);
    }

    // =========================================================
    // Relations
    // =========================================================

    public function getLevelUser()
    {
        $foreignKey = $this->hasAttribute('id_user_level') ? 'id_user_level' : 'level_user_id';
        return $this->hasOne(\app\models\level_user\LevelUser::class, ['id' => $foreignKey]);
    }

    public function getMasterWilayah()
    {
        return $this->hasOne(\app\models\MasterWilayah::class, ['id' => 'master_wilayah_id']);
    }

    // =========================================================
    // IdentityInterface
    // =========================================================

    public static function findIdentity($id)
    {
        return static::findOne(['id' => (int) $id]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException();
    }

    /**
     * FIX: Eksplisit return kolom 'id', bukan getPrimaryKey() yang bisa ambigu
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    public function getAuthKey()
    {
        return $this->hasAttribute('auth_key') ? $this->auth_key : null;
    }

    public function validateAuthKey($authKey)
    {
        if ($this->hasAttribute('auth_key')) {
            return $this->auth_key === $authKey;
        }
        return true;
    }

    // =========================================================
    // Auth helpers
    // =========================================================

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function validatePassword($password)
    {
        $storedPassword = (string) $this->password;

        if ($storedPassword === '') {
            return false;
        }

        try {
            if (Yii::$app->getSecurity()->validatePassword($password, $storedPassword)) {
                return true;
            }
        } catch (\Throwable $e) {
            // Fallback ke format password legacy di bawah.
        }

        if ($this->isLegacyMd5Password($storedPassword) && hash_equals($storedPassword, md5($password))) {
            $this->password = Yii::$app->security->generatePasswordHash($password);
            $this->save(false, ['password']);
            return true;
        }

        return false;
    }

    protected function isLegacyMd5Password(string $hash): bool
    {
        return (bool) preg_match('/^[a-f0-9]{32}$/i', $hash);
    }
}