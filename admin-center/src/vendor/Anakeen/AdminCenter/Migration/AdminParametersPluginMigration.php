<?php

namespace Anakeen\AdminCenter\Migration;

use Anakeen\Core\SEManager;
use SmartStructure\Hubconfigurationgeneric;

class AdminParametersPluginMigration
{

    const OLD_PLUGIN_NAME = "HGEA_PARAMETERS";
    const FROM_NAME = Hubconfigurationgeneric::familyName;

    // Precondition
    public static function checkOldPluginExists()
    {
        $plugin = SEManager::getDocument(self::OLD_PLUGIN_NAME);
        if (!empty($plugin)) {
            $fromid = $plugin->fromid;
            $fromname = SEManager::getNameFromId($fromid);
            return $fromname === self::FROM_NAME;
        }
        return false;
    }

    // Action
    public static function removeOldPlugin()
    {
        $plugin = SEManager::getDocument(self::OLD_PLUGIN_NAME);
        if (!empty($plugin)) {
            $plugin->delete(true, false);
        }
    }

    // Postcondition
    public static function checkOldPluginRemoved()
    {
        return !self::checkOldPluginExists();
    }
}
