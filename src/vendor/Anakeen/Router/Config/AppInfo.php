<?php

namespace Anakeen\Router;

/**
 * Class AppInfo
 *
 * Configuration data for an application
 *
 * @see     \Application
 * @package Anakeen\Router
 */
class AppInfo
{
    public $name;
    public $shortName;
    public $pattern;
    public $description;
    public $icon = "";
    public $displayable = true;
    public $parentName;
    public $version;
    /**
     * @var ParameterInfo[]
     */
    public $parameters = [];

    /**
     * @var \Application
     */
    protected $application;

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
     * @throws Exception
     * @throws \Dcp\Db\Exception
     */
    public function record()
    {
        $query = new \QueryDb("", \Application::class);
        $query->addQuery(sprintf("name = '%s'", pg_escape_string($this->name)));
        $list = $query->Query(0, 1);

        if (!$list) {
            $this->addApplication();
        } else {
            $this->updateApplication($list[0]);
        }
    }

    /**
     * Record new application to database
     *
     * @param bool $fullInit if false not add parameters
     *
     * @throws Exception
     */
    public function addApplication($fullInit = true)
    {
        $this->application = new \Application();
        $this->application->name = $this->name;
        $this->application->short_name = $this->shortName;
        $this->application->description = $this->description;
        $this->application->icon = $this->icon;
        $this->application->displayable = $this->displayable;
        $this->application->childof = $this->parentName;

        $this->application->param = new \Param();
        $err = $this->application->add();

        if ($err) {
            throw new Exception($err);
        }

        if ($fullInit) {
            $this->addApplicationParameters(false);
        }
    }

    /**
     * Update application to database
     *
     * @param \Application $app
     *
     * @throws Exception
     */
    protected function updateApplication(\Application $app)
    {
        $this->application = $app;

        $this->application->param = new \Param();
        $this->application->name = $this->name;
        $this->application->short_name = $this->shortName;
        $this->application->description = $this->description;
        $this->application->icon = $this->icon;
        $this->application->displayable = $this->displayable;
        $this->application->childof = $this->parentName;

        $err = $this->application->modify();

        if ($err) {
            throw new Exception($err);
        }


        $currentVersion = $this->application->getParam('VERSION', '');
        if ($currentVersion != '' && $this->version != $currentVersion) {
            $this->application->setParam('PREVIOUS_VERSION', array(
                'val' => $currentVersion,
                'kind' => 'static'
            ));
        }

        $this->addApplicationParameters(true);
    }

    protected function addApplicationParameters($updateMode)
    {


        if ($this->version) {
            $this->application->setParam(
                'VERSION',
                [
                    'val' => $this->version,
                    'kind' => 'static'
                ]
            );
        }

        foreach ($this->parameters as $name => $parameter) {
            /**
             * @var ParameterInfo $parameter
             */
            $paramDefData = [
                "val" => $parameter->value,
                "descr" => $parameter->description,
                "kind" => ($parameter->access === "static" || $parameter->access === "readonly") ? $parameter->access
                    : $parameter->type,
                "global" => empty($parameter->global) ? "N" : "Y",
                "user" => empty($parameter->isUser) ? "N" : "Y"
            ];
            $this->application->setParamDef($name, $paramDefData); // update definition
            if ($updateMode) {
                // don't modify old parameters
                if (true || $parameter->access === "static") {
                    // set only new parameters or static variable like VERSION
                    $this->application->setParam($name, $parameter->value);
                }
            } else {
                $this->application->setParam($name, $parameter->value);
            }
        }
    }
}