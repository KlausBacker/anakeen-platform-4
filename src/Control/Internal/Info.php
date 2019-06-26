<?php

namespace Control\Internal;

class Info
{
    public static function getInfo() {
        $data["version"]=Context::getVersion();
        $data["availableVersion"]=Context::getAvailableVersion();

        $data["userStatistics"]=Platform::getUserStats();
        return $data;
    }
    public static function getInstalledModuleList() {
        $modules=Context::getContext()->getInstalledModuleList(true);

          usort($modules, function ($a, $b) {
              return strcmp($a->name, $b->name);
          });
        return $modules;
    }
    public static function getAllModuleList() {
        $modules=Context::getContext()->getInstalledModuleList(true);

        usort($modules, function ($a, $b) {
            return strcmp($a->name, $b->name);
        });

        $uninstalled = Context::getContext()->getAvailableModuleList(true);

        usort($uninstalled, function ($a, $b) {
            return strcmp($a->name, $b->name);
        });

        return array_merge($modules, $uninstalled);
    }


    public static function getModuleOutdatedList() {
        $modules=Context::getContext()->getInstalledModuleList(true);

        $modules= array_filter($modules, function ($a) {
            /** @var \Module $a */
            return $a->canUpdate;
        });
        usort($modules, function ($a, $b) {
            return strcmp($a->name, $b->name);
        });
        return $modules;
    }
}