<?php

namespace metalguardian\fileProcessor\models;

use metalguardian\fileProcessor\Module;

/**
 * This is the model class for table "{{%fpm_file}}".
 *
 * @property integer $id
 * @property string $extension
 * @property string $base_name
 * @property string $alt_tag
 * @property integer $created_at
 */
class File extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return \metalguardian\fileProcessor\helpers\FPM::getTableName();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ]
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['extension'], 'required'],
            [['extension'], 'string', 'max' => 10],
            [['base_name', 'alt_tag'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Module::t('model', 'ID'),
            'extension' => Module::t('model', 'Extension'),
            'base_name' => Module::t('model', 'Base name'),
            'created_at' => Module::t('model', 'Created At'),
            'alt_tag' => Module::t('model', 'Alt tag')
        ];
    }
}
