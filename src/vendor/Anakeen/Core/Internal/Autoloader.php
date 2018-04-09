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
        $find = (self::findFile($className));
        if (!$find) {
            // @todo legacy mode - to be removed
            $classFile = sprintf("%s/../../../Root/Class.%s.php", __DIR__, $className);
            error_log($classFile);
            if (file_exists($classFile)) {
                return true;
            }
        } else {
            return true;
        }
        return false;
    }
}