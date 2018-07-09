<?php

namespace Anakeen\Router\Config;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Settings;

class RouterInfo
{
    protected static $statuses = null;
    public $priority = 0;
    /**
     * @var \Callable
     */
    public $callable;
    public $pattern;
    public $description;
    public $name;
    public $methods = [];
    public $authenticated = true;
    /**
     * @var string partial or complete
     */
    public $override;
    /**
     * @var RequiredAccessInfo
     */
    public $requiredAccess;
    public $configFile;

    public function __construct($data = null)
    {
        if ($data) {
            $vars = get_object_vars($data);

            foreach ($vars as $k => $v) {
                $this->$k = $v;
            }
        }
    }

    public function isActive()
    {
        $statuses = self::getStatuses();
        return !isset($statuses[$this->name]);
    }

    /**
     * Deactivate / Reactivate a route
     * @param bool $activate set to false to deactivate , true to reactivate
     */
    public function setActive(bool $activate)
    {
        self::getStatuses();
        if ($activate === false) {
            self::$statuses[$this->name] = ["status" => "deactivated"];
        } else {
            unset(self::$statuses[$this->name]);
        }
        ContextManager::setParameterValue(Settings::NsSde, "CORE_ROUTESSTATUSES", json_encode(self::$statuses));
    }

    protected static function getStatuses()
    {
        if (self::$statuses === null) {
            self::$statuses = json_decode(ContextManager::getParameterValue(Settings::NsSde, "CORE_ROUTESSTATUSES"), true);
        }
        return self::$statuses;
    }
}
