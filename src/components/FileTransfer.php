<?php
/**
 * Author: metal
 * Email: metal
 */

namespace metalguardian\fileProcessor\components;

use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class FileTransfer
 *
 * @package metalguardian\fileProcessor\components
 */
class FileTransfer extends \yii\base\Component
{
    /**
     * @param \yii\web\UploadedFile $file
     *
     * @return int
     */
    public function saveUploadedFile(\yii\web\UploadedFile $file)
    {
        $id = $this->saveData($file);

        $directory = \metalguardian\fileProcessor\helpers\FPM::getOriginalDirectory($id);

        \yii\helpers\FileHelper::createDirectory($directory, 0777, true);

        $fileName =
            $directory
            . DIRECTORY_SEPARATOR
            . \metalguardian\fileProcessor\helpers\FPM::getOriginalFileName(
                $id,
                $file->getBaseName(),
                $file->getExtension()
            );

        $file->saveAs($fileName);

        return $id;
    }

    /**
     * @param \yii\web\UploadedFile $file
     *
     * @return int
     */
    public function saveData(\yii\web\UploadedFile $file)
    {
        $ext = $file->getExtension();
        $baseName = $file->getBaseName();

        $model = new \metalguardian\fileProcessor\models\File();
        $model->extension = $ext;
        $model->base_name = $baseName;
        $model->save(false);

        return $model->id;
    }

    /**
     * @param $filePath
     * @param bool $deleteOriginal
     *
     * @return int
     * @throws \Exception
     * @throws \yii\base\Exception
     */
    public function saveSystemFile($filePath, $deleteOriginal = false)
    {
        if (is_file($filePath)) {
            //list($dirname, $basename, $extension, $filename) = pathinfo($filePath);
            $file = pathinfo($filePath);

            $dirname = ArrayHelper::getValue($file, 'dirname');
            $basename = ArrayHelper::getValue($file, 'basename');
            $extension = ArrayHelper::getValue($file, 'extension');
            $filename = ArrayHelper::getValue($file, 'filename');

            $id = $this->saveSystemData($filename, $extension);

            $directory = \metalguardian\fileProcessor\helpers\FPM::getOriginalDirectory($id);

            \yii\helpers\FileHelper::createDirectory($directory, 0777, true);

            $newFileName =
                $directory
                . DIRECTORY_SEPARATOR
                . \metalguardian\fileProcessor\helpers\FPM::getOriginalFileName(
                    $id,
                    $filename,
                    $extension
                );

            if ($deleteOriginal) {
                rename($filePath, $newFileName);
            } else {
                copy($filePath, $newFileName);
            }

            return $id;
        } else {
            throw new \Exception(\metalguardian\fileProcessor\Module::t('exception', 'File path not correct'));
        }
    }

    /**
     * @param $baseName
     * @param $extension
     *
     * @return int
     */
    public function saveSystemData($baseName, $extension)
    {
        $model = new \metalguardian\fileProcessor\models\File();
        $model->extension = $extension;
        $model->base_name = $baseName;
        $model->save(false);

        return $model->id;
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteFile($id)
    {
        if (!(int)$id) {
            return false;
        }

        $directory = \metalguardian\fileProcessor\helpers\FPM::getOriginalDirectory($id);

        $model = $this->getData($id);
        $fileName =
            $directory
            . DIRECTORY_SEPARATOR
            . \metalguardian\fileProcessor\helpers\FPM::getOriginalFileName(
                $id,
                $model->base_name,
                $model->extension
            );

        if (is_file($fileName)) {
            $result = unlink($fileName) && $this->deleteData($id) ? true : false;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Get file meta data
     *
     * @param integer $id file id
     *
     * @return \metalguardian\fileProcessor\models\File|null
     * @throws \Exception
     */
    public function getData($id)
    {
        $model = \metalguardian\fileProcessor\models\File::findOne($id);
        if (!$model) {
            throw new \Exception(\metalguardian\fileProcessor\Module::t('exception', 'Missing meta data for file'));
        }
        return $model;
    }

    /**
     * Delete file meta data
     *
     * @param integer $id file id
     *
     * @return boolean
     */
    public function deleteData($id)
    {
        $model = $this->getData($id);
        if ($model) {
            return (boolean)$model->delete();
        }
        return false;
    }
}
