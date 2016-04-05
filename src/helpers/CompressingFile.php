<?php
/**
 * @author walter
 */

namespace metalguardian\fileProcessor\helpers;


class CompressingFile
{
    private $_dirName;
    private $_name;
    private $_extension;

    public function __construct($filePath)
    {
        if(file_exists($filePath)) {
            $pathInfo = pathinfo($filePath);
            $this->setProperties($pathInfo);
        }
    }

    private function setProperties($pathInfo)
    {
        if(isset($pathInfo['dirname']))
        {
            $this->setDirName($pathInfo['dirname']);
        }
        if(isset($pathInfo['filename']))
        {
            $this->setName($pathInfo['filename']);
        }
        if(isset($pathInfo['extension']))
        {
            $this->setExtension($pathInfo['extension']);
        }
    }

    private function setDirName($dirName)
    {
        $this->_dirName = $dirName;
    }

    public function getDirName()
    {
        return $this->_dirName;
    }

    private function setName($name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    private function setExtension($extension)
    {
        $this->_extension = $extension;
    }

    public function getExtension()
    {
        return $this->_extension;
    }

    public function isCompressed()
    {
        $filePath = $this->getCompressedFilePath();
        if(file_exists($filePath)) {
            return true;
        }
        return false;
    }

    public function getCompressedFilePath()
    {
        return $this->_dirName . '/'
            . $this->_name . ImageCompressor::$suffix
            . '.' . $this->_extension;
    }

} 