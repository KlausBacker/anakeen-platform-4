<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Document list class
 *
 * @author Anakeen
 * @version $Id:  $
 * @package FDL
 */
/**
 */
class DocumentList implements Iterator, Countable
{
    /**
     * @var null|SearchDoc
     */
    private $search = null;
    /**
     * @var null|Doc
     */
    private $currentDoc = null;
    /**
     * anonymous function
     * @var Closure
     */
    private $hookFunction = null;
    
    private $init = false;
    public $length = 0;
    
    public function __construct(SearchDoc & $s = null)
    {
        $this->search = & $s;
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
                if (!$this->search->isExecuted()) $this->search->search();
                if ($this->search->getError()) {
                    throw new Dcp\Exception($this->search->getError());
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
        $this->currentDoc = $this->search->getNextDoc();
        $good = ($this->callHook() !== false);
        if (!$good) {
            while ($this->currentDoc = $this->search->getNextDoc()) {
                $good = ($this->callHook() !== false);
                if ($good) break;
            }
        }
    }
    
    public function rewind()
    {
        $this->initSearch();
        $this->getCurrentDoc();
    }
    /**
     * @return Doc|null
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
     * @return Doc
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
     * @return null|SearchDoc
     */
    public function &getSearchDocument()
    {
        return $this->search;
    }
    /**
     * set document identifiers to be used in iterator
     * @param int[] $ids document identifiers
     * @param bool $useInitid if true identifier must ne initid else must be latest ids
     * @deprecated use addDocumentIdentifiers instead
     */
    public function addDocumentIdentificators(array $ids, $useInitid = true)
    {
        $this->addDocumentIdentifiers($ids, $useInitid);
    }
    /**
     * set document identifiers to be used in iterator
     *
     * @api Set document identifiers to be used in iterator
     * @param int[] $ids document identifiers
     * @param bool $useInitid if true identifier must ne initid else must be latest ids
     */
    public function addDocumentIdentifiers(array $ids, $useInitid = true)
    {
        $this->search = new SearchDoc(getDbAccess());
        $this->search->setObjectReturn();
        $this->search->excludeConfidential();
        foreach ($ids as $k => $v) {
            if ((!$v) || (!is_numeric($v))) unset($ids[$k]);
        }
        $ids = array_unique($ids);
        $sid = $useInitid ? "initid" : "id";
        if (count($ids) == 0) {
            $this->search->addFilter("false");
        } else {
            $this->search->addFilter($this->search->sqlCond($ids, $sid, true));
        }
    }
    /**
     * apply a callback on each document
     * if callback return false, the document is skipped from list
     * @param Closure $hookFunction
     * @return void
     */
    public function listMap($hookFunction)
    {
        $this->hookFunction = $hookFunction;
    }
}
