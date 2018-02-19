<?php

namespace Anakeen\Router;

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
    public $applicationContext = "CORE";

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