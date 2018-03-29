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

    public static function findFile($className)
    {
        if ($className[0] === "\\") {
            $className = substr($className, 1);
        }
        $findPath = self::$loader->findFile($className);
        return $findPath;
    }

    public static function classExists($className)
    {
        return (self::findFile($className)) ? true : false;
    }
}