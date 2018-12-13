<?php

namespace Anakeen\Router\Config;

use Anakeen\Core\Internal\Param;

class ParameterInfo
{
    public $name;
    public $value = "";
    public $description = "";
    public $access;
    public $type = "text";
    public $domain;
    public $category;
    public $isUser = false;
    public $configFile = "";

    public function __construct($data = null)
    {
        if ($data) {
            $vars = get_object_vars($data);

            foreach ($vars as $k => $v) {
                $this->$k = $v;
            }
        }
    }


    /**
     * Record application to database
     *
     */
    public function record()
    {
        $pdef = new \Anakeen\Core\Internal\ParamDef("", $this->name);

        $updateMode = ($pdef->isAffected());
        $pdef->name = $this->name;
        $pdef->descr = $this->description;
        $pdef->domain = $this->domain;
        $pdef->category = $this->category;
        $pdef->kind = (!empty($this->access) && ($this->access === "static" || $this->access === "readonly")) ? $this->access
            : (!empty($this->type) ? $this->type : 'text');
        $pdef->isuser = empty($this->isUser) ? "N" : "Y";
        if ($updateMode) {
            $err = $pdef->modify();
        } else {
            $err = $pdef->add();
        }

        if ($err) {
            throw new \Anakeen\Exception($err);
        }

        $pval = new  Param();
        if ($updateMode) {
            // don't modify previous parameters configuration
            if ($this->access === "static") {
                // set only new parameters or static variable like VERSION
                $pval->set($this->name, $this->value);
            }
        } else {
            $pval->set($this->name, $this->value);
        }
    }
}
