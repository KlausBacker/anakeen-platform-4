<?php

namespace Anakeen\SmartCriteria;

use Anakeen\Core\ContextManager;

class SmartCriteriaConfigManager
{

    public static function getSmartCriteriaConfigPaths()
    {
        // Also add paths from app parameter ?

        $configPath[] = \Anakeen\Core\Settings::SmartCriteriaConfigDir;
        $absConfigPath = [];
        foreach ($configPath as $cpath) {
            $absConfigPath[] = ContextManager::getRootDirectory() . "/" . $cpath;
        }
        return $absConfigPath;
    }
}
