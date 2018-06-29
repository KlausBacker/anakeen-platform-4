<?php
namespace Anakeen\Routes\Admin;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Settings;
use Dcp\Core\Exception;

/**
 * Class Plugins
 * @note    Used by route : GET /admin/plugins
 * @package Anakeen\Routes\Ui
 */
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
        $result = [
            "appName" => ContextManager::getParameterValue(Settings::NsSde, 'CORE_CLIENT'),
            "plugins" => $pluginsConfig
        ];
        return $response->withJson($result);
    }

    /**
     * @throws Exception
     */
    public function getAdminPluginsConfig() {
        $dir = ContextManager::getRootDirectory() . "/" . self::PluginsConfigDir;
        $config = [];
        if (file_exists($dir)) {
            if ($handle = opendir($dir)) {
                while (false !== ($entry = readdir($handle))) {
                    if (preg_match("/\\.xml/", $entry)) {
                        $xmlObject = simplexml_load_file($dir . "/" . $entry, \SimpleXMLElement::class, 0, "admin", true);
                        if (!$xmlObject) {
                            throw new Exception("ADMINPLUGINS0001", $dir . "/" . $entry);
                        }

                        $conf = self::normalizeXMLPlugins($xmlObject->plugins);
                        $config = array_merge_recursive($config, $conf);
                    }
                }
                closedir($handle);

                $config = self::mergePlugins($config);
                $config = json_decode(json_encode($config), true);
            } else {
                throw new Exception("ADMINPLUGINS0002", $dir);
            }
        }

        return self::sortPlugins($config);
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
                    $componentNameRef = $pluginOrder['pluginName'];
                    if (empty($componentNameRef)) {
                        throw new Exception('ADMINPLUGINS0003', $pluginName);
                    }
                    $alreadyAdded = self::getPluginByName($result, $componentNameRef);
                    if (empty($alreadyAdded)) {
                        self::updatePluginsItem($result, $componentNameRef, $orders);
                    }
                    self::insertAfter($result, $componentNameRef, $plugin);
                } elseif ($position === self::PluginsPositionBefore) {
                    $componentNameRef = $pluginOrder['pluginName'];
                    if (empty($componentNameRef)) {
                        throw new Exception('ADMINPLUGINS0003', $pluginName);
                    }
                    $alreadyAdded = self::getPluginByName($result, $componentNameRef);
                    if (empty($alreadyAdded)) {
                        self::updatePluginsItem($result, $componentNameRef, $orders);
                    }
                    self::insertBefore($result, $componentNameRef, $plugin);
                }
            } else {
                throw new Exception('ADMINPLUGINS0004', $pluginName);
            }
        }
        return $result;
    }

    /**
     * Sort plugins considering the order attribute
     * @param array $config plugins to sort
     * @return array sorted plugins
     * @throws Exception
     */
    protected static function sortPlugins(array $config) {
        $result = [];
        if (!empty($config)) {
            foreach ($config as $plugin) {
                self::updatePluginsItem($result, $plugin['name'], $config);
            }
        }
        return $result;
    }

    protected static function normalizeXMLPlugins(\SimpleXMLElement $xmlObject, $parentPluginPath = "") {
        $result = [];
        $wsVersion = ContextManager::getParameterValue(Settings::NsSde, "WVERSION");
        if (!empty($xmlObject) && !empty($xmlObject->plugin)) {
            $plugins = $xmlObject->plugin;
            foreach ($plugins as $xmlPlugin) {
                $pluginComponentName = (string)$xmlPlugin->attributes()->name;
                $plugin = (array) $xmlPlugin;
                $plugin["name"] = $pluginComponentName;
                if (isset($xmlPlugin->order)) {
                    $position = (string)$xmlPlugin->order->attributes()->position;
                    $pluginRef = (string)$xmlPlugin->order->attributes()->pluginName;
                    $plugin['order'] = [
                        "position" => $position
                    ];
                    if (!empty($pluginRef)) {
                        $plugin['order']['pluginName'] = $pluginRef;
                    }
                }
                if (isset($plugin['pluginTemplate'])) {
                    $plugin['pluginTemplate'] = (string)$xmlPlugin->pluginTemplate;
                }
                if (isset($plugin['icon'])) {
                    $plugin['icon'] = (string)$xmlPlugin->icon;
                }
                if (isset($plugin["scriptURL"])) {
                    $plugin["scriptURL"] .= "?ws=$wsVersion";
                }
                if (isset($plugin["debugScriptURL"])) {
                    $plugin["debugScriptURL"] .= "?ws=$wsVersion";
                }
                if (!empty($parentPluginPath)) {
                    $plugin["pluginPath"] = $parentPluginPath."/".$plugin["pluginPath"];
                }
                if (!empty((string)$xmlPlugin->attributes()->override)) {
                    $plugin["override"] = (string)$xmlPlugin->attributes()->override;
                }
                if (!empty($xmlPlugin->sublevel)) {
                    $plugin['sublevel'] = self::normalizeXMLPlugins($xmlPlugin->sublevel, $plugin['pluginPath']);
                }
                if (isset($result[$pluginComponentName])) {
                    $result[$pluginComponentName][] = $plugin;
                } else {
                    $result[$pluginComponentName] = [$plugin];
                }
            }
        }
        return $result;
    }



    protected static function mergePlugins(array $plugins) {
        $result = [];
        foreach ($plugins as $pluginName => $overrides) {
            $completeOverrides = array_filter($overrides, function ($override) {
               return $override['override'] === 'complete';
            });
            $baseOverrides = array_filter($overrides, function ($override) {
               return !isset($override['override'])
                   || ($override['override'] !== 'partial' && $override['override'] !== 'complete');
            });
            switch (count($baseOverrides)) {
                case 0:
                    throw new Exception('ADMINPLUGINS0005', $pluginName);
                case 1:
                    break;
                default:
                    throw new Exception('ADMINPLUGINS0006', $pluginName);
            }
            if (count($baseOverrides) > 1) {
                throw new Exception('ADMINPLUGINS0005', $pluginName);
            }
            $basePlugin = array_shift($baseOverrides);
            usort($completeOverrides, function ($o1, $o2) {
                $p1 = $o1['priority'] ?? 0;
                $p2 = $o2['priority'] ?? 0;
                return $p1 - $p2;
            });

            $completeBase = array_pop($completeOverrides);
            $priorityBase = 0;
            if (!empty($completeBase)) {
                $priorityBase = $completeBase['priority'] ?? 0;
                $basePlugin = $completeBase;
            }
            $partialOverrides = array_filter($overrides, function ($override) use ($priorityBase) {
                $priority = $override['priority'] ?? 0;
                return $override['override'] === 'partial' && $priority >= $priorityBase;
            });
            usort($partialOverrides, function ($o1, $o2) {
                $p1 = $o1['priority'] ?? 0;
                $p2 = $o2['priority'] ?? 0;
                return $p1 - $p2;
            });

            // Merge partials override in order of their priority
            foreach ($partialOverrides as $partialOverride) {
                $basePlugin = array_merge($basePlugin, $partialOverride);
            }
            $result[] = $basePlugin;
        }
        return $result;
    }
}