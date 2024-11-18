<?php

namespace metalguardian\fileProcessor\controllers;

use Imagine\Image\ManipulatorInterface;
use metalguardian\fileProcessor\components\Image;
use metalguardian\fileProcessor\helpers\FPM;
use metalguardian\fileProcessor\Module;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;

/**
 * Class ImageController
 *
 * @package metalguardian\fileProcessor\controllers
 */
class FileController extends \yii\web\Controller
{
    /**
     * @param $sub
     * @param $module
     * @param $size
     * @param $id
     * @param $baseName
     * @param $extension
     *
     * @return int
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionProcess($sub, $module, $size, $id, $baseName, $extension)
    {
        $directory = (string)floor($id / FPM::getFilesPerDirectory());
        if ($sub !== $directory) {
            throw new NotFoundHttpException(Module::t('exception', 'Wrong generated link'));
        }

        $fileName = FPM::getOriginalDirectory($id) . DIRECTORY_SEPARATOR . FPM::getOriginalFileName($id, $baseName, $extension);

        if (file_exists($fileName)) {
            $data = FPM::transfer()->getData($id);

            if (strtolower($baseName) !== strtolower($data->base_name)) {
                throw new NotFoundHttpException(Module::t('exception', 'File not found'));
            }

            $config = isset(FPM::m()->imageSections[$module][$size]) ? FPM::m()->imageSections[$module][$size] : null;
            if (!is_array($config)) {
                throw new NotFoundHttpException(Module::t('exception', 'Incorrect request'));
            }

            $thumbnailFile = FPM::getThumbnailDirectory($id, $module, $size) . DIRECTORY_SEPARATOR . FPM::getThumbnailFileName($id, $baseName, $extension);
            FileHelper::createDirectory(FPM::getThumbnailDirectory($id, $module, $size));

            if (isset($config['action'])) {
                $options = $this->_getOptionsFromConfig($config);
                switch($config['action'])
                {
                    case FPM::ACTION_ADAPTIVE_THUMBNAIL:
                        $img = Image::thumbnail($fileName, $config['width'], $config['height'])
                            ->save($thumbnailFile, $options);
                        if (isset($config['watermark'])) {
                            $img = Image::addWatermarkWithSafeConfig($thumbnailFile, $config['watermark'])
                                ->save($thumbnailFile, $options);
                        }
                        $img->show($extension);
                        break;
                    case FPM::ACTION_THUMBNAIL:
                        $img = Image::thumbnail($fileName, $config['width'], $config['height'], ManipulatorInterface::THUMBNAIL_INSET)
                            ->save($thumbnailFile, $options);
                        if (isset($config['watermark'])) {
                            $img = Image::addWatermarkWithSafeConfig($thumbnailFile, $config['watermark'])
                                ->save($thumbnailFile, $options);
                        }
                        $img->show($extension);
                        break;
                    case FPM::ACTION_CROP:
                        Image::crop($fileName, $config['width'], $config['height'], $config['startX'], $config['startY'])
                            ->save($thumbnailFile, $options)
                            ->show($extension);
                        break;
                    case FPM::ACTION_CANVAS_THUMBNAIL:
                        $img = Image::canvasThumbnail($fileName, $config['width'], $config['height'])
                            ->save($thumbnailFile, $options);
                        if (isset($config['watermark'])) {
                            $img = Image::addWatermarkWithSafeConfig($thumbnailFile, $config['watermark'])
                                ->save($thumbnailFile, $options);
                        }
                        $img->show($extension);
                        break;
                    case FPM::ACTION_FRAME:
                        Image::frame($fileName, 50, 'F00')
                            ->save($thumbnailFile, $options)
                            ->show($extension);
                        break;
                    case FPM::ACTION_COPY:
                        if (FPM::m()->symLink) {
                            symlink($fileName, $thumbnailFile);
                        } else {
                            copy($fileName, $thumbnailFile);
                        }
                        \Yii::$app->response->sendFile($thumbnailFile);
                        break;
                    case FPM::ACTION_CHANGE_QUALITY:
                        Image::getImagine()->open($fileName)
                            ->save($thumbnailFile, $options)
                            ->show($extension);
                        break;
                    default:
                        throw new InvalidConfigException(Module::t('exception', 'Action is incorrect'));
                        break;
                }
            } else {
                throw new InvalidConfigException(Module::t('exception', 'Action not defined'));
            }
        } else {
            throw new NotFoundHttpException(Module::t('exception', 'File not found'));
        }
    }

    private function _getOptionsFromConfig(array $config)
    {
        $options = [];

        if (isset($config['quality']) && is_int($config['quality']) && $config['quality'] < 100 && $config['quality'] > 0 ) {
            $options['quality'] = $config['quality'];
        }

        return $options;
    }
}