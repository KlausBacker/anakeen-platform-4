<?php

namespace Anakeen\Router;

class AppInfo
{

    public $name;
    public $short_name;
    public $pattern;
    public $description;
    public $icon = [];
    public $displayable = true;
    public $parentName;


    public function __construct($data = null)
    {
        if ($data) {
            $vars = get_object_vars($data);

            foreach ($vars as $k => $v) {
                $this->$k = $v;
            }
        }
    }

    public function record()
    {
        $query = new \QueryDb("", \Application::class);
        $query->addQuery(sprintf("name = '%s'", pg_escape_string($this->name)));
        $list = $query->Query(0, 0, "TABLE");

        if (!$list) {
            $this->addApplication();
        } else {
            $this->updateApplication();
        }
    }

    protected function addApplication()
    {
        $app=new \Application();
        $app->name=$this->name;
        $app->short_name=$this->short_name;
        $app->description=$this->description;
        $app->icon=$this->icon;
        $app->displayable=$this->displayable;
        $app->childof=$this->parentName;

        $err=$app->add();
        if ($err) {
            throw new Exception($err);
        }
    }

    protected function updateApplication()
    {

    }
}