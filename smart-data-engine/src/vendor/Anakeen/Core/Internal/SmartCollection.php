<?php /** @noinspection PhpUnusedParameterInspection */

/**
 * Document searches classes
 */

namespace Anakeen\Core\Internal;

use Anakeen\Core\SEManager;

/**
 * Document searches classes
 *
 * @brief   class use to search documents
 */
class SmartCollection extends \Anakeen\Core\Internal\SmartElement
{
    /**
     * return document includes in search folder
     *
     * @param bool       $controlview if false all document are returned else only visible for current user  document are return
     * @param array      $filter      to add list sql filter for selected document
     * @param int|string $famid       family identifier to restrict search
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
     * @param object   $of    the object filter
     * @param integer &$famid return the family filter
     * @param string & $fsql  return the sql filter
     *
     * @return string error message if incorrect filter, empty if no errors
     */
    public function object2SqlFilter($of, &$famid, &$fsql)
    {
        if (!empty($of->family)) {
            if (preg_match('/([\w:]*)\s?(strict)?/', trim($of->family), $reg)) {
                if (!is_numeric($reg[1])) {
                    $reg[1] = \Anakeen\Core\SEManager::getFamilyIdFromName($reg[1]);
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
            if ((!strstr($of->sql, '--'))
                && (!strstr($of->sql, ';'))
                && (!stristr($of->sql, 'insert'))
                && (!stristr($of->sql, 'alter'))
                && (!stristr($of->sql, 'delete'))
                && (!stristr($of->sql, 'update'))) {
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
                    $err .= $this->oneObject2SqlFilter($c, $sqlone, $famid);
                    if ($err == "") {
                        $sql[] = $sqlone;
                    }
                } elseif ($c->or && is_array($c->or)) {
                    $sqlor = array();
                    foreach ($c->or as $cor) {
                        if ($cor->operator) {
                            $err .= $this->oneObject2SqlFilter($cor, $sqlone, $famid);
                        } else {
                            $oone = new \stdClass();
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
                            $err .= $this->oneObject2SqlFilter($cor, $sqlone, $famid);
                        } else {
                            $oone = new \stdClass();
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
     * @param \stdClass $c     the filter object
     * @param string &  $sql   return the sql where clause
     * @param string    $famid family identifier
     *
     * @return string error message. Empty is no errors
     */
    private function oneObject2SqlFilter($c, &$sql, $famid = "")
    {
        static $sw = false;
        static $operators = null;
        $err = '';
        if ($c->operator) {
            if ($operators === null) {
                $operators = SmartCollectionOperators::getOperators();
            }
            $top = $operators[$c->operator];
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
                    $sw = SEManager::createTemporaryDocument("DSEARCH");
                }
                /**
                 * @var \SmartStructure\DSEARCH $sw
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
     * @return \DocumentList
     */
    public function getDocumentList()
    {
        $s = new \Anakeen\Search\Internal\SearchSmartData($this->dbaccess);
        $s->useCollection($this->initid);
        $s->setObjectReturn();
        $s->excludeConfidential();
        $s->setOrder("fromid, title, id desc");
        return $s->search()->getDocumentList();
    }
}
