<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $fio
 * @property string $password
 * @property string $date_of_birth
 * @property string $tel
 * @property int $role_id
 *
 * @property Reception[] $receptions
 * @property Role $role
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    public function __toString()
    {
        return $this->fio;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['login', 'password', 'phone', 'role_id'], 'required'],
            [['date_of_birth'], 'safe'],
            [['role_id'], 'integer'],
            [['login'], 'string', 'max' => 511],
            [['password', 'tel'], 'string', 'max' => 255],
            [['phone'], 'unique',],
            [['phone'], 'match', 'pattern' => '/^\d{11}$/', 'message' => "неверный формат телефона"],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => Role::class, 'targetAttribute' => ['role_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'логин',
            'fio' => 'ФИО',
            'password' => 'пароль',
            'phone' => 'телефон',
            'role_id' => 'Role ID',
        ];
    }

    /**
     * Gets query for [[Receptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReceptions()
    {
        return $this->hasMany(Reception::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Role]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }
    
    public static function getInstance(): User {
        return Yii::$app->user->identity;
    }

    public static function login($tel, $password) {
        // метод find() возвращает Query-объект (объект построения запроса в бд)
        // метод where([{column} => {value}]) добавляет условие и возвращает Query-объект (объект построения запроса в бд)
        // метод one() возвращает экземпляр соответствующего класса, либо null, если не найдено ни одной записи
        // Может быть заменено на метод findOne([{column} => {value}]), который является alias для find()->where([{column} => {value}])->one()
        // Происходит поиск пользователя по его логину
        $user = static::find()->where(['tel' => $tel])->one();

        // Проверка на пользователя и на совпадение его пароля
        if ($user && $user->validatePassword($password)) {
            return $user;
        }

        // Иначе возвращать null
        return null;
    }

    /**
     * Скопировано из User.php.dist
     * В будущем будет изменено для сравнения пароля по хешу
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        // Поиск пользователя по id. Может быть заменено на alias static::findOne(['id' => $id]);
        return static::find()->where(['id' => $id])->one();
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // Работать с токенами не требуется, но методы обязательно надо реализовать, поэтому возвращаем null
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        // Работать с токенами не требуется, но методы обязательно надо реализовать, поэтому возвращаем null
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        // Работать с токенами не требуется, но методы обязательно надо реализовать, поэтому возвращаем null
        return null;
    }
    public function isAdmin() {
        return $this->role_id == Role::ADMIN_ID;
    }
}
