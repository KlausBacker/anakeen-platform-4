<?php

namespace Anakeen\Core\Internal;


use Anakeen\Core\Settings;

class GlobalParametersManager
{

    public static function initialize()
    {
        $absindex = ContextParameterManager::getValue(Settings::NsSde, "CORE_URLINDEX");
        if ($absindex == '') {
            $absindex = "./";
        }

        $core_externurl = ($absindex) ? self::stripUrlSlahes($absindex) : ".";
        $core_mailaction = ContextParameterManager::getValue(Settings::NsSde, "CORE_MAILACTION");
        $core_mailactionurl = ($core_mailaction != '') ? ($core_mailaction) : ($core_externurl . "api/v2/documents/%INITID%.html");

        ContextParameterManager::setVolatile(\Anakeen\Core\Settings::NsSde, "CORE_EXTERNURL", $core_externurl);
        ContextParameterManager::setVolatile(\Anakeen\Core\Settings::NsSde, "CORE_MAILACTIONURL", $core_mailactionurl);
    }

    /**
     * Delete double slashes in url path
     *
     * @param string $url
     *
     * @return string
     */
    protected static function stripUrlSlahes($url)
    {
        $pos = mb_strpos($url, '://');
        return mb_substr($url, 0, $pos + 3) . preg_replace('/\/+/u', '/', mb_substr($url, $pos + 3));
    }

    /**
     * return variable from dbaccess.php
     *
     * @param string $varName
     *
     * @return string|null
     * @throws \Dcp\Exception
     */
    public static function getDbAccessValue($varName)
    {
        $included = false;

        $filename = sprintf("%s/%s", DEFAULT_PUBDIR, \Anakeen\Core\Settings::DbAccessFilePath);


        if (file_exists($filename)) {
            if (include($filename)) {
                $included = true;
            }
        }
        if (!$included) {
            fprintf(STDERR, "Error: %s", $filename);
            throw new \Dcp\Exception("FILE0005", $filename);
        }

        if (!isset($$varName)) {
            return null;
        }
        return $$varName;
    }
}
