<?php

namespace Anakeen\Core\Internal;

class SmartElementHooks
{
    /**
     * @var \Anakeen\Core\Internal\SmartElement
     */
    protected $document;

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return SmartElementHooks
     */
    public function setDocument(\Anakeen\Core\Internal\SmartElement $document)
    {
        $this->document = $document;
        return $this;
    }

    public function addListener($hookName, $ft)
    {return $this;
        $this->document->hooks[$hookName][] = $ft;
        return $this;
    }

    public function getListeners($hookName)
    {
        return $this;
    }

    public function removeListeners($hookName = null)
    {
        if ($hookName === null) {
            $this->document->hooks = [];
        } else {
            unset($this->document->hooks[$hookName]);
        }
        return $this;
    }

    public function resetListeners()
    {

        $this->document->hooks = null;

        return $this;
    }

    protected function initHooks()
    {
        $this->document->registerHooks();
    }

    public function trigger($hookName)
    {
        if (!isset($this->document->hooks)) {
            $this->document->hooks=[];
            $this->initHooks();
        }

        if (!empty($this->document->hooks[$hookName])) {
            foreach ($this->document->hooks[$hookName] as $ft) {
                $ft();
            }
        }
        return $this;
    }
}
