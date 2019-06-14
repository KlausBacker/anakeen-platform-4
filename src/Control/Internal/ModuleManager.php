<?php


namespace Control\Internal;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

require_once __DIR__ . "/../../../include/class/Class.Module.php";

class ModuleManager
{
    protected $name;
    /** @var \Context */
    protected $context;
    /** @var \Module */
    protected $module;
    protected $depList;
    /**
     * @var array
     */
    protected $parameters = [];

    public function __construct($moduleName)
    {
        $this->name = $moduleName;
        $this->context = Context::getContext();
        if ($this->name) {

            $this->getAvailableModule();
        }

    }

    public function getAvailableModule(): ?\Module
    {
        $this->module = $this->context->getModuleAvail($this->name);
        if (!$this->module) {
            throw new InvalidArgumentException(sprintf("Download Module \"%s\" not found", $this->name));
        }
        return $this->module;
    }

    public function getInstalledModule($moduleName): ?\Module
    {
        $module = $this->context->getModuleInstalled($moduleName);
        if (!$module) {
            throw new InvalidArgumentException(sprintf("Installed Module \"%s\" not found", $moduleName));
        }
        return $module ?: null;
    }

    public function preUpgrade($force = false)
    {
        if ($this->name) {
            $installedModule = $this->getInstalledModule($this->name);
            if ($installedModule) {
                $cmp = \Context::cmpModuleByVersionAsc($this->module, $installedModule);
                if ($cmp <= 0) {
                    if (!$force) {
                        throw new RuntimeException(sprintf("The installed module '%s' (version '%s') is more recent than '%s' [CTRL010].\n", $installedModule->name,
                            $installedModule->version, $this->module->version));
                    }
                }
            }
            $this->depList = $this->context->getModuleDependencies(array(
                $this->name
            ));
        } else {
            $moduleList = array_filter($this->context->getInstalledModuleListWithUpgrade(true), function ($module) {
                return $module->canUpdate === true;
            });
            $moduleNames = array_map(function ($module) {
                return $module->name;
            }, $moduleList);
            if (empty($moduleList)) {

                return false;
            }
            $this->depList = $this->context->getModuleDependencies($moduleNames);
        }
        if ($this->depList === false) {
            throw new InvalidArgumentException($this->context->errorMessage);
        }
        foreach ($this->depList as &$module) {
            if (!$module->needphase) {
                $module->needphase = 'upgrade';
            }
        }
        return true;
    }

    /**
     * @return \Module[]
     */
    public function getDepencies()
    {
        return $this->depList;
    }

    public function displayModulesToProcess(OutputInterface $output)
    {

        $output->getFormatter()->setStyle('u', new OutputFormatterStyle('yellow', null, []));
        $output->getFormatter()->setStyle('i', new OutputFormatterStyle('green', null, []));
        $output->getFormatter()->setStyle('r', new OutputFormatterStyle('cyan', null, []));
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('black', 'yellow', []));

        $output->writeln("Will <i>(i)</i>nstall, <u>(u)</u>pgrade or <r>(r)</r>eplace the following modules:");
        foreach ($this->depList as $module) {
            if (!$module->needphase) {
                $module->needphase = 'upgrade';
            }
            $op = '<i>(i)</i>';
            if ($module->needphase === 'upgrade') {
                $op = '<u>(u)</u>';
            } else {
                if ($module->needphase === 'replaced') {
                    $op = '<r>(r)</r> (replaced by ' . (($module->replacedBy) ? $module->replacedBy : 'unknown') . ')';
                }
            }
            $error = "";

            if ($module->errorMessage) {
                $error = "(<error>" . $module->errorMessage . "</error>)";
            }
            $warning = "";
            if ($module->warningMessage) {
                $warning = "(<warning>" . $module->warningMessage . "</warning>)";
            }
            $output->writeln(sprintf("- %s %s %s %s%s", $op, $module->name, $module->version, $error, $warning));
        }
    }

    public function getAllParameters()
    {
        /** @var \Module $module */
        $contentXml = [];
        foreach ($this->depList as $module) {
            $repo = $module->repository;
            $contentXml[] = $repo->getContentUrl();
        }

        $contentXml = array_filter($contentXml, function ($a) {
            return !empty($a);
        });
        $contentXml = array_unique($contentXml);
        foreach ($contentXml as $xmlUrl) {
            $this->recordParameters(Context::download($xmlUrl));
        }

        return $this->parameters;
    }

    /**
     * Record in object module parameter of a repository
     * @param string $xmlContent content.xml
     */
    protected function recordParameters($xmlContent)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xmlContent);

        $xpath = new \DOMXPath($dom);
        $nodeModules = $xpath->query("/repo/modules/module");
        foreach ($nodeModules as $nodeModule) {
            /** @var \DOMElement $nodeModule */
            $paramNodes = $xpath->query("parameters/param", $nodeModule);
            foreach ($paramNodes as $paramNode) {
                /** @var \DOMElement $paramNode */
                $parameter = [
                    "module" => $nodeModule->getAttribute("name")
                ];

                foreach ($paramNode->attributes as $attribute) {
                    $parameter[$attribute->name] = $attribute->value;
                }
                $this->parameters[] = $parameter;
            }
        }
    }


    protected function runJobInBackground() {
        $command=sprintf("%s/anakeen-control dojob", realpath(__DIR__."/../../../"));
        exec("exec nohup $command > /dev/null 2>&1 &", $result, $status);
        //if (session_id()) @session_start();
        if ($status !== 0) {
            throw new RuntimeException("BgExec Script Error");
        }
    }

    public function recordJob()
    {
        ModuleJob::initJobTask($this);
        self::runJobInBackground();

    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}