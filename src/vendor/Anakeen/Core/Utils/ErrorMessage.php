<?php

namespace Dcp\Core\Utils;

use Dcp\DevTools\Template\ApplicationParameter;

class ErrorMessage
{
    public static function getHtml($htmlMessage, $errId, $tooltip = "")
    {
        $lay = new \Layout("CORE/Layout/error.html");
        $lay->set("TITLE", _("Error"));
        $lay->set("tooltip", $tooltip);
        $lay->set("error", str_replace("[", "&#x5b;", $htmlMessage));
        $lay->eset("code", $errId ? "[$errId]" : "");

        $lay->set("ico", \ApplicationParameterManager::getScopedParameterValue("DYNACASE_FAVICO"));
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

    public static function getJson($htmlMessage, $errId)
    {
        $error = ["success" => false, "exceptionMessage" => self::getText($htmlMessage, $errId)];
        return json_encode($error);
    }
}