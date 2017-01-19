<?php
/**
 * Author: metal
 * Email: metal
 */

namespace metalguardian\fileProcessor\components;

use yii\base\Component;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;
use metalguardian\fileProcessor\Module;
use metalguardian\fileProcessor\helpers\FPM;
use metalguardian\fileProcessor\models\File;

/**
 * Class FileTransfer
 *
 * @package metalguardian\fileProcessor\components
 */
class FileTransfer extends Component
{
    /**
     * @param UploadedFile $file
     *
     * @return int|boolean
     */
    public function saveUploadedFile(UploadedFile $file)
    {
        $id = $this->saveData($file);
        $directory = FPM::getOriginalDirectory($id);

        FileHelper::createDirectory($directory, 0777, true);

        $fileName =
            $directory
            . DIRECTORY_SEPARATOR
            . FPM::getOriginalFileName(
                $id,
                $file->getBaseName(),
                $file->getExtension()
            );

        if ($file->saveAs($fileName)) {
            return $id;
        }

        $this->deleteData($id);

        return false;
    }

    /**
     * @param UploadedFile $file
     *
     * @return int
     */
    public function saveData(UploadedFile $file)
    {
        $ext = $file->getExtension();
        $baseName = $file->getBaseName();

        $model = new File();
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
     * @throws Exception
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

            $directory = FPM::getOriginalDirectory($id);

            FileHelper::createDirectory($directory, 0777, true);

            $newFileName =
                $directory
                . DIRECTORY_SEPARATOR
                . FPM::getOriginalFileName(
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
            throw new Exception(Module::t('exception', 'File path not correct'));
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
        $model = new File();
        $model->extension = $extension;
        $model->base_name = $baseName;
        $model->save(false);

        return $model->id;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function deleteFile($id)
    {
        if (!(int)$id) {
            return false;
        }

        $directory = FPM::getOriginalDirectory($id);

        $model = $this->getData($id);
        $fileName =
            $directory
            . DIRECTORY_SEPARATOR
            . FPM::getOriginalFileName(
                $id,
                $model->base_name,
                $model->extension
            );

        $result = $this->deleteData($id);

        if ($result && is_file($fileName)) {
            unlink($fileName);
        }

        return $result;
    }

    /**
     * Get file meta data
     *
     * @param integer $id file id
     *
     * @return File|null
     * @throws Exception
     */
    public function getData($id)
    {
        $model = File::findOne($id);
        if (!$model) {
            throw new Exception(Module::t('exception', 'Missing meta data for file'));
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
