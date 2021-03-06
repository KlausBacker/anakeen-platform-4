<?php

namespace Anakeen\Core\Utils;

class ErrorMessage
{
    public static function getHtml($htmlMessage, $errId, $tooltip = "")
    {
        $lay = new \Anakeen\Layout\TextLayout("CORE/Layout/error.html");
        $lay->set("TITLE", _("Error"));
        $lay->set("tooltip", $tooltip);
        $lay->set("error", str_replace("[", "&#x5b;", $htmlMessage));
        $lay->eset("code", $errId ? "[$errId]" : "");

        $lay->set("ico", \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "DYNACASE_FAVICO"));
        return $lay->gen();
    }

    public static function getText($textMessage, $errId)
    {
        if ($errId) {
            return sprintf("[%s] %s", $errId, $textMessage);
        } else {
            return $textMessage;
        }
    }

    public static function getJson($htmlMessage, $errId, $exception = null)
    {
        if ($exception !== null && is_a($exception, \Anakeen\Exception::class)) {
            $error = json_decode(json_encode($exception), true);
            $error["exceptionMessage"] = self::getText($htmlMessage, $errId);
            $error["message"] = $error["userMessage"] ?: $error["exceptionMessage"];
        } else {
            $error = ["success" => false];
            $error["exceptionMessage"] = self::getText($htmlMessage, $errId);
        }
        return json_encode($error);
    }

    /**
     * return message according to accept HTTP header
     *
     * @param string $errorMessage
     * @param string $code
     *
     * @return string
     */
    public static function getError($errorMessage, $code = "")
    {
        $accept = $_SERVER['HTTP_ACCEPT'];
        $useHtml = ((!empty($accept) && preg_match("@\\btext/html\\b@", $accept)));

        if ($useHtml) {
            return self::getHtml($errorMessage, $code);
        } else {
            $useJSON = ((!empty($accept) && preg_match("@\\bapplication/json\\b@", $accept)));
            if ($useJSON) {
                header('Content-Type: application/json');
                return self::getJson($errorMessage, $code);
            } else {
                header('Content-Type: text/plain');
                return self::getText($errorMessage, $code);
            }
        }
    }
}
