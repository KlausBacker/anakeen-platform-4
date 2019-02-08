<?php

namespace Anakeen\Search;

class SearchElements
{
    const NOTRASH = "no";
    const ALSOTRASH = "also";
    const ONLYTRASH = "only";
    protected $searchData = null;

    /**
     * initialize with family
     *
     * @param int|string $structureName structure identifier to filter
     */
    public function __construct($structureName = 0)
    {
        $this->searchData = new \Anakeen\Search\Internal\SearchSmartData("", $structureName);
        $this->searchData->setObjectReturn(true);
    }

    /**
     * Count results without returning data.
     *
     * Note:
     * - The setStart() and setSlice() parameters are not used when counting with this method.
     *
     * @api send query search and only count results
     *
     * @return int the number of results
     */
    public function onlyCount()
    {
        $c = $this->searchData->onlyCount();
        if ($err = $this->searchData->getError()) {
            throw new Exception("SD0012", $err);
        }
        return $c;
    }

    /**
     * add join condition
     *
     * @api Add join condition
     * @code
     * $s=new searchDoc();
     * $s->trash='only';
     * $s->join("id = dochisto(id)");
     * $s->addFilter("dochisto.uid = %d",$this->getSystemUserId());
     * // search all document which has been deleted by search DELETE code in history
     * $s->addFilter("dochisto.code = 'DELETE'");
     * $s->distinct=true;
     * $result= $s->search();
     * @endcode
     * @param string $jointure
     * @return SearchElements
     */
    public function join($jointure)
    {
        $this->searchData->join($jointure);
        return $this;
    }

    /**
     * count results
     * ::search must be call before
     *
     * @see \Anakeen\Search\Internal\SearchSmartData::search()
     * @api count results after query search is sended
     *
     * @return int
     */
    public function count()
    {
        return $this->searchData->count();
    }

    /**
     * Verify if query is already sended to database
     *
     * @return boolean
     */
    public function isExecuted()
    {
        return $this->searchData->isExecuted();
    }

    /**
     * limit query to a subset of somes attributes
     * @param array $returns
     * @return SearchElements
     */
    public function returnsOnly(array $returns)
    {
        $this->searchData->returnsOnly($returns);
        return $this;
    }

    /**
     * set maximum number of document to return
     * @api set maximum number of document to return
     * @param int $slice the limit ('ALL' means no limit)
     *
     * @return $this
     */
    public function setSlice($slice)
    {
        if (!$this->searchData->setSlice($slice)) {
            throw new Exception("SD0009", $slice);
        }
        return $this;
    }

    /**
     * set offset where start the result window
     * @api set offset where start the result window
     * @param int $start the offset (0 is the begin)
     *
     * @return $this
     */
    public function setStart($start)
    {
        if (!$this->searchData->setStart($start)) {
            throw new Exception("SD0010", $start);
        }
        return $this;
    }

    /**
     * use different order , default is title
     *
     * @api set order to sort results
     *
     * @param string $order        the new order, empty means no order
     * @param string $orderbyLabel string of comma separated columns names on
     *                             which the order should be performed on their label instead of their value (e.g. order enum by their label instead of their key)
     *
     * @return void
     */
    public function setOrder($order, $orderbyLabel = '')
    {
        $this->searchData->setOrder($order, $orderbyLabel);
    }

    /**
     * use folder or search document to search within it
     * @api use folder or search document
     * @param int $dirid identifier of the collection
     *
     * @return $this
     */
    public function useCollection($dirid)
    {
        if (!$this->searchData->useCollection($dirid)) {
            throw new Exception("SD0011", $dirid);
        }
        return $this;
    }

    public function setLatest(bool $latest)
    {
        $this->searchData->latest = $latest;
        return $this;
    }

    public function setDistinct(bool $distinct)
    {
        $this->searchData->distinct = $distinct;
        return $this;
    }

    public function useTrash(string $trash)
    {
        $allowed = [self::NOTRASH, self::ONLYTRASH, self::ALSOTRASH];
        if (!in_array($trash, $allowed)) {
            throw new Exception("SD0012", $trash, implode($allowed, ", "));
        }
        $this->searchData->trash = $trash;
        return $this;
    }

    /**
     * Get the family on which the search is operating
     * @return \Anakeen\Core\SmartStructure
     */
    public function getFamily()
    {
        return $this->searchData->getFamily();
    }

    /**
     * add a filter to not return confidential document if current user cannot see it
     * @api add a filter to not return confidential
     * @param boolean $exclude set to true to exclude confidential
     * @return $this
     */
    public function excludeConfidential($exclude = true)
    {
        $this->searchData->excludeConfidential($exclude);
        return $this;
    }

    /**
     * add a condition in filters
     * @api add a new condition in filters
     * @param string $filter  the filter string
     * @param mixed  ...$args arguments of the filter string (arguments are escaped to avoid sql injection)
     * @return void
     * @throws Exception
     */
    public function addFilter($filter, ...$args)
    {
        $this->searchData->addFilter($filter, ...$args);
    }

    /**
     * no use access view control in filters
     * @api no add view access criteria in final query
     * @return $this
     */
    public function overrideAccessControl()
    {
        $this->searchData->overrideViewControl();
        return $this;
    }

    /**
     * send search
     * the query is sent to database
     * @api send query
     * @return $this
     */
    public function search()
    {
        $this->searchData->search();

        if ($err = $this->searchData->getError()) {
            throw new Exception("SD0013", $err);
        }
        return $this;
    }

    public function getResults()
    {
        return new ElementList($this);
    }

    /**
     * reset result offset
     * use it to redo a element list iteration
     *
     */
    public function rewind()
    {
        $this->searchData->rewind();
        return $this;
    }

    /**
     * return informations about query after search has been sent
     * array indexes are : query, err, count, delay
     * @api get informations about query results
     * @return array of info
     */
    public function getSearchInfo()
    {
        return $this->searchData->getSearchInfo();
    }


    /**
     * can, be use in loop
     * ::search must be call before
     *
     * @see SearchElements::search
     *
     * @api get next document results
     *
     * @return \Anakeen\Core\Internal\SmartElement |array|bool  false if this is the end
     */
    public function getNextElement()
    {
        return $this->searchData->getNextDoc();
    }
}
