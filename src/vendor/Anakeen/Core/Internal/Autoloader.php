<?php

namespace Anakeen\Core\Internal;

class Autoloader
{

    /**
     * @var \Composer\Autoload\ClassLoader $loader
     */
    protected static $loader;

    public static function recordLoader($loader)
    {
        self::$loader = $loader;
    }

    public static function classExists($className)
    {
        if ($className[0] === "\\") {
            $className = substr($className, 1);
        }
        return (self::$loader->findFile($className)) ? true : false;
    }
}