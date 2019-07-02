<?php

namespace Control\Internal;


class FatalHandler
{

    public static function handleFatalShutdown()
    {
        $error = error_get_last();

        if ($error !== null) {
            if (in_array($error["type"], array(
                E_ERROR,
                E_PARSE,
                E_COMPILE_ERROR,
                E_CORE_ERROR,
                E_USER_ERROR,
                E_RECOVERABLE_ERROR
            ))) {
                ob_get_clean();
                if (!headers_sent()) {
                    header("HTTP/1.1 500 Anakeen Control Fatal Error");
                }

               print $error;

                // Fatal error are already logged by PHP
            }
        }
    }

}
