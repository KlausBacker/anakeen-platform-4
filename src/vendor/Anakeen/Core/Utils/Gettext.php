<?php

namespace Anakeen\Core\Utils {

    class Gettext
    {
        public static function pgettext($context, $msgid)
        {
            $contextString = "{$context}\004{$msgid}";
            $translation = _($contextString);
            if ($translation === $contextString) {
                return $msgid;
            } else {
                return $translation;
            }
        }

        public static function npgettext($context, $msgid, $msgid_plural, $num)
        {
            $contextString = "{$context}\004{$msgid}";
            $contextStringp = "{$context}\004{$msgid_plural}";
            $translation = ngettext($contextString, $contextStringp, $num);
            if ($translation === $contextString) {
                return $msgid;
            } elseif ($translation === $contextStringp) {
                return $msgid_plural;
            } else {
                return $translation;
            }
        }

        public static function ___($message, $context = "")
        {
            if ($context != "") {
                return self::pgettext($context, $message);
            } else {
                return _($message);
            }
        }

        // New gettext keyword for plural strings with optional context argument
        public static function n___($message, $message_plural, $num, $context = "")
        {
            if ($context != "") {
                return self::npgettext($context, $message, $message_plural, abs($num));
            } else {
                return ngettext($message, $message_plural, abs($num));
            }
        }
    }
}

namespace {

    // New gettext keyword for regular strings with optional context argument
    function ___($message, $context = "")
    {
        return Anakeen\Core\Utils\Gettext::___($message, $context);
    }

    // New gettext keyword for plural strings with optional context argument
    function n___($message, $message_plural, $num, $context = "")
    {
        return Anakeen\Core\Utils\Gettext::n___($message, $message_plural, $num, $context);
    }
}