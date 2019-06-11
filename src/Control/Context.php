<?php


namespace Control;


require_once(__DIR__ . '/../../include/class/Class.WIFF.php');

class Context
{
    public static $contextName;
    private static $context;

    public static function getContext()
    {

        if (!self::$context) {

            $wiff = \WIFF::getInstance();

            $contextList = $wiff->getContextList();
            if ($contextList === false) {
                throw new \Exception(sprintf("Error getting contexts list: %s\n", $wiff->errorMessage));

            }
            self::$contextName = $contextList[0]->name;
            self::$context = $wiff->getContext(self::$contextName);
            if (!self::$context) {
                throw new \Exception(sprintf("Context \"%s\" not exists", self::$contextName));
            }
        }
        return self::$context;
    }

    public static function getParameters()
    {

        $wiff = \WIFF::getInstance();
        return $wiff->getParamList();
    }

    public static function getRepositories()
    {

        $wiff = \WIFF::getInstance();
        return $wiff->getRepoList();
    }

    public static function getVersion()
    {
        $wiff = \WIFF::getInstance();
        return $wiff->getVersion();
    }

    public static function getAvailableVersion()
    {
        $wiff = \WIFF::getInstance();
        return $wiff->getAvailVersion();
    }

    public static function getPhpInfo()
    {
        ob_start();
        phpinfo();
        $phpinfo = trim(ob_get_clean());
        if (preg_match('/<table>(.*)<\/table>/muis', $phpinfo, $matches)) {
            return $matches[0];
        }
        return "";
    }
}