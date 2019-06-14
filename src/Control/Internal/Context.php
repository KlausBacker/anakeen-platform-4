<?php

namespace Control\Internal;

use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\RuntimeException;

require_once(__DIR__ . '/../../../include/class/Class.WIFF.php');

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
        $allRepos=  $wiff->getRepoList();
        $ctxRepos= self::getContext()->repo;
        foreach ($allRepos as &$repo) {
            $searchName=$repo->name;

            $ctxRepo=array_filter($ctxRepos, function ($lrepo) use ($searchName) {
                return $lrepo->name === $searchName;
            });
            if ($ctxRepo) {
                /** @noinspection PhpUndefinedFieldInspection */
                $repo->status = "activated";
                if ($ctxRepo[0]->errorMessage) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $repo->status = $ctxRepo[0]->errorMessage;
                }
            } else {
                /** @noinspection PhpUndefinedFieldInspection */
                $repo->status = "disabled";
            };
        }
        return $allRepos;
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


    public static function download($url)
    {
        $wiff = \WIFF::getInstance();
        $tmpfile=  $wiff->downloadUrl($url);

        if ($tmpfile !== false) {
            return file_get_contents($tmpfile);
        }

        throw new \Exception(sprintf("Page \"%s\" not found", $url));
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