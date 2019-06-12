<?php


namespace Control\Internal;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

require_once __DIR__ . "/../../../include/class/Class.Module.php";

class Module
{
    protected $name;
    /** @var \Context */
    protected $context;
    /** @var \Module */
    protected $module;
    protected $depList;

    public function __construct($moduleName)
    {
        $this->name = $moduleName;
        $this->context = Context::getContext();

    }

    public function getAvailableModule(): ?\Module
    {
        $this->module = $this->context->getModuleAvail($this->name);
        if (!$this->module) {
            throw new InvalidArgumentException(sprintf("Download Module \"%s\" not found", $this->name));
        }
        return $this->module;
    }

    public function getInstalledModule(): ?\Module
    {
        $this->module = $this->context->getModuleInstalled($this->name);
        if (!$this->module) {
            throw new InvalidArgumentException(sprintf("Installed Module \"%s\" not found", $this->name));
        }
        return $this->module ?: null;
    }

    public function preUpgrade()
    {
        if ($this->name) {
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
        return true;
    }

    public function displayModulesToProcess(OutputInterface $output)
    {

        $output->getFormatter()->setStyle('u', new OutputFormatterStyle('yellow', null, []));
        $output->getFormatter()->setStyle('i', new OutputFormatterStyle('green', null, []));
        $output->getFormatter()->setStyle('r', new OutputFormatterStyle('cyan', null, []));
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('black', 'yellow', []));

        $output->writeln("Will <i>(i)</i>nstall, <u>(u)</u>pgrade or <r>(r)</r>eplace the following modules:");
        foreach ($this->depList as $module) {
            if ($module->needphase == '') {
                $module->needphase = 'upgrade';
            }
            $op = '<i>(i)</i>';
            if ($module->needphase == 'upgrade') {
                $op = '<u>(u)</u>';
            } else {
                if ($module->needphase == 'replaced') {
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

    public function askAllParameters() {
        /** @var \Module $module */
        $contentXml=[];
        foreach ($this->depList as $module) {
            $repo=$module->repository;
            $contentXml[]=$repo->getContentUrl();
        }

        $contentXml=array_filter($contentXml, function ($a) {
            return !empty($a);
        });
        foreach ($contentXml as $xmlUrl) {

            print_r(Context::download($xmlUrl));
        }

    }

    public function update()
    {

    }
}