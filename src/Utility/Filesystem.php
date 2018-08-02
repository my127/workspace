<?php

namespace my127\Workspace\Utility;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class Filesystem
{
    public static function upsearch(string $name, string $startFrom): ?string
    {
        $path   = null;
        $search = explode(DIRECTORY_SEPARATOR, $startFrom);

        while (!empty($search)) {
            if (file_exists($candidate = implode(DIRECTORY_SEPARATOR, $search).DIRECTORY_SEPARATOR.$name)) {
                $path = dirname($candidate);
                break;
            }
            array_pop($search);
        }

        return $path;
    }

    public static function rrmdir($src) {

        $dir = opendir($src);

        while(false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $full = $src . '/' . $file;
                if (is_dir($full) ) {
                    self::rrmdir($full);
                } else {
                    unlink($full);
                }
            }
        }

        closedir($dir);
        rmdir($src);
    }

    public static function rcopy($src, $dst)
    {
        $dir = opendir($src);

        if (!is_dir($dst)) {
            mkdir($dst);
        }

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ( $file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::rcopy($src . '/' . $file,$dst . '/' . $file);
                } else {
                    $srcFile = $src.'/'.$file;
                    $dstFile = $dst.'/'.$file;
                    copy($srcFile, $dstFile);
                }
            }
        }

        closedir($dir);
    }

    public static function rsearch($folder, $pattern)
    {
        $dir      = new RecursiveDirectoryIterator($folder);
        $ite      = new RecursiveIteratorIterator($dir);
        $files    = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
        $fileList = [];

        foreach($files as $file) {
            $fileList = array_merge($fileList, $file);
        }

        return $fileList;
    }
}
