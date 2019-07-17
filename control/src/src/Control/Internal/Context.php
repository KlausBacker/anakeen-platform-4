<?php

namespace Control\Internal;

use Symfony\Component\Console\Exception\RuntimeException;

require_once(__DIR__ . '/../../../include/class/Class.WIFF.php');

class Context
{
    public static $contextName;
    private static $context;


    public static function isInitialized()
    {
        $contentsFile = sprintf("%s/%s", getenv("WIFF_ROOT"), \WIFF::contexts_filepath);
        return file_exists($contentsFile);
    }

    public static function getContext($verifyContextAccess=true)
    {
        if (!self::isInitialized()) {
            throw new \Exception(sprintf("Context not initialized yet"));
        }
        if (!self::$context) {

            $wiff = \WIFF::getInstance();

            $contextList = $wiff->getContextList( $verifyContextAccess);
            if ($contextList === false) {
                throw new \Exception(sprintf("Error getting contexts list: %s\n", $wiff->errorMessage));
            }
            self::$contextName = $contextList[0]->name;
            self::$context = $wiff->getContext(self::$contextName, $verifyContextAccess);
            if (!self::$context) {
                throw new \Exception(sprintf("Context \"%s\" not exists: %s", self::$contextName, $wiff->errorMessage));
            }
        }
        return self::$context;
    }

    /**
     * get parameter of anakeen-control
     *
     * @return array
     */
    public static function getControlParameters()
    {
        $wiff = \WIFF::getInstance();
        $parameters = $wiff->getParamList();
        ksort($parameters);
        return $parameters;
    }

    /**
     * set parameter of anakeen-control
     *
     * @param string $name
     * @param string $value
     */
    public static function setControlParameter($name, $value)
    {
        $wiff = \WIFF::getInstance();
        $wiff->setParam($name, $value);
        if ($wiff->errorMessage) {
            throw new RuntimeException($wiff->errorMessage);
        }
    }

    /**
     * Get parameters find in all info.xml
     *
     * @return array
     */
    public static function getParameters()
    {
        $context = self::getContext();
        return $context->getParameters();
    }

    /**
     * Set parameters in contexts.xml
     *
     * @param string $paramName
     * @param string $value
     *
     * @return array
     * @throws \Exception
     */
    public static function setParameter($paramName, $value)
    {
        $context = self::getContext();
        return $context->setParamByName($paramName, $value);
    }

    public static function getControlPath()
    {
        $wiff = \WIFF::getInstance();
        return $wiff->getWiffRoot();
    }

    public static function init()
    {
        if (!Context::isInitialized()) {
            require(__DIR__ . '/../../../include/lib/Lib.checkInitServer.php');
            $errors = [];
            if (checkInitServer($errors) === false) {
                throw new RuntimeException(implode(", ", $errors));
            }
        }
    }

    public static function reset()
    {
        if (Context::isInitialized()) {
            $wiff = \WIFF::getInstance();
            $confFile=$wiff->contexts_filepath;
            if (is_file($confFile)) {
                rename($confFile, $confFile.".bak");
            }
        }
    }

    /**
     * @param bool $onlyEnabled
     *
     * @return \Repository[]
     * @throws \Exception
     */
    public static function getRepositories($onlyEnabled = false)
    {

        $wiff = \WIFF::getInstance();
        $allRepos = $wiff->getRepoList();
        $ctxRepos = self::getContext()->repo;
        foreach ($allRepos as &$repo) {
            $searchName = $repo->name;

            $ctxFilterRepos = array_filter($ctxRepos, function ($lrepo) use ($searchName) {
                return $lrepo->name === $searchName;
            });
            if ($ctxFilterRepos) {
                $ctxRepo = array_pop($ctxFilterRepos);

                /** @noinspection PhpUndefinedFieldInspection */
                $repo->status = "activated";
                if ($ctxRepo->errorMessage) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $repo->status = $ctxRepo->errorMessage;
                }
            } else {
                /** @noinspection PhpUndefinedFieldInspection */
                $repo->status = "disabled";
            };
        }
        if ($onlyEnabled === true) {
            return array_filter($allRepos, function ($lrepo) {
                return $lrepo->status !== "disabled";
            });
        }
        return $allRepos;
    }

    public static function getVersion()
    {
        return \WIFF::getVersion();
    }


    public static function getParameterValue($paramName)
    {
        $context = self::getContext();
        $value = $context->getParamByName($paramName);
        if ($context->errorMessage) {
            return null;
        }
        return $value;
    }

    public static function addRepository($name, $url)
    {
        $wiff = \WIFF::getInstance();
        if (is_dir($url)) {
            $url = "file://" . realpath($url);
        }

        $parse = parse_url($url);

        $ret = $wiff->createRepo(
            $name,
            $name,
            $parse['scheme'],
            $parse['host'] ?? "",
            $parse['path'],
            'yes',
            empty($parse['user']) ? "no" : "yes",
            $parse['user'] ?? "",
            $parse['pass'] ?? ""
        );
        if (!$ret) {
            throw new RuntimeException($wiff->errorMessage);
        }

        $context = self::getContext();
        if (!$context->activateRepo($name)) {
            throw new RuntimeException($context->errorMessage);
        }
    }


    public static function removeRepository($name)
    {
        $wiff = \WIFF::getInstance();

        $context = self::getContext();
        $context->deactivateRepo($name);

        if (!$wiff->deleteRepo($name)) {
            throw new RuntimeException($wiff->errorMessage);
        }


    }

    public static function getAvailableVersion()
    {
        $wiff = \WIFF::getInstance();
        return $wiff->getAvailVersion();
    }


    public static function download($url)
    {
        $wiff = \WIFF::getInstance();
        $tmpfile = $wiff->downloadUrl($url);

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