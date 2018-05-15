<?php
namespace Anakeen\Routes\Admin;

use Anakeen\Core\ContextManager;
use Dcp\Core\Exception;

class Plugins
{

    const PluginsConfigDir = "config/adminPlugins";
    const PluginsFirstOrder = "first";
    const PluginsAutoOrder = "auto";
    const PluginsPositionBefore = "before";
    const PluginsPositionAfter = "after";

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {

        $pluginsConfig = $this->getAdminPluginsConfig();
        return $response->withJson($pluginsConfig);
    }

    /**
     * @throws Exception
     */
    public function getAdminPluginsConfig() {
        $dir = ContextManager::getRootDirectory() . "/" . self::PluginsConfigDir;
        if ($handle = opendir($dir)) {
            $config = [];

            while (false !== ($entry = readdir($handle))) {
                if (preg_match("/\\.json$/", $entry)) {
                    $content = file_get_contents($dir . "/" . $entry);
                    $conf = json_decode($content, true);

                    if ($conf === null) {
                        throw new Exception("ADMINPLUGINS0001", $dir . "/" . $entry);
                    }
                    $conf = self::normalizePlugins($conf);
                    $config = array_merge_recursive($config, $conf);
                }
            }

            closedir($handle);

            $config = json_decode(json_encode($config), true);

            return self::sortPlugins($config);
        } else {
            throw new Exception("ADMINPLUGINS0002", $dir);
        }
    }

    protected static function insertBefore(array & $result, $pluginName, $new) {
        foreach ($result as $k => & $v) {
            if ($v["name"] === $pluginName) {
                if ($k === 0) {
                    array_unshift($result, $new);
                } else {
                    array_splice($result, $k - 1, 0, [$new]);
                }
                return true;
            }
        }
        return false;
    }

    protected static function insertAfter(array & $result, $pluginName, $new) {
        foreach ($result as $k => & $v) {
            if ($v["name"] == $pluginName) {
                array_splice($result, $k + 1, 0, [$new]);
                return true;
            }
        }
        return false;
    }

    protected static function getPluginByName(array $plugins, $pluginName) {
        $filter = array_filter($plugins,
            function ($plugin) use ($pluginName) {
                return ($plugin['name'] === $pluginName);
            });
        if (empty($filter)) {
            return null;
        } else {
            return array_values($filter)[0];
        }
    }

    protected static function updatePluginsItem(array & $result, $pluginName, $orders) {

        $alreadyAdded = self::getPluginByName($result, $pluginName);

        if (empty($alreadyAdded)) {
            $plugin = self::getPluginByName($orders, $pluginName);

            $pluginOrder = $plugin['order'];
            if (empty($pluginOrder)) {
                // If no specified order, treat as auto
                $result[] = $plugin;
            } elseif (!empty($pluginOrder['position'])) {
                $position = $pluginOrder['position'];
                if ($position === self::PluginsAutoOrder) {
                    $result[] = $plugin;
                } elseif ($position === self::PluginsFirstOrder) {
                    array_unshift($result, $plugin);
                } elseif ($position === self::PluginsPositionAfter) {
                    $componentNameRef = $pluginOrder['componentName'];
                    if (empty($componentNameRef)) {
                        // TODO throw exception
                    }
                    $alreadyAdded = self::getPluginByName($result, $componentNameRef);
                    if (empty($alreadyAdded)) {
                        self::updatePluginsItem($result, $componentNameRef, $orders);
                    }
                    self::insertAfter($result, $componentNameRef, $plugin);
                } elseif ($position === self::PluginsPositionBefore) {
                    $componentNameRef = $pluginOrder['componentName'];
                    if (empty($componentNameRef)) {
                        // TODO throw exception
                    }
                    $alreadyAdded = self::getPluginByName($result, $componentNameRef);
                    if (empty($alreadyAdded)) {
                        self::updatePluginsItem($result, $componentNameRef, $orders);
                    }
                    self::insertBefore($result, $componentNameRef, $plugin);
                }
            } else {
                // TODO throw exception
            }
        }
        return $result;
    }


    protected static function sortPlugins(array $config) {
        $result = [];
        if (!empty($config)) {
            foreach ($config as $plugin) {
                self::updatePluginsItem($result, $plugin['name'], $config);
            }
        }
        return $result;
    }

    protected static function normalizePlugins(array $config, $parentComponentPath = "") {
        $result = [];
        if (!empty($config)) {
            $plugins = $config;
            foreach ($plugins as $pluginComponentName => $plugin) {
                $plugin["name"] = $pluginComponentName;
                if (!empty($parentComponentPath)) {
                    $plugin["componentPath"] = $parentComponentPath."/".$plugin["componentPath"];
                }
                if (!empty($plugin['subcomponents'])) {
                    $plugin['subcomponents'] = self::normalizePlugins($plugin['subcomponents'], $plugin['componentPath']);
                }
                $result[] = $plugin;
            }
        }
        return $result;
    }
}