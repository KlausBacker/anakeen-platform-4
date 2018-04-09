<?php

namespace Anakeen\Router\Config;

class RouterInfo
{
    public $priority;
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
    public $applicationContext = "CORE";
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
}