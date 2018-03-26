<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Document searches classes
 */
/**
 * Document searches classes
 *
 * @brief class use to search documents
 * @class DocCollection
 * @package FDL
 */
class DocCollection extends Doc
{
    /**
     * conditionnal operator compatibilities
     *
     * @var array
     */
    public $top = array(
        "~*" => array(
            "label" => "include",
            "operand" => array(
                "left",
                "right"
            ) ,
            "dynlabel" => "{left} include {right}", # _("{left} include {right}")
            "slabel" => array(
                "file" => "filename or type include", #_("filename or type include")
                "image" => "filename or type include",
                "array" => "one value include", #_("one value include")
                
            ) ,
            "sdynlabel" => array(
                "file" => "{left} filename or type include {right}", #_("{left} filename or type include {right}")
                "image" => "{left} filename or type include {right}",
                "array" => "one value of {left} include {right}", #_("one value of {left} include {right}")
                
            ) ,
            "type" => array(
                "text",
                "longtext",
                "htmltext",
                "ifile",
                "array",
                "file",
                "image",
                "fulltext"
            )
        ) ,
        "=~*" => array(
            "label" => "title include",
            "operand" => array(
                "left",
                "right"
            ) ,
            "dynlabel" => "{left} title include {right}", # _("{left} last or first name include {right}")
            "slabel" => array(
                "uid" => "last or first name include",
                "docidtitle[]" => "one of the titles include"
            ) , #_("title include") _("last or first name include") _("one of the titles include")
            "sdynlabel" => array(
                "uid" => "{left} last or first name include {right}",
                "docidtitle[]" => "one of the titles {left} include {right}"
            ) , #_("{left} title include {right}") _("one of the titles {left} include {right}")
            "type" => array(
                "uid",
                "docid",
                "account",
                "docidtitle[]"
            )
        ) ,
        "@@" => array(
            "label" => "content file word",
            "operand" => array(
                "left",
                "right"
            ) ,
            "dynlabel" => "file {left} contain the word {right}", # _("file {left} contain the word {right}")
            "type" => array(
                "file"
            )
        ) ,
        "~@" => array(
            "label" => "content file expression",
            "operand" => array(
                "left",
                "right"
            ) ,
            "dynlabel" => "file {left} contain the expression {right}", # _("file {left} contain the expression {right}")
            "type" => array(
                "file"
            )
        ) ,
        "=" => array(
            "label" => "equal",
            "operand" => array(
                "left",
                "right"
            ) ,
            "dynlabel" => "{left} equal {right}", # _("{left} equal {right}")
            "slabel" => array(
                "docid" => "identificator equal",
                "account" => "identificator equal",
                "uid" => "system identifiant equal"
            ) , #_("identificator equal") _("system identifiant equal")
            "sdynlabel" => array(
                "docid" => "{left} identifier equal {right}",
                "account" => "{left} identifier equal {right}",
                "uid" => "{left} system identifier equal {right}"
            ) , #_("{left} identifier equal {right}") _("{left} system identifier equal {right}")
            "type" => array(
                "text",
                "integer",
                "int",
                "double",
                "enum",
                "date",
                "time",
                "timestamp",
                "money",
                "color",
                "docid",
                "account",
                "uid"
            )
        ) ,
        "~^" => array(
            "label" => "begin by",
            "operand" => array(
                "left",
                "right"
            ) ,
            "dynlabel" => "{left} begin by {right}", # _("{left} begin by {right}")
            "type" => array(
                "text",
                "longtext"
            )
        ) ,
        "!=" => array(
            "label" => "not equal",
            "operand" => array(
                "left",
                "right"
            ) ,
            "dynlabel" => "{left} is not equal {right}", # _("{left} is not equal {right}")
            "sdynlabel" => array(
                "docid" => "{left} identifier not equal {right}",
                "account" => "{left} identifier not equal {right}",
                "uid" => "{left} system identifier not equal {right}"
            ) , #_("{left} identifier not equal {right}") _("{left} system identifier not equal {right}")
            "slabel" => array(
                "docid" => "identificator not equal",
                "account" => "identificator not equal",
                "uid" => "system identifier not equal"
            ) , #_("identificator not equal") _("system identifier not equal")
            "type" => array(
                "text",
                "integer",
                "int",
                "double",
                "enum",
                "date",
                "time",
                "timestamp",
                "money",
                "color",
                "docid",
                "account",
                "uid"
            )
        ) ,
        "!~*" => array(
            "label" => "not include",
            "operand" => array(
                "left",
                "right"
            ) ,
            "dynlabel" => "{left} not include {right}", # _("{left} not include {right}")
            "slabel" => array(
                "file" => "filename or type not include",
                "fulltext" => "any value include", #_("any value include")
                "image" => "filename or type not include", #_("filename or type not include")
                "array" => "no value include", #_("no value include")
                
            ) ,
            "sdynlabel" => array(
                "file" => "{left} filename or type not include {right}",
                "fulltext" => "any values include {right}", #_("any values include {right}")
                "image" => "{left} filename or type not include {right}", #_("{left} filename or type not include {right}")
                "array" => "{left} include no value of {right}"
            ) , #_("{left} include no value of {right}")
            "type" => array(
                "text",
                "longtext",
                "htmltext",
                "ifile",
                "array",
                "file",
                "image",
                "fulltext"
            )
        ) ,
        ">" => array(
            "label" => "&gt;",
            "operand" => array(
                "left",
                "right"
            ) ,
            "dynlabel" => "{left} greater than {right}", # _("{left} greater than {right}")
            "type" => array(
                "int",
                "integer",
                "double",
                "date",
                "time",
                "timestamp",
                "money"
            )
        ) ,
        "<" => array(
            "label" => "&lt;",
            "operand" => array(
                "left",
                "right"
            ) ,
            "dynlabel" => "{left} lesser than {right}", # _("{left} lesser than {right}")
            "type" => array(
                "int",
                "integer",
                "double",
                "date",
                "time",
                "timestamp",
                "money"
            )
        ) ,
        ">=" => array(
            "label" => "&gt; or equal",
            "operand" => array(
                "left",
                "right"
            ) ,
            "dynlabel" => "{left} greater or equal to {right}", # _("{left} greater or equal to {right}")
            "type" => array(
                "int",
                "integer",
                "double",
                "date",
                "time",
                "timestamp",
                "money"
            )
        ) ,
        "<=" => array(
            "label" => "&lt; or equal",
            "operand" => array(
                "left",
                "right"
            ) ,
            "dynlabel" => "{left} lesser or equal to {right}", # _("{left} lesser or equal to {right}")
            "type" => array(
                "int",
                "integer",
                "double",
                "date",
                "time",
                "timestamp",
                "money"
            )
        ) ,
        "is null" => array(
            "label" => "is empty",
            "operand" => array(
                "left"
            ) ,
            "dynlabel" => "{left} is null"
        ) , # _("{left} is null"),
        "is not null" => array(
            "label" => "is not empty",
            "operand" => array(
                "left"
            ) ,
            "dynlabel" => "{left} is not empty"
        ) , # _("{left} is not empty"),
        "><" => array(
            "label" => "between", #_("between")
            "operand" => array(
                "left",
                "min",
                "max"
            ) ,
            "dynlabel" => "{left} is between {min} and {max}", # _("{left} is between {min} and {max}")
            "type" => array(
                "int",
                "integer",
                "double",
                "date",
                "time",
                "timestamp",
                "money"
            )
        ) ,
        "~y" => array(
            "label" => "one value equal", #("one value equal")
            "operand" => array(
                "left",
                "right"
            ) ,
            "dynlabel" => "{left} one value equal {right}", # _("{left} one value equal {right}")
            "slabel" => array(
                "docid[]" => "one id equal", # _("one id equal")
                "account[]" => "one id equal"
            ) ,
            "sdynlabel" => array(
                "docid[]" => "{left} one id equal {right}", #_("{left} one id equal {right}")
                "account[]" => "{left} one id equal {right}"
            ) ,
            "type" => array(
                "array",
                "docid[]",
                "account[]"
            )
        )
    );
    /**
     * get label forom operatore code
     *
     * @param string $operator operator code
     * @param string $attributeType sttribute type
     *
     * @return string
     */
    public function getOperatorLabel($operator, $attributeType)
    {
        $op = $this->top[$operator];
        if (!$op) {
            return _("unknow operator") . " : $operator";
        } // TODO set exception
        if ($attributeType) {
            if (isset($op["slabel"]) && is_array($op["slabel"])) {
                foreach ($op["slabel"] as $type => $label) {
                    if ($type == $attributeType) {
                        return _($label);
                    }
                }
            }
        }
        if ($op["label"]) {
            return _($op["label"]);
        }
        return $operator; // no label found
    }
    /**
     * return document includes in search folder
     *
     * @param bool $controlview if false all document are returned else only visible for current user  document are return
     * @param array $filter to add list sql filter for selected document
     * @param int|string $famid family identifier to restrict search
     *
     * @return array array of document array
     */
    public function getContent($controlview = true, array $filter = array(), $famid = "", $qtype = "TABLE", $trash = "")
    {
        return array();
    }
    /**
     * return sql filter from object filter
     *
     * @param object $of the object filter
     * @param integer &$famid return the family filter
     * @param string &$fsql return the sql filter
     *
     * @return string error message if incorrect filter, empty if no errors
     */
    public function object2SqlFilter($of, &$famid, &$fsql)
    {
        if (!empty($of->family)) {
            if (preg_match('/([\w:]*)\s?(strict)?/', trim($of->family), $reg)) {
                if (!is_numeric($reg[1])) {
                    $reg[1] = \Anakeen\Core\DocManager::getFamilyIdFromName($reg[1]);
                }
                if (isset($reg[2]) && ($reg[2] == "strict")) {
                    $famid = '-' . $reg[1];
                } else {
                    $famid = $reg[1];
                }
            }
        }
        if (!$famid) {
            $famid = $this->getRawValue("se_famid");
            if (!$famid) { // search in origin filter
                $filter = $this->getMultipleRawValues("se_filter", '', 0);
                if ($filter) {
                    $xfilter = simplexml_load_string($filter);
                    $famid = trim($xfilter->family);
                }
            }
        }
        $sql = array();
        if (!empty($of->sql)) {
            $of->sql = trim($of->sql);
        }
        if (!empty($of->sql)) {
            if ((!strstr($of->sql, '--')) && (!strstr($of->sql, ';')) && (!stristr($of->sql, 'insert')) && (!stristr($of->sql, 'alter')) && (!stristr($of->sql, 'delete')) && (!stristr($of->sql, 'update'))) {
                // try to prevent sql injection
                $sql[] = $of->sql;
            }
        }
        if ((!empty($of->criteria)) && (!is_array($of->criteria))) {
            if ($of->criteria->operator) {
                $of->criteria = array(
                $of->criteria
            );
            }
            if ($of->criteria->or) {
                $of->criteria = array(
                $of->criteria
            );
            }
            if ($of->criteria->and) {
                $of->criteria = array(
                $of->criteria
            );
            }
        }
        $err = '';
        if ((!empty($of->criteria)) && is_array($of->criteria)) {
            foreach ($of->criteria as $c) {
                $sqlone = '';
                if (!empty($c->operator)) {
                    $err.= $this->_1object2SqlFilter($c, $sqlone, $famid);
                    if ($err == "") {
                        $sql[] = $sqlone;
                    }
                } elseif ($c->or && is_array($c->or)) {
                    $sqlor = array();
                    foreach ($c->or as $cor) {
                        if ($cor->operator) {
                            $err.= $this->_1object2SqlFilter($cor, $sqlone, $famid);
                        } else {
                            $oone = new stdClass();
                            $oone->criteria = $cor;
                            $sqlone = '';
                            $_f = '';
                            $this->object2SqlFilter($oone, $_f, $sqlone);
                        }
                        if ($err == "") {
                            $sqlor[] = $sqlone;
                        }
                    }
                    if (count($sqlor) > 0) {
                        $sql[] = '(' . implode(') or (', $sqlor) . ')';
                    }
                } elseif ($c->and && is_array($c->and)) {
                    $sqlor = array();
                    foreach ($c->and as $cor) {
                        if ($cor->operator) {
                            $err.= $this->_1object2SqlFilter($cor, $sqlone, $famid);
                        } else {
                            $oone = new stdClass();
                            $oone->criteria = $cor;
                            $_f = '';
                            $this->object2SqlFilter($oone, $_f, $sqlone);
                        }
                        if ($err == "") {
                            $sqlor[] = $sqlone;
                        }
                    }
                    if (count($sqlor) > 0) {
                        $sql[] = '(' . implode(') and (', $sqlor) . ')';
                    }
                }
            }
        }
        if (count($sql) > 0) {
            $fsql = '(' . implode(') and (', $sql) . ')';
        } else {
            $fsql = "true";
        }
        return $err;
    }
    /**
     * return sql from a single object filter
     *
     * @param stdClass $c the filter object
     * @param string &$sql return the sql where clause
     * @param string $famid family identifier
     *
     * @return string error message. Empty is no errors
     */
    private function _1object2SqlFilter($c, &$sql, $famid = "")
    {
        static $sw = false;
        $err = '';
        if ($c->operator) {
            $top = $this->top[$c->operator];
            if ($top) {
                $toperand = $top["operand"];
                $first = $toperand[0];
                $second = isset($toperand[1]) ? $toperand[1] : null;
                $third = isset($toperand[2]) ? $toperand[2] : null;
                $col = $c->$first;
                if ($second) {
                    $val1 = (isset($c->$second)) ? $c->$second : null;
                    if (!$val1) {
                        $sql = "true"; // incomplete request parameters
                        return "";
                    }
                    if (strtolower(substr($val1, 0, 5)) == "::get") { // only get method allowed
                        // it's method call
                        $val1 = $this->ApplyMethod($val1);
                    }
                } else {
                    $val1 = "";
                }
                if ($third) {
                    $val2 = $c->$third;
                    if (!$val2) {
                        $sql = "true"; // incomplete request parameters
                        return "";
                    }
                    if (strtolower(substr($val2, 0, 5)) == "::get") { // only get method allowed
                        // it's method call
                        $val2 = $this->ApplyMethod($val2);
                    }
                } else {
                    $val2 = "";
                }
                if (!$sw) {
                    $sw = createTmpDoc($this->dbaccess, "DSEARCH");
                }
                /**
                 * @var \Dcp\Family\DSEARCH $sw
                 */
                $sw->setValue("se_famid", $famid);
                $sql = $sw->getSqlCond($col, $c->operator, $val1, $val2, $err);
            } else {
                $err = sprintf(_("operator [%s] not allowed"), $c->operator);
            }
        } else {
            $err = sprintf(_("no operator detected"));
        }
        return $err;
    }
    /**
     * return specfic filters instead of normal content
     *
     * @return array of sql filters
     */
    public function getSpecificFilters()
    {
        return array();
    }
    /**
     * test if document has specific filters
     *
     * @return boolean true if has filter
     */
    public function hasSpecificFilters()
    {
        return (count($this->getSpecificFilters()) > 0);
    }
    /**
     * return content of collection
     *
     * @return DocumentList
     */
    public function getDocumentList()
    {
        $s = new SearchDoc($this->dbaccess);
        $s->useCollection($this->initid);
        $s->setObjectReturn();
        $s->excludeConfidential();
        $s->setOrder("fromid, title, id desc");
        return $s->search()->getDocumentList();
    }
}
