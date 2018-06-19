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

    /**
     * Add callback to execute when hook is triggered
     * @param string   $hookName Hook name
     * @param callable $ft callback
     * @return $this
     */
    public function addListener(string $hookName, callable $ft)
    {
        $this->document->hooks[$hookName][] = $ft;
        return $this;
    }

    public function getListeners($hookName)
    {
        if (isset($this->document->hooks[$hookName])) {
            return $this->document->hooks[$hookName];
        }
        return [];
    }

    /**
     * Remove specific listeners
     * @param string $hookName hook type to remove
     * @return $this
     */
    public function removeListeners(string $hookName = null)
    {
        if ($hookName === null) {
            $this->document->hooks = [];
        } else {
            unset($this->document->hooks[$hookName]);
        }
        return $this;
    }

    /**
     * Remove all listeners
     * @return $this
     */
    public function resetListeners()
    {
        $this->document->hooks = null;
        return $this;
    }

    protected function initHooks()
    {
        $this->document->registerHooks();
    }

    /**
     * Call all registered callback for the hook name
     * @param string $hookName
     * @param mixed  ...$data
     * @return string
     */
    public function trigger(string $hookName, ...$data)
    {
        if (!isset($this->document->hooks)) {
            $this->document->hooks = [];
            $this->initHooks();
        }
        $outs = [];
        if (!empty($this->document->hooks[$hookName])) {
            $this->document->disableAccessControl();
            foreach ($this->document->hooks[$hookName] as $ft) {
                $outs[] = $ft(...$data);
            }
            $this->document->restoreAccessControl();
        }
        return implode("\n", $outs);
    }
}
