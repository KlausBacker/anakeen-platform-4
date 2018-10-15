<?php


namespace Anakeen\Search;


use Anakeen\Core\Internal\SmartElement;

class ElementList implements \Iterator, \Countable
{
    /**
     * @var null|SearchElements
     */
    private $search = null;
    /**
     * @var null|SmartElement
     */
    private $currentDoc = null;
    /**
     * anonymous function
     * @var \Closure
     */
    private $hookFunction = null;

    private $init = false;
    public $length = 0;

    public function __construct(SearchElements & $s = null)
    {
        $this->search = &$s;
    }

    /**
     * get number of returned documents
     * can be upper of real length due to callback map
     *
     * @api Get number of returned documents
     * @return int
     */
    public function count()
    {
        $this->initSearch();
        return $this->length;
    }

    private function initSearch()
    {
        if ($this->search) {
            if (!$this->init) {
                if (!$this->search->isExecuted()) {
                    $this->search->search();
                }

                $this->length = $this->search->count();
                $this->init = true;
            } else {
                $this->search->rewind();
            }
        }
    }

    private function getCurrentDoc()
    {
        $this->currentDoc = $this->search->getNextElement();
        $good = ($this->callHook() !== false);
        if (!$good) {
            while ($this->currentDoc = $this->search->getNextElement()) {
                $good = ($this->callHook() !== false);
                if ($good) {
                    break;
                }
            }
        }
    }

    public function rewind()
    {
        $this->initSearch();
        $this->getCurrentDoc();
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->getCurrentDoc();
    }

    private function callHook()
    {
        if ($this->currentDoc && $this->hookFunction) {
            // call_user_func($function, $this->currentDoc);
            $h = $this->hookFunction;
            return $h($this->currentDoc);
        }
        return true;
    }

    public function key()
    {
        return is_array($this->currentDoc) ? $this->currentDoc["id"] : $this->currentDoc->id;
    }

    /**
     * @return \Anakeen\Core\Internal\SmartElement
     */
    public function current()
    {
        return $this->currentDoc;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->currentDoc != false;
    }

    /**
     * @return null|searchElements
     */
    public function &getSearchElement()
    {
        return $this->search;
    }

    /**
     * apply a callback on each document
     * if callback return false, the document is skipped from list
     * @param \Closure $hookFunction
     * @return void
     */
    public function listMap($hookFunction)
    {
        $this->hookFunction = $hookFunction;
    }
}