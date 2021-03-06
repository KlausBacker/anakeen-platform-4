<?php

namespace Anakeen\Search;

use Anakeen\Search\Filters\ElementSearchFilter;

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
     * @return int the number of results
     * @api send query search and only count results
     *
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
     * @param string $jointure like "id = dochisto(id)"
     * @param string $joinType "inner" or "left outer"
     * @return SearchElements
     * @throws \Anakeen\Exception
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
     */
    public function join($jointure, $joinType = "inner")
    {
        $this->searchData->join($jointure, $joinType);
        return $this;
    }

    /**
     * count results
     * ::search must be call before
     *
     * @return int
     * @api count results after query search is sended
     *
     * @see \Anakeen\Search\Internal\SearchSmartData::search()
     */
    public function count()
    {
        if (!$this->searchData->isExecuted()) {
            $this->searchData->search();
        }
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
     * @param int $slice the limit ('ALL' means no limit)
     *
     * @return $this
     * @api set maximum number of document to return
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
     * @param int $start the offset (0 is the begin)
     *
     * @return $this
     * @api set offset where start the result window
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
     * @param string $order the new order, empty means no order
     * @param string $orderbyLabel string of comma separated columns names on
     *                             which the order should be performed on their label instead of their value (e.g. order enum by their label instead of their key)
     *
     * @return void
     * @api set order to sort results
     *
     */
    public function setOrder($order, $orderbyLabel = '')
    {
        $this->searchData->setOrder($order, $orderbyLabel);
    }

    /**
     * use folder or search document to search within it
     * @param int $dirid identifier of the collection
     *
     * @return $this
     * @api use folder or search document
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
            throw new Exception("SD0012", $trash, implode(", ", $allowed));
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
     * Get the main table search
     * Useful when use join
     * @return string
     */
    public function getMainTable()
    {
        return $this->searchData->getMainTable();
    }

    /**
     * add a filter to not return confidential document if current user cannot see it
     * @param boolean $exclude set to true to exclude confidential
     * @return $this
     * @api add a filter to not return confidential
     */
    public function excludeConfidential($exclude = true)
    {
        $this->searchData->excludeConfidential($exclude);
        return $this;
    }

    /**
     * add a condition in filters
     * @param ElementSearchFilter|string $filter the filter string (sql where condition) or filter object
     * @param mixed ...$args arguments of the filter string (arguments are escaped to avoid sql injection)
     * @return void
     * @throws Exception
     * @api add a new condition in filters
     */
    public function addFilter($filter, ...$args)
    {
        $this->searchData->addFilter($filter, ...$args);
    }

    /**
     * no use access view control in filters
     * @return $this
     * @api no add view access criteria in final query
     */
    public function overrideAccessControl()
    {
        $this->searchData->overrideViewControl();
        return $this;
    }

    /**
     * send search
     * the query is sent to database
     * @return $this
     * @api send query
     */
    public function search()
    {
        $this->searchData->search();

        if ($err = $this->searchData->getError()) {
            throw new Exception("SD0013", $err);
        }
        return $this;
    }

    /**
     * Return iterable Smart Element List
     *
     * @return ElementList the result of the search
     */
    public function getResults()
    {
        return new ElementList($this);
    }

    /**
     * To disable search into child structure
     * @param bool $excludeInheritedStructures set to true to search only elements on this structure
     * @return $this
     */
    public function excludeInheritedStructures(bool $excludeInheritedStructures)
    {
        $this->searchData->only = $excludeInheritedStructures;
        return $this;
    }

    /**
     * reset result offset
     * use it to redo a element list iteration
     * @return $this
     */
    public function rewind()
    {
        $this->searchData->rewind();
        return $this;
    }

    /**
     * return informations about query after search has been sent
     * array indexes are : query, err, count, delay
     * @return array of info
     * @api get informations about query results
     */
    public function getSearchInfo()
    {
        return $this->searchData->getSearchInfo();
    }


    /**
     * can, be use in loop
     * ::search must be call before
     *
     * @return \Anakeen\Core\Internal\SmartElement |array|bool  false if this is the end
     * @api get next document results
     *
     * @see SearchElements::search
     *
     */
    public function getNextElement()
    {
        return $this->searchData->getNextDoc();
    }

    /**
     * return where condition like : foo in ('x','y','z')
     *
     * @static
     *
     * @param array $values set of values
     * @param string $column database column name
     * @param bool $integer set to true if database column is numeric type
     *
     * @return string
     */
    public static function sqlcond(array $values, $column, $integer = false)
    {
        $sql_cond = "true";
        if (count($values) > 0) {
            if ($integer) { // for integer type
                $sql_cond = "$column in (";
                $sql_cond .= implode(",", $values);
                $sql_cond .= ")";
            } else { // for text type
                foreach ($values as & $v) {
                    $v = pg_escape_string($v);
                }
                $sql_cond = "$column in ('";
                $sql_cond .= implode("','", $values);
                $sql_cond .= "')";
            }
        }

        return $sql_cond;
    }
}
