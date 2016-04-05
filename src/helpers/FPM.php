<?php
/**
 * Author: metal
 * Email: metal
 */

namespace metalguardian\fileProcessor\helpers;

/**
 * Class FPM
 *
 * @package metalguardian\fileProcessor\helpers
 */
class FPM
{
    const ACTION_ADAPTIVE_THUMBNAIL = 'adaptiveThumbnail';
    const ACTION_THUMBNAIL = 'thumbnail';
    const ACTION_CROP = 'crop';
    const ACTION_CANVAS_THUMBNAIL = 'canvasThumbnail';
    const ACTION_FRAME = 'frame';
    const ACTION_COPY = 'copy';

    /**
     * @var \metalguardian\fileProcessor\components\ThumbnailCache
     */
    protected static $cache = null;

    /**
     * @var \metalguardian\fileProcessor\components\FileTransfer
     */
    protected static $transfer = null;

    public static $moduleName = 'fileProcessor';

    /**
     * @param string $module
     *
     * @throws \yii\base\InvalidConfigException
     * @return \metalguardian\fileProcessor\Module
     */
    public static function m($module = null)
    {
        if (is_null($module)) {
            $module = static::$moduleName;
        }
        if (!\Yii::$app->hasModule($module)) {
            throw new \yii\base\InvalidConfigException(
                \metalguardian\fileProcessor\Module::t(
                    'exception',
                    'Wrong module name! You need call this method with right fileProcessor module name.'
                )
            );
        }
        return \Yii::$app->getModule($module);
    }

    /**
     * @return \metalguardian\fileProcessor\components\ThumbnailCache
     * @throws \yii\base\InvalidConfigException
     */
    public static function cache()
    {
        if (is_null(static::$cache)) {
            static::$cache = \Yii::createObject(\metalguardian\fileProcessor\components\ThumbnailCache::className());
        }

        return static::$cache;
    }

    /**
     * @return \metalguardian\fileProcessor\components\FileTransfer
     * @throws \yii\base\InvalidConfigException
     */
    public static function transfer()
    {
        if (is_null(static::$transfer)) {
            static::$transfer = \Yii::createObject(\metalguardian\fileProcessor\components\FileTransfer::className());
        }

        return static::$transfer;
    }

    /**
     * @param $id
     *
     * @return null
     */
    public static function deleteFile($id)
    {
        if (!(int)$id) {
            return null;
        }

        static::cache()->delete($id);
        static::transfer()->deleteFile($id);

        return true;
    }

    /**
     * Get host for the image with $id
     *
     * @param null $id
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getHost($id = null)
    {
        if (is_string(static::m()->host)) {
            $host = \Yii::getAlias(static::m()->host);
        } else {
            $count = count(static::m()->host);
            $host = \Yii::getAlias(static::m()->host[$id % $count]);
        }
        return $host;
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getTableName()
    {
        return static::m()->tableName;
    }

    /**
     * @return bool|string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getBaseUploadDirectory()
    {
        return \Yii::getAlias(static::m()->baseUploadDirectory);
    }

    /**
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    public static function getFilesPerDirectory()
    {
        return static::m()->filesPerDir;
    }

    /**
     * @param $id
     *
     * @return string
     */
    public static function getOriginalDirectory($id)
    {
        return
            static::getBaseUploadDirectory()
            . static::m()->originalDirectory
            . DIRECTORY_SEPARATOR
            . static::getOriginalDirectoryName($id);
    }

    /**
     * @param $id
     * @param $module
     * @param $size
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getThumbnailDirectory($id, $module, $size)
    {
        return
            static::getBaseUploadDirectory()
            . static::m()->thumbnailDirectory
            . DIRECTORY_SEPARATOR
            . static::getThumbnailDirectoryName($id, $module, $size);
    }

    /**
     * @param $id
     * @param $module
     * @param $size
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getThumbnailDirectoryUrl($id, $module, $size)
    {
        return
            static::getHost($id)
            . static::m()->thumbnailDirectory
            . '/'
            . static::getThumbnailDirectoryName($id, $module, $size)
            . '/';
    }

    /**
     * @param $id
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getOriginalDirectoryUrl($id)
    {
        return
            static::getHost($id)
            . static::m()->originalDirectory
            . '/'
            . static::getOriginalDirectoryName($id)
            . '/';
    }

    /**
     * @param $id
     * @param $baseName
     * @param $extension
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getOriginalFileName($id, $baseName, $extension)
    {
        $fileNameTemplate = static::m()->originalFileNameTemplate;
        $replaces = [
            '{id}' => $id,
            '{baseName}' => $baseName,
            '{extension}' => $extension,
        ];
        return strtr($fileNameTemplate, $replaces);
    }

    /**
     * @param $id
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getOriginalDirectoryName($id)
    {
        $directoryNameTemplate = static::m()->originalDirectoryNameTemplate;
        $replaces = [
            '{id}' => floor($id / static::getFilesPerDirectory()),
        ];
        return strtr($directoryNameTemplate, $replaces);
    }

    /**
     * @param $id
     * @param $baseName
     * @param $extension
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getThumbnailFileName($id, $baseName, $extension)
    {
        $fileNameTemplate = static::m()->thumbnailFileNameTemplate;
        $replaces = [
            '{id}' => $id,
            '{baseName}' => $baseName,
            '{extension}' => $extension,
        ];
        return strtr($fileNameTemplate, $replaces);
    }

    /**
     * @param $id
     * @param $module
     * @param $size
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getThumbnailDirectoryName($id, $module, $size)
    {
        $directoryNameTemplate = static::m()->thumbnailDirectoryNameTemplate;
        $replaces = [
            '{id}' => floor($id / static::getFilesPerDirectory()),
            '{module}' => $module,
            '{size}' => $size,
        ];
        return strtr($directoryNameTemplate, $replaces);
    }

    /**
     * @param $id
     * @param $moduleId
     * @param $size
     * @param array $options
     *
     * @return null|string
     */
    public static function image($id, $moduleId, $size, $options = [])
    {
        if (!(int)$id) {
            return null;
        }

        return \yii\helpers\Html::img(static::src($id, $moduleId, $size), $options);
    }

    /**
     * @param $id
     * @param $module
     * @param $size
     *
     * @return null|string
     * @throws \Exception
     */
    public static function src($id, $module, $size)
    {
        if (!(int)$id) {
            return null;
        }

        $model = FPM::transfer()->getData($id);
        $src = static::getThumbnailDirectoryUrl($id, $module, $size)
            . rawurlencode(static::getThumbnailFileName($id, $model->base_name, $model->extension));

        return $src;
    }

    /**
     * @param $id
     *
     * @return null|string
     * @throws \Exception
     */
    public static function originalSrc($id)
    {
        if (!(int)$id) {
            return null;
        }

        $model = FPM::transfer()->getData($id);
        $src = static::getOriginalDirectoryUrl($id)
            . rawurlencode(static::getThumbnailFileName($id, $model->base_name, $model->extension));

        return $src;
    }
}
