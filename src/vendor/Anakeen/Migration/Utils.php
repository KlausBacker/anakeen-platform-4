<?php

namespace Anakeen\Migration;

use Anakeen\Router\Exception;

class Utils
{

    public static function writeFileContent($path, $content)
    {
        static::mkdirPath(dirname($path));

        if (!file_put_contents($path, $content)) {
            throw new Exception(sprintf("Cannot write file \%s\"", $path));
        }
    }

    public static function mkdirPath($path)
    {
        if ($path && !is_dir($path)) {
            static ::mkdirPath(dirname($path));
            if (!mkdir($path)) {
                throw new Exception(sprintf("Cannot mkdir \%s\"", $path));
            }
        }
    }
}
