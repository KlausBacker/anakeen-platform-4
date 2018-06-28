<?php

namespace Anakeen\Router;

use Anakeen\Core\ContextManager;

class FatalHandler
{

    public static function handleFatalShutdown()
    {
        global $action;

        $error = error_get_last();

        if ($error !== null && $action) {
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
                    header("HTTP/1.1 500 Anakeen Fatal Error");
                }

                $displayMsg = \Anakeen\Core\LogException::logMessage($error, $errId);
                if ($action) {
                    ContextManager::exitError($displayMsg, false, $errId);
                } else {
                    print $displayMsg;
                }
                // Fatal error are already logged by PHP
            }
        }
    }

    /**
     * @param Exception|\Error $e
     *
     */
    public static function handleActionException($e)
    {
        if (php_sapi_name() !== "cli") {
            if (method_exists($e, "addHttpHeader")) {
                /**
                 * @var \Dcp\Exception $e
                 */
                if ($e->getHttpHeader()) {
                    header($e->getHttpHeader());
                } else {
                    header("HTTP/1.1 500 Dynacase Uncaught Exception");
                }
            } else {
                header("HTTP/1.1 500 Dynacase Uncaught Exception");
            }
        }

        $displayMsg = \Anakeen\Core\LogException::logMessage($e, $errId);

        if (php_sapi_name() === 'cli') {
            fwrite(STDERR, sprintf("[%s]: %s\n", $errId, $displayMsg));
        } else {
            if (is_a($e, "\\Anakeen\\Router\\Exception")) {
                /**
                 * @var \Anakeen\Router\Exception $e
                 */
                header(sprintf("HTTP/1.0 %d %s", $e->getHttpStatus(), $e->getHttpMessage()));
            }

            print \Dcp\Core\Utils\ErrorMessage::getError($displayMsg, $errId);
        }
        exit(1);
    }
}
