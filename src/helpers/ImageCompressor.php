<?php
/**
 * @author walter
 */

namespace metalguardian\fileProcessor\helpers;


class ImageCompressor
{
    const FILE_EXTENSION_PNG = 'png';

    public static $suffix = '_compressed';

    public static function compressPngThumbs($thumbFolder)
    {
        if(self::isPngQuantInstalled()) {
            $iterator = self::getRecursiveIterator($thumbFolder);
            foreach ($iterator as $path => $dir) {
                if (!$dir->isDir()) {
                    $file = new CompressingFile($path);
                    if(self::isPngExtension($file->getExtension()) && !$file->isCompressed()) {
                        self::compressPng($path, $file->getCompressedFilePath());
                    }
                }
            }
        }
    }

    public static function isPngQuantInstalled()
    {
        if(!shell_exec("pngquant --version")) {
            return false;
        }
        return true;
    }

    public static function getRecursiveIterator($thumbFolder)
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($thumbFolder, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD
        );
    }

    public static function isPngExtension($extension)
    {
        if($extension == self::FILE_EXTENSION_PNG) {
            return true;
        }
        return false;
    }

    public static function compressPng($toCompressionFileName, $compressedFileName)
    {
        if($compressedPng = self::getCompressPngFile($toCompressionFileName)) {
            file_put_contents($compressedFileName, $compressedPng);
        }
    }

    public static function getCompressPngFile($filePath, $maxQuality = 90)
    {
        // guarantee that quality won't be worse than that.
        $minQuality = 60;
        // '-' makes it use stdout, required to save to $compressedPngContent variable
        // '<' makes it read from the given file path
        // escapeshellarg() makes this safe to use with any path
        $compressedPngContent = shell_exec("pngquant --quality=$minQuality-$maxQuality - < " . escapeshellarg($filePath));
        if (!$compressedPngContent) {
            return false;
        }
        return $compressedPngContent;
    }
} 