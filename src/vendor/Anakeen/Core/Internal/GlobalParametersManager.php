<?php

namespace Anakeen\Core\Internal;

use Anakeen\Core\ContextManager;

class GlobalParametersManager
{

    public static function initialize()
    {
        $core = ContextManager::getCurrentApplication();
        $absindex = $core->GetParam("CORE_URLINDEX");

        $core_externurl = ($absindex) ? self::stripUrlSlahes($absindex) : ".";
        $core_mailaction = $core->getParam("CORE_MAILACTION");
        $core_mailactionurl = ($core_mailaction != '') ? ($core_mailaction) : ($core_externurl . "api/v2/documents/%INITID%.html");

        $core->SetVolatileParam("CORE_EXTERNURL", $core_externurl);
        $core->SetVolatileParam("CORE_MAILACTIONURL", $core_mailactionurl);
    }


    protected static function stripUrlSlahes($url)
    {
        $pos = mb_strpos($url, '://');
        return mb_substr($url, 0, $pos + 3) . preg_replace('/\/+/u', '/', mb_substr($url, $pos + 3));
    }
}
