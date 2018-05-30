<?php

namespace Anakeen\Router\Config;

use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\Application;
use Anakeen\Router\Exception;

class ParameterInfo
{
    public $name;
    public $value = "";
    public $description = "";
    public $access;
    public $type = "text";
    public $category;
    public $isUser = false;
    public $global = true;
    public $applicationContext = "CORE";
    public $configFile = "";

    public function __construct($data = null)
    {
        if ($data) {
            $vars = get_object_vars($data);

            foreach ($vars as $k => $v) {
                $this->$k = $v;
            }
            if ($this->applicationContext !== "CORE") {
                $this->global = false;
            }
        }
    }


    /**
     * Record application to database
     *
     * @throws Exception
     * @throws \Dcp\Db\Exception
     */
    public function record()
    {

        $paramDefData = [
            "val" => $this->value,
            "descr" => $this->description,
            "kind" => (!empty($this->access) && ($this->access === "static" || $this->access === "readonly")) ? $this->access
                : (!empty($this->type) ? $this->type : 'text'),
            "global" => empty($this->global) ? "N" : "Y",
            "user" => empty($this->isUser) ? "N" : "Y"
        ];
        $app = new Application();
        $app->set($this->applicationContext);

        $pdef = \Anakeen\Core\Internal\ParamDef::getParamDef($this->name, $app->id);


        $updateMode = ($pdef !== null);
        $app->setParamDef($this->name, $paramDefData); // update definition

        if ($updateMode) {
            // don't modify old parameters
            if ($this->access === "static") {
                // set only new parameters or static variable like VERSION
                $app->setParam($this->name, $this->value);
            }
        } else {
            $app->setParam($this->name, $this->value);
        }
    }
}
