<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Search Document
 *
 * @author  Anakeen
 * @version $Id: Class.SearchDoc.php,v 1.8 2008/08/14 14:20:25 eric Exp $
 * @package FDL
 */

/**
 */

use \Anakeen\SmartStructures\Dir\DirLib;
use \Anakeen\SmartStructures\Dir\DirHooks;

/**
 * document searches
 * @code
 * $s=new SearchDoc($db,"IUSER");
 * $s->setObjectReturn(); // document object returns
 * $s->addFilter('us_extmail is not null'); // simple filter
 * $s->search(); // send search query
 * $c=$s->count();
 * print "count $c\n";
 * $k=0;
 * while ($doc=$s->nextDoc()) {
 * // iterate document by document
 * print "$k)".$doc->getTitle()."(".$doc->getRawValue("US_MAIL","nomail").")\n";clam
 * $k+
 * @endcode
 * @class SearchDoc.
 */
class SearchDoc
{
    /**
     * family identifier filter
     * @public string
     */
    public $fromid;
    /**
     * folder identifier filter
     * @public int
     */
    public $dirid = 0;
    /**
     * recursive search for folders
     * @public boolean
     */
    public $recursiveSearch = false;
    /**
     * max recursive level
     * @public int
     */
    public $folderRecursiveLevel = 2;
    /**
     * number of results : set "ALL" if no limit
     * @public int
     */
    public $slice = "ALL";
    /**
     * index of results begins
     * @public int
     */
    public $start = 0;
    /**
     * sql filters
     * @public array
     */
    public $filters = array();
    /**
     * search in sub-families set false if restriction to top family
     * @public bool
     */
    public $only = false;
    /**
     *
     * @public bool
     */
    public $distinct = false;
    /**
     * order of result : like sql order
     * @public string
     */
    public $orderby = 'title';
    /**
     * order result by this attribute label/title
     * @public string
     */
    public $orderbyLabel = '';
    /**
     * to search in trash : [no|also|only]
     * @public string
     */
    public $trash = "";
    /**
     * restriction to latest revision
     * @public bool
     */
    public $latest = true;
    /**
     * user identifier : set to current user by default
     * @public int
     */
    public $userid = 0;
    protected $dbaccess;

    private $debuginfo = [];
    private $join = "";
    /**
     * sql filter not return confidential document if current user cannot see it
     * @var string
     */
    private $excludeFilter = "";
    /**
     *
     * Iterator document
     * @var \Anakeen\Core\Internal\SmartElement
     */
    private $iDoc = null;
    /**
     *
     * Iterator document
     * @var \Anakeen\Core\Internal\SmartElement []
     */
    private $cacheDocuments = array();
    /**
     * result type [ITEM|TABLE]
     * @private string
     */
    private $mode = "TABLE";
    private $count = -1;
    private $index = 0;
    /**
     * @var bool|array
     */
    private $result = false;
    private $searchmode;


    private $resultPos = 0;
    /**
     * @var int query number (in ITEM mode)
     */

    private $resultQPos = 0;
    protected $originalDirId = 0;

    protected $returnsFields = array();

    /**
     * initialize with family
     *
     * @param string     $dbaccess database coordinate
     * @param int|string $fromid   family identifier to filter
     */
    public function __construct($dbaccess = '', $fromid = 0)
    {
        if ($dbaccess == "") {
            $dbaccess = \Anakeen\Core\DbManager::getDbAccess();
        }
        $this->dbaccess = $dbaccess;
        $this->fromid = trim($fromid);
        $this->setOrder('title');
        $this->userid = \Anakeen\Core\ContextManager::getCurrentUser()->id;
    }

    /**
     * Normalize supported forms of fromid
     *
     * @param int|string $id the fromid to normalize
     * @return bool|int normalized integer or bool(false) on normalization failure
     */
    private function normalizeFromId($id)
    {
        $id = trim($id);
        // "0" or "" (empty srting) = search on all documents (cross-family)
        if ($id === "0" || $id === "") {
            return 0;
        }
        // "-1" = search on docfam
        if ($id === "-1") {
            return -1;
        }
        if (is_numeric($id)) {
            // 123 or -123 = search on family with id 123
            $sign = 1;
            if ($id < 0) {
                // -123 = search on family with id 123 without sub-families
                $sign = -1;
                $id = abs($id);
            }
            $fam = Anakeen\Core\SEManager::getFamily($id);
            if ($fam && $fam->isAlive()) {
                return $sign * (int)$fam->id;
            }
        } else {
            // "ABC" or "-ABC" = search on family with name ABC
            $sign = 1;
            if (substr($id, 0, 1) == '-') {
                // "-ABC" = search on family with name 123 without sub-families
                $sign = -1;
                $id = substr($id, 1);
            }
            $fam = Anakeen\Core\SEManager::getFamily($id);
            if ($fam && $fam->isAlive()) {
                return $sign * (int)$fam->id;
            }
        }
        return false;
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
     * @throws Dcp\SearchDoc\Exception
     * @throws Dcp\Db\Exception
     */
    public function onlyCount()
    {
        /**  @var DirHooks $fld */
        $fld = Anakeen\Core\SEManager::getDocument($this->dirid);
        $userid = $this->userid;
        if (!$fld || $fld->fromid != \Anakeen\Core\SEManager::getFamilyIdFromName("SSEARCH")) {
            $this->recursiveSearchInit();
            $tqsql = $this->getQueries();
            $this->debuginfo["query"] = $tqsql[0];
            $count = 0;
            if (!is_array($tqsql)) {
                if (!isset($this->debuginfo["error"]) || $this->debuginfo["error"] == "") {
                    $this->debuginfo["error"] = _("cannot produce sql request");
                }
                return -1;
            }
            foreach ($tqsql as $sql) {
                if ($sql) {
                    if (preg_match('/from\s+(?:only\s+)?([a-z0-9_\-]*)/', $sql, $reg)) {
                        $maintable = $reg[1];
                    } else {
                        $maintable = '';
                    }
                    $maintabledot = ($maintable) ? $maintable . '.' : '';

                    $mainid = ($maintable) ? "$maintable.id" : "id";
                    $distinct = "";
                    if (preg_match('/^\s*select\s+distinct(\s+|\(.*?\))/iu', $sql, $m)) {
                        $distinct = "distinct ";
                    }
                    $sql = preg_replace('/^\s*select\s+(.*?)\s+from\s/iu', "select count($distinct$mainid) from ", $sql, 1);
                    if ($userid != 1) {
                        $sql .= sprintf(" and (%sviews && '%s')", $maintabledot, $this->getUserViewVector($userid));
                    }

                    $dbid = \Anakeen\Core\DbManager::getDbId();
                    $mb = microtime(true);
                    try {
                        \Anakeen\Core\DbManager::query($sql, $result, false, true);
                    } catch (\Dcp\Db\Exception $e) {
                        $this->debuginfo["query"] = $sql;
                        $this->debuginfo["error"] = pg_last_error($dbid);
                        $this->count = -1;
                        throw $e;
                    }
                    $count += $result["count"];
                    $this->debuginfo["query"] = $sql;
                    $this->debuginfo["delay"] = sprintf("%.03fs", microtime(true) - $mb);
                }
            }
            $this->count = $count;
            return $count;
        } else {
            $this->count = count($fld->getContent());
        }

        return $this->count;
    }

    /**
     * return memberof to be used in profile filters
     * @static
     * @param $uid
     * @return string
     */
    public static function getUserViewVector($uid)
    {
        $memberOf = \Anakeen\Core\Account::getUserMemberOf($uid);
        if ($memberOf === null) {
            return '';
        }

        $memberOf[] = $uid;
        return '{' . implode(',', $memberOf) . '}';
    }

    /**
     * return original sql query before test permissions
     *
     *
     * @return string
     */
    public function getOriginalQuery()
    {
        return DirLib::_internalGetDocCollection(
            true,
            $this->dbaccess,
            $this->dirid,
            $this->start,
            $this->slice,
            $this->getFilters(),
            $this->userid,
            $this->searchmode,
            $this->fromid,
            $this->distinct,
            $this->orderby,
            $this->latest,
            $this->trash,
            $debuginfo,
            $this->folderRecursiveLevel,
            $this->join,
            $this
        );
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
     * @throws \Anakeen\Exception
     */
    public function join($jointure)
    {
        if (empty($jointure)) {
            $this->join = '';
        } elseif (preg_match('/([a-z0-9_\-:]+)\s*(=|<|>|<=|>=)\s*([a-z0-9_\-:]+)\(([^\)]*)\)/', $jointure, $reg)) {
            $this->join = $jointure;
        } else {
            throw new \Dcp\SearchDoc\Exception("SD0001", $jointure);
        }
    }

    /**
     * count results
     * ::search must be call before
     * @see SearchDoc::search()
     * @api count results after query search is sended
     *
     * @return int
     *
     */
    public function count()
    {
        if ($this->isExecuted()) {
            if ($this->count == -1) {
                if ($this->searchmode == "ITEM") {
                    $this->count = $this->countDocs();
                } else {
                    $this->count = count($this->result);
                }
            }
        }
        return $this->count;
    }

    /**
     * count returned document in sql select ressources
     * @return int
     */
    protected function countDocs()
    {
        $n = 0;
        foreach ($this->result as $res) {
            $n += pg_num_rows($res);
        }
        reset($this->result);
        return $n;
    }

    /**
     * reset results to use another search
     *
     *
     * @return void
     */
    public function reset()
    {
        $this->result = false;
        $this->resultPos = 0;
        $this->resultQPos = 0;
        $this->debuginfo = [];
        $this->count = -1;
    }

    /**
     * reset result offset
     * use it to redo a document's iteration
     *
     */
    public function rewind()
    {
        $this->resultPos = 0;
        $this->resultQPos = 0;
    }

    /**
     * Verify if query is already sended to database
     *
     * @return boolean
     */
    public function isExecuted()
    {
        return ($this->result !== false);
    }

    /**
     * Return sql filters used for request
     *
     * @return array of string
     */
    public function getFilters()
    {
        if (!$this->excludeFilter) {
            return $this->filters;
        } else {
            return array_merge(array(
                $this->excludeFilter
            ), $this->filters);
        }
    }

    /**
     * send search
     * the query is sent to database
     * @api send query
     * @return array|null|SearchDoc array of documents if no setObjectReturn else itself
     * @throws Dcp\SearchDoc\Exception
     * @throws Dcp\Db\Exception
     */
    public function search()
    {
        if (count($this->filters) > 0 && $this->dirid > 0) {
            $dir = Anakeen\Core\SEManager::getDocument($this->dirid);
            if ($dir && $dir->isAlive() && is_a($dir, \SmartStructure\Ssearch::class)) {
                // Searching on a "Specialized search" collection and specifying additional filters is not supported
                throw new \Dcp\SearchDoc\Exception("SD0008");
            }
        }
        if ($this->getError()) {
            if ($this->mode == "ITEM") {
                return null;
            } else {
                return array();
            }
        }
        if ($this->fromid) {
            if (!is_numeric($this->fromid)) {
                $fromid = \Anakeen\Core\SEManager::getFamilyIdFromName($this->fromid);
            } else {
                if ($this->fromid != -1) {
                    // test if it is a family
                    if ($this->fromid < -1) {
                        $this->only = true;
                    }
                    \Anakeen\Core\DbManager::query(sprintf("select doctype from docfam where id=%d", abs($this->fromid)), $doctype, true, true);
                    if ($doctype != 'C') {
                        $fromid = 0;
                    } else {
                        $fromid = $this->fromid;
                    }
                } else {
                    $fromid = $this->fromid;
                }
            }
            if ($fromid == 0) {
                $error = sprintf(___("\"%s\" is not a structure", "search"), $this->fromid);
                $this->debuginfo["error"] = $error;
                error_log("ERROR SearchDoc: " . $error);
                if ($this->mode == "ITEM") {
                    return null;
                } else {
                    return array();
                }
            }
            if ($this->only) {
                $this->fromid = -(abs($fromid));
            } else {
                $this->fromid = $fromid;
            }
        }
        $this->recursiveSearchInit();
        $this->index = 0;
        $this->searchmode = $this->mode;
        if ($this->mode == "ITEM") {
            if ($this->dirid) {
                // change search mode because ITEM mode not supported for Specailized searches
                $fld = Anakeen\Core\SEManager::getDocument($this->dirid);
                if ($fld->fromid == \Anakeen\Core\SEManager::getFamilyIdFromName("SSEARCH")) {
                    $this->searchmode = "TABLE";
                }
            }
        }
        $debuginfo = array();
        $this->count = -1;
        $this->result = DirLib::internalGetDocCollection(
            $this->dbaccess,
            $this->dirid,
            $this->start,
            $this->slice,
            $this->getFilters(),
            $this->userid,
            $this->searchmode,
            $this->fromid,
            $this->distinct,
            $this->orderby,
            $this->latest,
            $this->trash,
            $debuginfo,
            $this->folderRecursiveLevel,
            $this->join,
            $this
        );
        if ($this->searchmode == "TABLE") {
            $this->count = count($this->result);
        } // memo cause array is unset by shift
        $this->debuginfo = $debuginfo;
        if (($this->searchmode == "TABLE") && ($this->mode == "ITEM")) {
            $this->mode = "TABLEITEM";
        }
        $this->resultPos = 0;
        $this->resultQPos = 0;
        if ($this->mode == "ITEM") {
            return $this;
        }

        return $this->result;
    }

    /**
     * return document iterator to be used in loop
     * @code
     *  $s=new \SearchDoc($dbaccess, $famName);
     * $s->setObjectReturn();
     * $s->search();
     * $dl=$s->getDocumentList();
     * foreach ($dl as $docId=>$doc) {
     * print $doc->getTitle();
     * }
     * @endcode
     * @api get document iterator
     * @return DocumentList
     */
    public function getDocumentList()
    {
        return new DocumentList($this);
    }

    /**
     * limit query to a subset of somes attributes
     * @param array $returns
     */
    public function returnsOnly(array $returns)
    {
        if ($this->fromid) {
            $fdoc = \Anakeen\Core\SEManager::createTemporaryDocument($this->fromid, false);
            $fields = array_merge($fdoc->fields, $fdoc->sup_fields);
        } else {
            $fdoc = new \Anakeen\Core\Internal\SmartElement();
            $fields = array_merge($fdoc->fields, $fdoc->sup_fields);
        }
        foreach ($returns as $k => $r) {
            if (empty($r)) {
                unset($returns[$k]);
            }
            $returns[$k] = strtolower($r);
            // delete unknow fields
            if (!in_array($r, $fields)) {
                unset($returns[$k]);
            }
        }
        $this->returnsFields = array_unique(array_merge(array(
            "id",
            "title",
            "fromid",
            "doctype"
        ), $returns));
    }

    public function getReturnsFields()
    {
        if ($this->returnsFields) {
            return $this->returnsFields;
        }
        if ($this->fromid) {
            $fdoc = Anakeen\Core\SEManager::createTemporaryDocument($this->fromid, false);
            if ($fdoc->isAlive()) {
                return array_merge($fdoc->fields, $fdoc->sup_fields);
            }
        }
        return null;
    }

    /**
     * return error message
     * @return string empty if no errors
     */
    public function searchError()
    {
        return $this->getError();
    }

    /**
     * Return error message
     * @api get error message
     * @return string
     */
    public function getError()
    {
        if ($this->debuginfo && isset($this->debuginfo["error"])) {
            return $this->debuginfo["error"];
        }
        return "";
    }



    /**
     * set recursive mode for folder searches
     * can be use only if collection set if a static folder
     * @param bool $recursiveMode set to true to use search in sub folders when collection is folder
     * @param int  $level         Indicate depth to inspect subfolders
     * @throws Dcp\SearchDoc\Exception
     * @api set recursive mode for folder searches
     * @see SearchDoc::useCollection
     * @return void
     */
    public function setRecursiveSearch($recursiveMode = true, $level = 2)
    {
        $this->recursiveSearch = $recursiveMode;
        if (!is_int($level) || $level < 0) {
            throw new \Dcp\SearchDoc\Exception("SD0006", $level);
        }
        $this->folderRecursiveLevel = $level;
    }



    /**
     * return informations about query after search has been sent
     * array indexes are : query, err, count, delay
     * @api get informations about query results
     * @return array of info
     */
    public function getSearchInfo()
    {
        return $this->debuginfo;
    }

    /**
     * set maximum number of document to return
     * @api set maximum number of document to return
     * @param int $slice the limit ('ALL' means no limit)
     *
     * @return Boolean
     */
    public function setSlice($slice)
    {
        if ((!is_numeric($slice)) && ($slice != 'ALL')) {
            return false;
        }
        $this->slice = $slice;
        return true;
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
        $this->orderby = $order;
        $this->orderbyLabel = $orderbyLabel;
        /* Rewrite "-<column_name>" to "<column_name> desc" */
        $this->orderby = preg_replace('/(^\s*|,\s*)-([A-Z_0-9]{1,63})\b/i', '$1$2 desc', $this->orderby);
    }

    /**
     * use folder or search document to search within it
     * @api use folder or search document
     * @param int $dirid identifier of the collection
     *
     * @return Boolean true if set
     */
    public function useCollection($dirid)
    {
        $dir = Anakeen\Core\SEManager::getDocument($dirid);
        if ($dir && $dir->isAlive()) {
            $this->dirid = $dir->initid;
            $this->originalDirId = $this->dirid;
            return true;
        }
        $this->debuginfo["error"] = sprintf(_("collection %s not exists"), $dirid);

        return false;
    }

    /**
     * set offset where start the result window
     * @api set offset where start the result window
     * @param int $start the offset (0 is the begin)
     *
     * @return Boolean true if set
     */
    public function setStart($start)
    {
        if (!(is_numeric($start))) {
            return false;
        }
        $this->start = intval($start);
        return true;
    }



    /**
     * can, be use in loop
     * ::search must be call before
     *
     * @see SearchDoc::search
     *
     * @api get next document results
     *
     * @return \Anakeen\Core\Internal\SmartElement |array|bool  false if this is the end
     */
    public function getNextDoc()
    {
        if ($this->mode == "ITEM") {
            $n = empty($this->result[$this->resultQPos]) ? null : $this->result[$this->resultQPos];
            if (!$n) {
                return false;
            }
            $tdoc = @pg_fetch_array($n, $this->resultPos, PGSQL_ASSOC);
            if ($tdoc === false) {
                $this->resultQPos++;
                $n = empty($this->result[$this->resultQPos]) ? null : $this->result[$this->resultQPos];
                if (!$n) {
                    return false;
                }
                $this->resultPos = 0;
                $tdoc = @pg_fetch_array($n, $this->resultPos, PGSQL_ASSOC);
                if ($tdoc === false) {
                    return false;
                }
            }
            $this->resultPos++;
            return $this->iDoc = $this->getNextDocument($tdoc);
        } elseif ($this->mode == "TABLEITEM") {
            $tdoc = current(array_slice($this->result, $this->resultPos, 1));
            if (!is_array($tdoc)) {
                return false;
            }
            $this->resultPos++;
            return $this->iDoc = $this->getNextDocument($tdoc);
        } else {
            return current(array_slice($this->result, $this->resultPos++, 1));
        }
    }

    /**
     * after search return only document identifiers instead of complete document
     * @api get only document identifiers
     * @return int[] document identifiers
     */
    public function getIds()
    {
        $ids = array();
        if ($this->mode == "ITEM") {
            foreach ($this->result as $n) {
                $c = pg_num_rows($n);
                for ($i = 0; $i < $c; $i++) {
                    $ids[] = pg_fetch_result($n, $i, "id");
                }
            }
        } else {
            foreach ($this->result as $raw) {
                $ids[] = $raw["id"];
            }
        }
        return $ids;
    }

    /**
     * Return an object document from array of values
     *
     * @param array $v the values of documents
     * @return \Anakeen\Core\Internal\SmartElement the document object
     */
    protected function getNextDocument(array $v)
    {
        $fromid = $v["fromid"];
        if ($v["doctype"] == "C") {
            if (!isset($this->cacheDocuments["family"])) {
                $this->cacheDocuments["family"] = new \Anakeen\Core\SmartStructure($this->dbaccess);
            }
            $this->cacheDocuments["family"]->Affect($v, true);
            $fromid = "family";
        } else {
            if (!isset($this->cacheDocuments[$fromid])) {
                $this->cacheDocuments[$fromid] = Anakeen\Core\SEManager::createDocument($fromid, false);
                if (empty($this->cacheDocuments[$fromid])) {
                    throw new \Dcp\SearchDoc\Exception(sprintf('Document "%s" has an unknow family "%s"', $v["id"], $fromid));
                }
            }
        }

        $this->cacheDocuments[$fromid]->Affect($v, true);
        $this->cacheDocuments[$fromid]->nocache = true;
        if ((!empty($this->returnsFields))) {
            $this->cacheDocuments[$fromid]->doctype = "I";
        } // incomplete document
        return $this->cacheDocuments[$fromid];
    }

    /**
     * add a condition in filters
     * @api add a new condition in filters
     * @param string $filter the filter string
     * @param string $args   arguments of the filter string (arguments are escaped to avoid sql injection)
     * @return void
     */
    public function addFilter($filter, $args = '')
    {
        if (is_string($filter)) {
            if ($filter != "") {
                $args = func_get_args();
                if (count($args) > 1) {
                    $fs[0] = $args[0];
                    for ($i = 1; $i < count($args); $i++) {
                        $fs[] = pg_escape_string($args[$i]);
                    }
                    $filter = call_user_func_array("sprintf", $fs);
                }
                if (preg_match('/(\s|^|\()(?P<relname>[a-z0-9_\-]+)\./', $filter, $reg)) {
                    // when use join filter like "zoo_espece.es_classe='Boo'"
                    $famid = \Anakeen\Core\SEManager::getFamilyIdFromName($reg['relname']);
                    if ($famid > 0) {
                        $filter = preg_replace('/(\s|^|\()(?P<relname>[a-z0-9_\-]+)\./', '${1}doc' . $famid . '.', $filter);
                    }
                }
                $this->filters[] = $filter;
            }
        } elseif (is_object($filter)) {
            if (!is_a($filter, \Anakeen\Search\Filters\ElementSearchFilter::class)) {
                throw new \Dcp\SearchDoc\Exception(sprintf("Filter object does not implements \"%s\" interface.", \Anakeen\Search\Filters\ElementSearchFilter::class));
            }
            /**
             * @var \Anakeen\Search\Filters\ElementSearchFilter $filter
             */
            $filter->addFilter($this);
        } else {
            throw new \Dcp\SearchDoc\Exception(sprintf("Filter is neither a string nor an objet implementing \\Dcp\\Documentfilter interface."));
        }
    }


    /**
     * return where condition like : foo in ('x','y','z')
     *
     * @static
     * @param array  $values  set of values
     * @param string $column  database column name
     * @param bool   $integer set to true if database column is numeric type
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



    /**
     * no use access view control in filters
     * @api no add view access criteria in final query
     * @return void
     */
    public function overrideViewControl()
    {
        $this->userid = 1;
    }

    /**
     * the return of ::search will be array of document's object
     *
     * @api set return type : document object or document array
     * @param bool $returnobject set to true to return object, false to return raw data
     * @return void
     */
    public function setObjectReturn($returnobject = true)
    {
        if ($returnobject) {
            $this->mode = "ITEM";
        } else {
            $this->mode = "TABLE";
        }
    }

    public function isObjectReturn()
    {
        return ($this->mode == "ITEM");
    }

    /**
     * add a filter to not return confidential document if current user cannot see it
     * @api add a filter to not return confidential
     * @param boolean $exclude set to true to exclude confidential
     * @return void
     */
    public function excludeConfidential($exclude = true)
    {
        if ($exclude) {
            if ($this->userid != 1) {
                $this->excludeFilter = sprintf(
                    "confidential is null or hasaprivilege('%s', profid,%d)",
                    DocPerm::getMemberOfVector($this->userid),
                    1 << \Anakeen\Core\Internal\DocumentAccess::POS_CONF
                );
            }
        } else {
            $this->excludeFilter = '';
        }
    }

    protected function recursiveSearchInit()
    {
        if ($this->recursiveSearch && $this->dirid) {
            if (!$this->originalDirId) {
                $this->originalDirId = $this->dirid;
            }
            /**
             * @var \Anakeen\SmartStructures\Search\SearchHooks $tmps
             */
            $tmps = Anakeen\Core\SEManager::createTemporaryDocument("SEARCH");
            $tmps->setValue(\SmartStructure\Fields\Search::se_famid, $this->fromid);
            $tmps->setValue(\SmartStructure\Fields\Search::se_idfld, $this->originalDirId);
            $tmps->setValue(\SmartStructure\Fields\Search::se_latest, "yes");
            $err = $tmps->add();
            if ($err == "") {
                $tmps->addQuery($tmps->getQuery()); // compute internal sql query
                $this->dirid = $tmps->id;
            } else {
                throw new \Dcp\SearchDoc\Exception("SD0005", $err);
            }
        }
    }

    /**
     * Get the SQL queries that will be executed by the search() method
     * @return array|bool boolean false on error, or array() of queries on success.
     */
    public function getQueries()
    {
        $dbaccess = $this->dbaccess;
        $dirid = $this->dirid;
        $fromid = $this->fromid;
        $sqlfilters = $this->getFilters();
        $distinct = $this->distinct;
        $latest = $this->latest;
        $trash = $this->trash;
        $folderRecursiveLevel = $this->folderRecursiveLevel;
        $join = $this->join;

        $normFromId = $this->normalizeFromId($fromid);
        if ($normFromId === false) {
            $this->debuginfo["error"] = sprintf(_("%s is not a family"), $fromid);
            return false;
        }
        $fromid = $normFromId;
        if (($fromid != "") && (!is_numeric($fromid))) {
            preg_match('/^(?P<sign>-?)(?P<fromid>.+)$/', trim($fromid), $m);
            $fromid = $m['sign'] . \Anakeen\Core\SEManager::getFamilyIdFromName($m['fromid']);
        }
        if ($this->only && strpos($fromid, '-') !== 0) {
            $fromid = '-' . $fromid;
        }
        $table = "doc";
        $only = "";

        if ($fromid == -1) {
            $table = "docfam";
        } elseif ($fromid < 0) {
            $only = "only";
            $fromid = -$fromid;
            $table = "doc$fromid";
        } else {
            if ($fromid != 0) {
                if (DirLib::isSimpleFilter($sqlfilters) && (DirLib::familyNeedDocread($dbaccess, $fromid))) {
                    $table = "docread";

                    $fdoc = Anakeen\Core\SEManager::getFamily($fromid);
                    $sqlfilters[-4] = \Anakeen\Core\DbManager::getSqlOrCond(array_merge(array(
                        $fromid
                    ), array_keys($fdoc->GetChildFam())), "fromid", true);
                } else {
                    $table = "doc$fromid";
                }
            } elseif ($fromid == 0) {
                if (DirLib::isSimpleFilter($sqlfilters)) {
                    $table = "docread";
                }
            }
        }
        $maintable = $table; // can use join only on search
        if ($join) {
            if (preg_match('/(?P<attr>[a-z0-9_\-:]+)\s*(?P<operator>=|<|>|<=|>=)\s*(?P<family>[a-z0-9_\-:]+)\((?P<family_attr>[^\)]*)\)/', $join, $reg)) {
                $joinid = \Anakeen\Core\SEManager::getFamilyIdFromName($reg['family']);
                $jointable = ($joinid) ? "doc" . $joinid : $reg['family'];

                $sqlfilters[] = sprintf("%s.%s %s %s.%s", $table, $reg['attr'], $reg['operator'], $jointable, $reg['family_attr']); // "id = dochisto(id)";
                $maintable = $table;
                $table .= ", " . $jointable;
            } else {
                \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("search join syntax error : %s"), $join));
                return false;
            }
        }
        $maintabledot = ($maintable && $dirid == 0) ? $maintable . '.' : '';

        if ($distinct) {
            $selectfields = "distinct on ($maintable.initid) $maintable.*";
        } else {
            $selectfields = "$maintable.*";
            $sqlfilters[-2] = $maintabledot . "doctype != 'T'";
            ksort($sqlfilters);
        }
        $sqlcond = "true";
        ksort($sqlfilters);
        if (count($sqlfilters) > 0) {
            $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
        }

        $qsql = '';
        if ($dirid == 0) {
            //-------------------------------------------
            // search in all Db
            //-------------------------------------------

            if ($trash === "only") {
                $sqlfilters[-3] = $maintabledot . "doctype = 'Z'";
            } elseif ($trash !== "also") {
                $sqlfilters[-3] = $maintabledot . "doctype != 'Z'";
            }

            if (($latest) && (($trash == "no") || (!$trash))) {
                $sqlfilters[-1] = $maintabledot . "locked != -1";
            }
            ksort($sqlfilters);
            if (count($sqlfilters) > 0) {
                $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
            }
            $qsql = "select $selectfields " . "from $only $table  " . "where  " . $sqlcond;
            $qsql = $this->injectFromClauseForOrderByLabel($fromid, $this->orderbyLabel, $qsql);
        } else {
            //-------------------------------------------
            // in a specific folder
            //-------------------------------------------

            $fld = Anakeen\Core\SEManager::getDocument($dirid);
            if ($fld->defDoctype != 'S') {
                /**
                 * @var DirHooks $fld
                 */
                $hasFilters = false;
                if ($fld && method_exists($fld, "getSpecificFilters")) {
                    $specFilters = $fld->getSpecificFilters();
                    if (is_array($specFilters) && (count($specFilters) > 0)) {
                        $sqlfilters = array_merge($sqlfilters, $specFilters);
                        $hasFilters = true;
                    }
                }

                //if ($fld->getRawValue("se_trash")!="yes") $sqlfilters[-3] = "doctype != 'Z'";
                if ($trash == "only") {
                    $sqlfilters[-1] = "locked = -1";
                } elseif ($latest) {
                    $sqlfilters[-1] = "locked != -1";
                }
                ksort($sqlfilters);
                if (count($sqlfilters) > 0) {
                    $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
                }

                $sqlfld = "dirid=$dirid and qtype='S'";
                if ($fromid == 2) {
                    $sqlfld .= " and doctype='D'";
                }
                if ($fromid == 5) {
                    $sqlfld .= " and doctype='S'";
                }
                if ($hasFilters) {
                    $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
                    $qsql = "select $selectfields from $only $table where $sqlcond ";
                } else {
                    $q = new \Anakeen\Core\Internal\QueryDb($dbaccess, \QueryDir::class);
                    $q->AddQuery($sqlfld);
                    $tfld = $q->Query(0, 0, "TABLE");
                    if ($q->nb > 0) {
                        $tfldid = array();
                        foreach ($tfld as $onefld) {
                            $tfldid[] = $onefld["childid"];
                        }
                        if (count($tfldid) > 1000) {
                            $qsql = "select $selectfields " . "from $table where initid in (select childid from fld where $sqlfld)  " . "and  $sqlcond ";
                        } else {
                            $sfldids = implode(",", $tfldid);
                            if ($table == "docread") {
                                /*$qsql= "select $selectfields ".
                                                 "from $table where initid in (select childid from fld where $sqlfld)  ".
                                                 "and  $sqlcond ";	*/
                                $qsql = "select $selectfields " . "from $table where initid in ($sfldids)  " . "and  $sqlcond ";
                            } else {
                                /*$qsql= "select $selectfields ".
                                    "from (select childid from fld where $sqlfld) as fld2 inner join $table on (initid=childid)  ".
                                    "where  $sqlcond ";*/
                                $qsql = "select $selectfields " . "from $only $table where initid in ($sfldids)  " . "and  $sqlcond ";
                            }
                        }
                    }
                }
            } else {
                //-------------------------------------------
                // search familly
                //-------------------------------------------
                $docsearch = new \Anakeen\Core\Internal\QueryDb($dbaccess, \QueryDir::class);
                $docsearch->AddQuery("dirid=$dirid");
                $docsearch->AddQuery("qtype = 'M'");
                $ldocsearch = $docsearch->Query(0, 0, "TABLE");
                // for the moment only one query search
                if (($docsearch->nb) > 0) {
                    switch ($ldocsearch[0]["qtype"]) {
                        case "M": // complex query
                            // $sqlM=$ldocsearch[0]["query"];
                            $fld = Anakeen\Core\SEManager::getDocument($dirid);
                            /**
                             * @var \Anakeen\SmartStructures\Search\SearchHooks $fld
                             */
                            if ($trash) {
                                $fld->setValue("se_trash", $trash);
                            } else {
                                $trash = $fld->getRawValue("se_trash");
                            }
                            $fld->folderRecursiveLevel = $folderRecursiveLevel;
                            $tsqlM = $fld->getQuery();
                            $qsql = [];
                            foreach ($tsqlM as $sqlM) {
                                if ($sqlM != false) {
                                    if (!preg_match("/doctype[ ]*=[ ]*'Z'/", $sqlM, $reg)) {
                                        if (($trash != "also") && ($trash != "only")) {
                                            $sqlfilters[-3] = "doctype != 'Z'"; // no zombie if no trash
                                        }
                                        ksort($sqlfilters);
                                        foreach ($sqlfilters as $kf => $sf) { // suppress doubles
                                            if (strstr($sqlM, $sf)) {
                                                unset($sqlfilters[$kf]);
                                            }
                                        }
                                        if (count($sqlfilters) > 0) {
                                            $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
                                        } else {
                                            $sqlcond = "";
                                        }
                                    }
                                    if ($fromid > 0) {
                                        $sqlM = str_replace("from doc ", "from $only $table ", $sqlM);
                                    }
                                    $fldFromId = ($fromid == 0) ? $fld->getRawValue('se_famid', 0) : $fromid;
                                    $sqlM = $this->injectFromClauseForOrderByLabel($fldFromId, $this->orderbyLabel, $sqlM);
                                    if ($sqlcond) {
                                        $qsql[] = $sqlM . " and " . $sqlcond;
                                    } else {
                                        $qsql[] = $sqlM;
                                    }
                                }
                            }
                            break;
                    }
                } else {
                    return false; // no query avalaible
                }
            }
        }
        if (is_array($qsql)) {
            return $qsql;
        }
        return array(
            $qsql
        );
    }
    /**
     * Get the family on which the search is operating
     * @return \Anakeen\Core\SmartStructure
     */
    public function getFamily()
    {
        return \Anakeen\Core\SEManager::getFamily($this->fromid);
    }
    /**
     * Insert an additional relation in the FROM clause of the given query
     * to perform a sort on a label/title instead of a key/id.
     *
     * After rewriting the query, the new column name which will serve for
     * the ordering is stored into the private _orderbyLabelMaps struct
     * which will be used later when the "ORDER BY" directive will be
     * constructed.
     *
     * @param int    $fromid The identifier of the family which the query is based on
     * @param string $column The name of the column on which the result is supposed to be be ordered
     * @param string $sqlM   The SQL query in which an additional FROM relation should be injected
     * @return string The modified query
     */
    private function injectFromClauseForOrderByLabel($fromid, $column, $sqlM)
    {
        if ($column == '') {
            return $sqlM;
        }
        $attr = $this->_getAttributeFromColumn($fromid, $column);
        if ($attr === false || $attr->isMultiple()) {
            return $sqlM;
        }
        switch ($attr->type) {
            case 'enum':
                $enumKeyLabelList = $attr->getEnum();
                $mapValues = array(
                    "('', NULL)"
                );
                foreach ($enumKeyLabelList as $key => $label) {
                    $mapValues[] = sprintf("('%s', '%s')", pg_escape_string($key), pg_escape_string($label));
                }
                $map = sprintf('(VALUES %s) AS map_%s(key, label)', join(', ', $mapValues), $attr->id);
                $where = sprintf("map_%s.key = coalesce(doc%s.%s, '')", $attr->id, $fromid, $attr->id);

                $sqlM = preg_replace('/ where /i', ", $map where ($where) and ", $sqlM);
                $this->orderby = preg_replace(sprintf('/\b%s\b/', preg_quote($column, "/")), sprintf("map_%s.label", $attr->id), $this->orderby);
                break;

            case 'docid':
                /*
                 * No need to inject anything, just remap the docid attribute
                 * to the one holding the title.
                */
                $opt_doctitle = $attr->getOption('doctitle');
                if ($opt_doctitle != '') {
                    if ($opt_doctitle == 'auto') {
                        $opt_doctitle = sprintf('%s_title', $attr->id);
                    }
                    $this->orderby = preg_replace(sprintf('/\b%s\b/', preg_quote($column, "/")), $opt_doctitle, $this->orderby);
                }
        }
        return $sqlM;
    }

    /**
     * Get the NormalAttribute object corresponding to the column of the given family
     *
     * @param $fromid
     * @param $column
     * @return \Anakeen\Core\SmartStructure\NormalAttribute|bool
     */
    private function _getAttributeFromColumn($fromid, $column)
    {
        $fam = Anakeen\Core\SEManager::getFamily($fromid);
        if (!$fam) {
            return false;
        }
        $attrList = $fam->getNormalAttributes();
        foreach ($attrList as $attr) {
            if ($attr->id == $column) {
                return $attr;
            }
        }
        return false;
    }
}