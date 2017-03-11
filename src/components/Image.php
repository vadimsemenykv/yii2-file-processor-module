<?php
/**
 * Author: metal
 * Email: metal
 */

namespace metalguardian\fileProcessor\components;

use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

/**
 * Class Image
 * @package metalguardian\fileProcessor\components
 */

class Image
{
    /**
     * GD2 driver definition for Imagine implementation using the GD library.
     */
    const DRIVER_GD2 = 'gd2';
    /**
     * imagick driver definition.
     */
    const DRIVER_IMAGICK = 'imagick';
    /**
     * gmagick driver definition.
     */
    const DRIVER_GMAGICK = 'gmagick';

    /**
     * @var array|string the driver to use. This can be either a single driver name or an array of driver names.
     * If the latter, the first available driver will be used.
     */
    public static $driver = [self::DRIVER_GMAGICK, self::DRIVER_IMAGICK, self::DRIVER_GD2];

    /**
     * @var ImagineInterface instance.
     */
    private static $_imagine;


    /**
     * Returns the `Imagine` object that supports various image manipulations.
     * @return ImagineInterface the `Imagine` object
     */
    public static function getImagine()
    {
        if (self::$_imagine === null) {
            self::$_imagine = static::createImagine();
        }

        return self::$_imagine;
    }

    /**
     * @param ImagineInterface $imagine the `Imagine` object.
     */
    public static function setImagine($imagine)
    {
        self::$_imagine = $imagine;
    }

    /**
     * Creates an `Imagine` object based on the specified [[driver]].
     * @return ImagineInterface the new `Imagine` object
     * @throws InvalidConfigException if [[driver]] is unknown or the system doesn't support any [[driver]].
     */
    protected static function createImagine()
    {
        foreach ((array) static::$driver as $driver) {
            switch ($driver) {
                case self::DRIVER_GMAGICK:
                    if (class_exists('Gmagick', false)) {
                        return new \Imagine\Gmagick\Imagine();
                    }
                    break;
                case self::DRIVER_IMAGICK:
                    if (class_exists('Imagick', false)) {
                        return new \Imagine\Imagick\Imagine();
                    }
                    break;
                case self::DRIVER_GD2:
                    if (function_exists('gd_info')) {
                        return new \Imagine\Gd\Imagine();
                    }
                    break;
                default:
                    throw new InvalidConfigException("Unknown driver: $driver");
            }
        }
        throw new InvalidConfigException("Your system does not support any of these drivers: " . implode(',', (array) static::$driver));
    }

    /**
     * Crops an image.
     *
     * For example,
     *
     * ~~~
     * $obj->crop('path\to\image.jpg', 200, 200, 5, 5);
     * ~~~
     *
     * @param string $filename the image file path
     * @param integer $width the crop width
     * @param integer $height the crop height
     * @param int $startX
     * @param int $startY
     * @return ImageInterface
     */
    public static function crop($filename, $width, $height, $startX = 0, $startY = 0)
    {
        return static::getImagine()
            ->open($filename)
            ->copy()
            ->crop(new Point($startX, $startY), new Box($width, $height));
    }
    /**
     * Creates a thumbnail image. The function differs from `\Imagine\Image\ImageInterface::thumbnail()` function that
     * it keeps the aspect ratio of the image.
     * @param string $filename the image file path or path alias.
     * @param integer $width the width in pixels to create the thumbnail
     * @param integer $height the height in pixels to create the thumbnail
     * @param string $mode
     * @return ImageInterface
     */
    public static function thumbnail($filename, $width, $height, $mode = ManipulatorInterface::THUMBNAIL_OUTBOUND, $filter = ImageInterface::FILTER_UNDEFINED)
    {
        $image = static::getImagine()->open($filename);

        $ratio = $image->getSize()->getWidth() / $image->getSize()->getHeight();
        list($width, $height) = static::countNullableSide($ratio, $width, $height);

        $box = new Box($width, $height);

        return $image->thumbnail($box, $mode, $filter);
    }

    /**
     * Adds a frame around of the image. Please note that the image size will increase by `$margin` x 2.
     * @param string $filename the full path to the image file
     * @param integer $margin the frame size to add around the image
     * @param string $color the frame color
     * @param integer $alpha the alpha value of the frame.
     * @return ImageInterface
     */
    public static function frame($filename, $margin, $color, $alpha = 100)
    {
        $img = static::getImagine()->open($filename);
        $size = $img->getSize();
        $pasteTo = new Point($margin, $margin);
        $padColor = (new RGB())->color($color, $alpha);
        $box = new Box($size->getWidth() + ceil($margin * 2), $size->getHeight() + ceil($margin * 2));
        $image = static::getImagine()->create($box, $padColor);
        $image->paste($img, $pasteTo);
        return $image;
    }

    /**
     * Creates a thumbnail image. The function differs from `\Imagine\Image\ImageInterface::thumbnail()` function that
     * it keeps the aspect ratio of the image.
     * @param string $filename the image file path or path alias.
     * @param integer $width the width in pixels to create the thumbnail
     * @param integer $height the height in pixels to create the thumbnail
     * @param integer $alpha
     * @return ImageInterface
     */
    public static function canvasThumbnail($filename, $width, $height, $alpha = null)
    {
        $box = new Box($width, $height);

        $img = static::getImagine()->open($filename);
        /** @var ImageInterface $img */
        $img = $img->thumbnail($box);

        $thumb = static::getImagine()->create($box, (new RGB())->color('FFF', $alpha));

        // calculate points
        $size = $img->getSize();
        $startX = 0;
        $startY = 0;
        if ($size->getWidth() < $width) {
            $startX = ceil($width - $size->getWidth()) / 2;
        }
        if ($size->getHeight() < $height) {
            $startY = ceil($height - $size->getHeight()) / 2;
        }
        $thumb->paste($img, new Point($startX, $startY));
        return $thumb;
    }

    /**
     * @param null|int $width
     * @param null|int $height
     * @return array
     */
    protected static function countNullableSide($ratio, $width = null, $height = null)
    {
        if ($width !== null && $height === null) {
            $height = ceil($width / $ratio);
        } elseif ($width === null && $height !== null) {
            $width = ceil($height * $ratio);
        } elseif ($width === null && $height === null) {
            throw new InvalidParamException("Width and height cannot be null at same time.");
        }
        return [$width, $height];
    }


    /**
     * @param string $filename
     * @param string $watermarkFilename
     * @param array $config
     * @return ImageInterface
     */
    public static function addWatermarkWithSafeConfig($filename, $config = [])
    {
        $wpoint = null;
        $wbox = null;
        $watermarkFilename = null;
        if (is_array($config) && isset($config['fileName'])) {
            $watermarkFilename = $config['fileName'];
        }
        if (is_array($config)) {
            if (isset($config['point']['x']) && isset($config['point']['y'])) {
                $wpoint = new Point($config['point']['x'], $config['point']['y']);
            }
            if (isset($config['size']['width']) && isset($config['size']['height'])) {
                $wbox = new Box($config['size']['width'], $config['size']['height']);
            }
        }

        return static::addWatermark($filename, $watermarkFilename, $wpoint, $wbox);
    }

    /**
     * @param string $filename
     * @param string $watermarkFilename
     * @param null|Point $wpoint
     * @param null|Box $wbox
     * @return ImageInterface
     */
    public static function addWatermark($filename, $watermarkFilename, $wpoint = null, $wbox = null)
    {
        $image = static::getImagine()->open($filename);
        $wmarkImage = static::getImagine()->open($watermarkFilename);
        $size = $image->getSize();
        $wSize = $wmarkImage->getSize();

        // if point is not set - it will be top left cornet
        if (is_null($wpoint)) {
            $wpoint = new Point(0, 0);
        }

        // if size for watermark thumbnail is set - do this
        if (!is_null($wbox)) {
            $wmarkImage = $wmarkImage->thumbnail($wbox, ManipulatorInterface::THUMBNAIL_INSET);
        }

        // resize watermark if it larger then image in any dimension to fully fit image
        if (!static::fitToImage($size, $wSize, $wpoint)) {
            $imageBox = new Box($size->getWidth(), $size->getHeight());
            $wmarkImage = $wmarkImage->thumbnail($imageBox, ManipulatorInterface::THUMBNAIL_INSET);
        }

        return $image->paste($wmarkImage, $wpoint);
    }

    /**
     * @param BoxInterface $originalSize
     * @param BoxInterface $insertedSize
     * @param Point $point
     * @return bool
     */
    protected static function fitToImage(BoxInterface $originalSize, BoxInterface $insertedSize, Point $point)
    {
        if ($insertedSize->getWidth() > $originalSize->getWidth() || $insertedSize->getHeight() > $originalSize->getHeight()) {
            return false;
        }

        $withOffset = $insertedSize->getWidth() + $point->getX();
        $heightOffset = $insertedSize->getHeight() + $point->getY();

        if ($withOffset > $originalSize->getHeight() || $heightOffset > $originalSize->getHeight()) {
            return false;
        }

        return true;
    }
}
