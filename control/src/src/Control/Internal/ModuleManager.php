<?php


namespace Control\Internal;

use Control\Exception\RuntimeException;
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
    protected $mainPhase;


    /**
     * @var array
     */
    protected $parameters = [];
    /**
     * @var string
     */
    protected $moduleFilePath = "";
    /**
     * @var bool
     */
    protected $moduleFileForceInstall = false;

    public function __construct($moduleName)
    {
        $this->name = $moduleName;
        $this->context = Context::getContext();
        if ($this->name) {
            $this->getAvailableModule();
        }
    }

    /**
     * @return mixed
     */
    public function getMainPhase()
    {
        return $this->mainPhase;
    }

    public function getFile()
    {
        return $this->moduleFilePath;
    }

    public function setFile($filePath, $forceInstall = false)
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException(sprintf("File \"%s\" not found", $filePath));
        }
        $this->moduleFilePath = realpath($filePath);
        $this->moduleFileForceInstall = $forceInstall;
    }

    public function getAvailableModule(): ?\Module
    {
        $this->module = $this->context->getModuleAvail($this->name);

        return $this->module ?: null;
    }

    public function getInstalledModule($moduleName): ?\Module
    {
        $module = $this->context->getModuleInstalled($moduleName);

        return $module ?: null;
    }


    protected function prepareLocalInstall($pkgName, $force = false)
    {
        $tmpfile = \Control\Internal\LibSystem::tempnam(null, basename($pkgName));
        if ($tmpfile === false) {
            throw new RuntimeException(sprintf("Error: could not create temp file!\n"));
        }

        $ret = copy($pkgName, $tmpfile);
        if ($ret === false) {
            throw new RuntimeException(sprintf("Error: could not copy '%s' to '%s'!\n", $pkgName, $tmpfile));
        }
        $context = Context::getContext();

        $tmpMod = $context->loadModuleFromPackage($tmpfile);
        if ($tmpMod === false) {
            throw new RuntimeException(sprintf(
                "Error: could not load module '%s': %s\n",
                $tmpfile,
                $context->errorMessage
            ));
        }

        $moduleName = $tmpMod->name;
        $this->name = $moduleName;
        $existingModule = $context->getModuleInstalled($moduleName);
        if ($existingModule !== false) {
            if ($force === false) {
                throw new RuntimeException(sprintf(
                    "A module '%s' with version '%s' already exists [CTRL011].\n",
                    $existingModule->name,
                    $existingModule->version
                ));
            }
        }

        $tmpMod = $context->importArchive($tmpfile, 'downloaded');
        if ($tmpMod === false) {
            throw new RuntimeException(sprintf(
                "Error: could not import module '%s': %s\n",
                $tmpfile,
                $context->errorMessage
            ));
        }


        $depList = $context->getLocalModuleDependencies($tmpfile);
        if ($depList === false) {
            throw new RuntimeException(sprintf(
                "Error: could not get dependencies for '%s': %s\n",
                $tmpfile,
                $context->errorMessage
            ));
        }

        $this->depList = $depList;
        foreach ($this->depList as &$module) {
            if ($module->name === $moduleName) {
                if ($this->moduleFileForceInstall === true) {
                    $module->needphase = "install";
                } else {
                    $module->needphase = $existingModule ? "upgrade" : "install";
                }
            }
        }
    }

    public function prepareRemove()
    {
        $this->mainPhase = "remove";

        $installedModule = $this->getInstalledModule($this->name);
        if (!$installedModule) {
            throw new RuntimeException(sprintf("Module '%s' is not installed [CTRL013].\n", $this->name));
        }

        $this->depList = [$installedModule];

        if ($this->depList === false) {
            throw new RuntimeException($this->context->errorMessage);
        }
        foreach ($this->depList as &$module) {
            $module->needphase = 'delete';
        }
        return true;
    }

    public function prepareInstall($force = false)
    {
        $this->mainPhase = "install";
        if ($this->moduleFilePath) {
            $this->prepareLocalInstall($this->moduleFilePath, $force);
        } elseif ($this->name) {
            if (!$this->module) {
                throw new RuntimeException(sprintf("Module \"%s\" not found", $this->name));
            }
            $installedModule = $this->getInstalledModule($this->name);
            if ($installedModule && $force === false) {
                throw new RuntimeException(sprintf(
                    "Module '%s' (version '%s') is already installed [CTRL011].\n",
                    $installedModule->name,
                    $installedModule->version
                ));
            }

            $this->depList = $this->context->getModuleDependencies(array(
                $this->name
            ));
        } else {
            $moduleList = $this->context->getAvailableModuleList(true);
            $moduleNames = array_map(function ($module) {
                return $module->name;
            }, $moduleList);
            if (empty($moduleList)) {
                return false;
            }
            $this->depList = $this->context->getModuleDependencies($moduleNames);
        }
        if ($this->depList === false) {
            throw new RuntimeException($this->context->errorMessage);
        }
        foreach ($this->depList as &$module) {
            if (!$module->needphase) {
                $module->needphase = 'install';
            }
        }
        return true;
    }

    public function prepareUpgrade($force = false)
    {
        $this->mainPhase = "upgrade";
        if ($this->name) {
            if (!$this->module) {
                throw new RuntimeException(sprintf("Module \"%s\" not found", $this->name));
            }
            $installedModule = $this->getInstalledModule($this->name);
            if ($installedModule) {
                $cmp = \Context::cmpModuleByVersionAsc($this->module, $installedModule);
                if ($cmp <= 0) {
                    if (!$force) {
                        throw new RuntimeException(sprintf(
                            "The installed module '%s' (version '%s') is more recent than '%s' [CTRL010].\n",
                            $installedModule->name,
                            $installedModule->version,
                            $this->module->version
                        ));
                    }
                }
            } else {
                throw new RuntimeException(sprintf("Installed Module \"%s\" not found", $this->name));
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
            throw new RuntimeException($this->context->errorMessage);
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
        $output->getFormatter()->setStyle('d', new OutputFormatterStyle('red', null, []));
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('black', 'yellow', []));

        $output->writeln("Will <i>(i)</i>nstall, <u>(u)</u>pgrade, <d>(d)</d>elete, or <r>(r)</r>eplace the following modules:");
        foreach ($this->depList as $module) {
            if (!$module->needphase) {
                $module->needphase = 'upgrade';
            }
            $op = '<i>(i)</i>';
            if ($module->needphase === 'upgrade') {
                $op = '<u>(u)</u>';
            } else {
                if ($module->needphase === 'delete') {
                    $op = '<d>(d)</d>';
                } else {
                    if ($module->needphase === 'replaced') {
                        $op = '<r>(r)</r> (replaced by ' . (($module->replacedBy) ? $module->replacedBy : 'unknown') . ')';
                    }
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
        if (!$this->parameters) {
            /** @var \Module $module */
            $contentXml = [];
            foreach ($this->depList as $module) {
                $repo = $module->repository;
                if ($repo) {
                    $contentXml[] = $repo->getContentUrl();
                }
            }

            $contentXml = array_filter($contentXml, function ($a) {
                return !empty($a);
            });
            $contentXml = array_unique($contentXml);
            foreach ($contentXml as $xmlUrl) {
                $this->recordParametersDefinition(Context::download($xmlUrl));
            }
            if ($this->moduleFilePath) {
                $this->recordLocalParametersDefinition();
            }
        }
        return $this->parameters;
    }


    /**
     * Add parameters for install/update from local app file
     */
    protected function recordLocalParametersDefinition()
    {
        $wiff = \WIFF::getInstance();
        $xmlContent = file_get_contents($wiff->contexts_filepath);
        self::recordParametersDefinition($xmlContent, '/contexts/context/modules/module[@status="downloaded"]');
    }

    /**
     * Record in object module parameter of a repository
     *
     * @param string $xmlContent content.xml
     * @param string $moduleXPath XPATH for search modules
     */
    protected function recordParametersDefinition($xmlContent, $moduleXPath = "/repo/modules/module")
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xmlContent);

        $xpath = new \DOMXPath($dom);
        $nodeModules = $xpath->query($moduleXPath);
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

    public function setParameterAnswer($moduleName, $paramName, $value)
    {
        if (!$this->parameters) {
            $this->getAllParameters();
        }
        foreach ($this->parameters as &$parameter) {
            if ($parameter["name"] === $paramName && $parameter["module"] === $moduleName) {
                $parameter["answer"] = $value;
            }
        }
    }

    public static function runJobInBackground()
    {
        $command = sprintf("%s/anakeen-control dojob", realpath(__DIR__ . "/../../../"));
        exec("exec nohup $command > /dev/null 2>&1 &", $result, $status);

        if ($status !== 0) {
            throw new RuntimeException("BgExec Script Error");
        }
    }

    public function recordJob($justRecord = false)
    {
        ModuleJob::initJobTask($this);
        if ($justRecord === false) {
            self::runJobInBackground();
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isModuleFileForceInstall(): bool
    {
        return $this->moduleFileForceInstall;
    }
}
