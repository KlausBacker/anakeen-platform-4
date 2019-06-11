<?php


namespace Anakeen\SmartStructures\Wdoc;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SmartStructure\BasicAttribute;

class Transition
{
    protected $rawConfig;
    /** @var WDocHooks */
    protected $workflow;
    protected $id;


    public function __construct(WDocHooks $wdoc, string $transitionName)
    {
        $this->rawConfig =& $wdoc->transitions[$transitionName];
        $this->workflow = $wdoc;
        $this->id = $transitionName;
    }

    /**
     * Return transition id
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->workflow->getTransitionLabel($this->id);
    }

    public function setM0(\Closure $call)
    {
        $this->rawConfig["m0"] = $call;
        return $this;
    }

    public function setM1(\Closure $call)
    {
        $this->rawConfig["m1"] = $call;
        return $this;
    }

    public function setM2(\Closure $call)
    {
        $this->rawConfig["m2"] = $call;
        return $this;
    }

    public function setM3(\Closure $call)
    {
        $this->rawConfig["m3"] = $call;
        return $this;
    }

    public function setAsks(\Closure $call)
    {
        $this->rawConfig["ask"] = $call;
        return $this;
    }

    public function getRequiredComment(): bool
    {
        return empty($this->rawConfig["nr"]);
    }
    public function setRequiredComment(bool $require)
    {
        $this->rawConfig["nr"] = !$require;
        return $this;
    }

    /**
     * @return BasicAttribute[]
     */
    public function getAsks()
    {
        if (!empty($this->rawConfig["ask"])) {
            if (is_callable($this->rawConfig["ask"])) {
                $askes = call_user_func($this->rawConfig["ask"]);
            } else {
                $askes = $this->rawConfig["ask"];
            }
            $oAskes = [];
            foreach ($askes as $ask) {
                if (is_a($ask, BasicAttribute::class)) {
                    $oa = $ask;
                } else {
                    $oa = $this->workflow->getAttribute($ask);
                }
                $oAskes[] = $oa;
            }
            return $oAskes;
        }
        return [];
    }
}
