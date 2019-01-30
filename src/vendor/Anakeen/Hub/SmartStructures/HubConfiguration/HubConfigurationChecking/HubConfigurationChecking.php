<?php
namespace Anakeen\Hub\SmartStructures\HubConfiguration\HubConfigurationChecking;

class HubConfigurationChecking
{
    public static function checkTitle($title, $language)
    {
        return $title && $language;
    }
}
