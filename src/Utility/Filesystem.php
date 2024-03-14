<?php

namespace my127\Workspace\Utility;

use function mkdir;

class Filesystem
{
    public const TMPNAM_DEFAULT_PREFIX = 'my127ws';

    public static function upsearch(string $name, string $startFrom): ?string
    {
        $path = null;
        $search = explode(DIRECTORY_SEPARATOR, $startFrom);

        while (!empty($search)) {
            if (file_exists($candidate = implode(DIRECTORY_SEPARATOR, $search) . DIRECTORY_SEPARATOR . $name)) {
                $path = dirname($candidate);
                break;
            }
            array_pop($search);
        }

        return $path;
    }

    public static function rrmdir($src)
    {
        $dir = opendir($src);

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $full = $src . '/' . $file;
                if (is_dir($full)) {
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
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::rcopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    $srcFile = $src . '/' . $file;
                    $dstFile = $dst . '/' . $file;
                    copy($srcFile, $dstFile);
                }
            }
        }

        closedir($dir);
    }

    public static function rsearch($folder, $pattern)
    {
        $dir = new \RecursiveDirectoryIterator($folder);
        $ite = new \RecursiveIteratorIterator($dir);
        $files = new \RegexIterator($ite, $pattern, \RegexIterator::GET_MATCH);
        $fileList = [];

        foreach ($files as $file) {
            $fileList = array_merge($fileList, $file);
        }

        return $fileList;
    }

    public static function tempname(TmpNamType $type = TmpNamType::PATH, string $prefix = self::TMPNAM_DEFAULT_PREFIX): string
    {
        if (false === ($tmpFilePath = tempnam(sys_get_temp_dir(), $prefix))) {
            throw new \Exception('Could not create temporary ' . $type->value);
        }

        if ($type === TmpNamType::FILE) {
            return $tmpFilePath;
        }


        if (false === unlink($tmpFilePath)) {
            throw new \Exception('Could not remove temporary filepath ' . $tmpFilePath);
        }

        if ($type === TmpNamType::PATH) {
            return $tmpFilePath;
        }

        mkdir($tmpFilePath, 0777, true);
        if (false === mkdir($tmpFilePath, 0777, true)) {
            throw new \Exception('Could not create temporary directory ' . $tmpFilePath);
        }

        return $tmpFilePath;
    }
}
