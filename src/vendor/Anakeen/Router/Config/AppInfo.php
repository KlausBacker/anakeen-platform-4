<?php

namespace Anakeen\Router\Config;
use Anakeen\Router\Exception;
/**
 * Class AppInfo
 *
 * Configuration data for an application
 *
 * @see \Anakeen\Core\Internal\Application
 * @package Anakeen\Router
 */
class AppInfo
{
    public $name;
    public $shortName;
    public $description;
    public $icon = "";
    public $displayable = true;
    public $parentName;
    public $version;
    public $override;
    /**
     * @var ParameterInfo[]
     */
    public $parameters = [];

    /**
     * @var \Anakeen\Core\Internal\Application
     */
    protected $application;
    public $configFile;

    public function __construct($data = null)
    {
        $this->set($data);
    }

    public function set($data = null)
    {
        if ($data) {
            $vars = get_object_vars($data);

            foreach ($vars as $k => $v) {
                if (is_a($v, \stdClass::class)) {
                    $v= get_object_vars($v);
                }
                if (!empty($this->$k) && is_array($this->$k) && is_array($v)) {
                    $this->$k = array_merge($this->$k, $v);
                } else {
                    $this->$k = $v;
                }
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
        $query = new \Anakeen\Core\Internal\QueryDb("", \Anakeen\Core\Internal\Application::class);
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
        $this->application = new \Anakeen\Core\Internal\Application();
        $this->application->name = $this->name;
        $this->application->short_name = $this->shortName;
        $this->application->description = $this->description;
        $this->application->icon = $this->icon;
        $this->application->displayable = $this->displayable?"Y":"N";
        $this->application->childof = $this->parentName;
        $this->application->available = 'Y';

        $this->application->param = new \Anakeen\Core\Internal\Param();
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
     * @param \Anakeen\Core\Internal\Application $app
     *
     * @throws Exception
     */
    protected function updateApplication(\Anakeen\Core\Internal\Application $app)
    {
        $this->application = $app;

        $this->application->param = new \Anakeen\Core\Internal\Param();
        $this->application->name = $this->name;
        $this->application->short_name = $this->shortName;
        $this->application->description = $this->description;
        $this->application->icon = $this->icon;
        $this->application->displayable = $this->displayable?"Y":"N";
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
                "kind" => (!empty($parameter->access) && ($parameter->access === "static" || $parameter->access === "readonly")) ? $parameter->access
                    : (!empty($parameter->type) ? $parameter->type : 'text'),
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
