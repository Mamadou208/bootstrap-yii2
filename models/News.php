<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use Intervention\Image\ImageManagerStatic as Image;
use app\helpers\Util;
use app\models\query\NewsQuery;

/**
 * This is the model class for table "news".
 *
 * @property integer $id
 * @property integer $type_id
 * @property string $title
 * @property string $text
 * @property string $preview
 * @property string $date_create
 * @property string $date_update
 * @property string $date_pub
 * @property string $reference
 * @property integer $status
 */
class News extends \yii\db\ActiveRecord
{
    const STATUS_BLOCKED = 0;
    const STATUS_ACTIVE  = 1;

    /**
     * @var array
     */
    public $tagValues;
    /**
     * @var array
     */
    public $gallery;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['title', 'type_id', 'text', 'date_pub'], 'required'
            ],
            [
                [
                    'title', 'type_id', 'text', 'date_pub', 'preview', 'gallery',
                    'reference', 'status', 'tagValues'
                ], 'safe'
            ],

            ['type_id', 'integer'],
            ['type_id', 'exist', 'targetClass' => NewsType::className(), 'targetAttribute' => ['type_id' => 'id']],

            ['title', 'string', 'max' => 255],
            ['text', 'string'],

            ['date_pub', 'date', 'format' => 'php:Y-m-d H:i:s',
                'timestampAttribute' => 'date_pub',
                'timestampAttributeFormat' => 'php:Y-m-d H:i:s'
            ],

            ['reference', 'url'],
            ['reference', 'string', 'max' => 255],

            ['status', 'integer'],
            ['status', 'in', 'range' => array_keys(News::getStatuses())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type_id' => Yii::t('app', 'Type'),
            'title' => Yii::t('app', 'Title'),
            'text' => Yii::t('app', 'Text'),
            'preview' => Yii::t('app', 'Preview'),
            'gallery' => Yii::t('app', 'Gallery'),
            'date_create' => Yii::t('app', 'Date create'),
            'date_update' => Yii::t('app', 'Date update'),
            'date_pub' => Yii::t('app', 'Date publication'),
            'reference' => Yii::t('app', 'Reference'),
            'status' => Yii::t('app', 'Status'),

            'tagValues' => Yii::t('app', 'Tags'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [

        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'date_create',
                'updatedAtAttribute' => 'date_update',
                'value' => new \yii\db\Expression('NOW()'),
            ],

            [
                'class' => 'creocoder\taggable\TaggableBehavior',
                // 'tagValuesAsArray' => false,
                // 'tagRelation' => 'tags',
                 'tagValueAttribute' => 'title',
                 'tagFrequencyAttribute' => 'count',
            ],

            [
                'class' => 'rkit\filemanager\behaviors\FileBehavior',
                'attributes' => [
                    'text' => [
                        'storage' => 'rkit\filemanager\storages\LocalStorage',
                        'rules' => [
                            'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg'],
                            'extensions' => ['jpg', 'jpeg', 'png'],
                            'maxSize' => 1024 * 1024 * 1, // 1 MB
                            'tooBig' => Yii::t('app.validators', 'File size must not exceed') . ' 1Mb'
                        ]
                    ],
                    'preview' => [
                        'storage' => 'rkit\filemanager\storages\LocalStorage',
                        'saveFilePath' => true,
                        'rules' => [
                            'imageSize' => ['minWidth' => 300, 'minHeight' => 300],
                            'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg'],
                            'extensions' => ['jpg', 'jpeg', 'png'],
                            'maxSize' => 1024 * 1024 * 1, // 1 MB
                            'tooBig' => Yii::t('app.validators', 'File size must not exceed') . ' 1Mb'
                        ],
                        'preset' => [
                            '200x200' => function ($realPath, $publicPath, $thumbPath) {
                                Image::make($realPath . $publicPath)
                                    ->fit(200, 200)
                                    ->save($realPath . $thumbPath, 100);
                            },
                            '1000x1000' => function ($realPath, $publicPath, $thumbPath) {
                                Image::make($realPath . $publicPath)
                                    ->resize(1000, 1000, function ($constraint) {
                                        $constraint->aspectRatio();
                                        $constraint->upsize();
                                    })
                                    ->save(null, 100);
                            },
                        ],
                        'applyPresetAfterUpload' => '*'
                    ],
                    'gallery' => [
                        'storage' => 'rkit\filemanager\storages\LocalStorage',
                        'multiple' => true,
                        'rules' => [
                            'imageSize' => ['minWidth' => 300, 'minHeight' => 300],
                            'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg'],
                            'extensions' => ['jpg', 'jpeg', 'png'],
                            'maxSize' => 1024 * 1024 * 1, // 1 MB
                            'tooBig' => Yii::t('app.validators', 'File size must not exceed') . ' 1Mb'
                        ],
                        'preset' => [
                            '80x80' => function ($realPath, $publicPath, $thumbPath) {
                                Image::make($realPath . $publicPath)
                                    ->fit(80, 80)
                                    ->save($realPath . $thumbPath, 100);
                            },
                        ],
                    ]
                ]
            ]
        ];
    }

    public function transactions()
    {
        return [
            'create' => self::OP_ALL,
            'update' => self::OP_ALL,
            'delete' => self::OP_ALL,
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->setTagValues($this->tagValues);

            return true;
        }

        return false; // @codeCoverageIgnore
    }

    /**
     * @inheritdoc
     * @return NewsQuery
     */
    public static function find()
    {
        return new NewsQuery(get_called_class());
    }

    /**
     * Get all statuses
     *
     * @return string[]
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_BLOCKED => Yii::t('app', 'Not published'),
            self::STATUS_ACTIVE  => Yii::t('app', 'Published'),
        ];
    }

    /**
     * Get statuse name
     *
     * @return string
     */
    public function getStatusName()
    {
        $statuses = self::getStatuses();
        return isset($statuses[$this->status]) ? $statuses[$this->status] : '';
    }

    /**
     * Is it blocked?
     *
     * @param bool
     */
    public function isBlocked()
    {
        return $this->status == self::STATUS_BLOCKED;
    }

    /**
     * Is it active?
     *
     * @param bool
     */
    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(NewsType::className(), array('id' => 'type_id'));
    }

    /**
     * Get tags.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->owner
            ->hasMany(Tag::className(), ['id' => 'tag_id'])
            ->viaTable('{{%news_tag_assn}}', ['news_id' => 'id']);
    }
}
