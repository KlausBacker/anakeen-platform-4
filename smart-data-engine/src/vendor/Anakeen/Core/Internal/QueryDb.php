<?php

namespace Anakeen\Core\Internal;

/**
 * Query to Database
 *
 */

class QueryDb
{
    public int $nb = 0;
    public $LastQuery = "";

    public $table;

    public $operators = array(
        "none" => array(
            "lib" => " --",
            "oper" => "",
            "param" => "NONE"
        ),
        "begin" => array(
            "lib" => "Commence par",
            "oper" => "like",
            "param" => "SEMIPERCENT"
        ),
        "like" => array(
            "lib" => "Contient",
            "oper" => "like",
            "param" => "PERCENT"
        ),
        "nlike" => array(
            "lib" => "Ne Contient Pas",
            "oper" => "not like",
            "param" => "PERCENT"
        ),
        "=" => array(
            "lib" => "Est égal à",
            "oper" => "=",
            "param" => "NORMAL"
        ),
        "!=" => array(
            "lib" => "Est différent de",
            "oper" => "!=",
            "param" => "NORMAL"
        ),
        ">" => array(
            "lib" => "Est Supérieur à",
            "oper" => ">",
            "param" => "NORMAL"
        ),
        "<" => array(
            "lib" => "Est Inférieur à",
            "oper" => "<",
            "param" => "NORMAL"
        ),
        "notn" => array(
            "lib" => "N'est pas Vide",
            "oper" => "is not null",
            "param" => "NONE"
        ),
        "null" => array(
            "lib" => "Est Vide",
            "oper" => "is null",
            "param" => "NONE"
        )
    );
    public $casse = "NON";

    public $criteria = "";
    public $order_by = "";
    public $operator;
    public $string;
    public $slice;
    public $start;
    public $desc;
    public $res_type;
    public $cindex;
    public $list = array();
    /**
     * @var DbObj
     */
    public $basic_elem;
    public $dbaccess;
    protected $class;

    public function __construct($dbaccess, $class)
    {
        $this->basic_elem = new $class($dbaccess);
        $this->dbaccess = $this->basic_elem->dbaccess;
        $this->class = $class;
    }

    private function initQuery($start = 0, $slice = 0, $p_query = "", $onlycont = false)
    {
        if ($start == "") {
            $start = 0;
        }
        if ($p_query == '') {
            // select construct
            $select = "";
            if (!$onlycont) {
                foreach ($this->basic_elem->fields as $k => $v) {
                    $select = $select . " " . $this->basic_elem->dbtable . "." . $v . ",";
                }

                foreach ($this->basic_elem->sup_fields as $k => $v) {
                    $select = $select . " " . $v . ",";
                }
                $select = substr($select, 0, strlen($select) - 1);
            } else {
                $select = 'count(*)';
            }
            // from
            $from = $this->basic_elem->dbtable;
            foreach ($this->basic_elem->sup_tables as $k => $v) {
                $from = $from . "," . $v;
            }

            $query = "select {$select}
              from {$from} ";

            $nb_where = 0;
            $where[$nb_where] = $this->CriteriaClause();
            if ($where[$nb_where] != "") {
                $nb_where++;
            }
            $where[$nb_where] = $this->AlphaClause();
            if ($where[$nb_where] != "") {
                $nb_where++;
            }
            $where[$nb_where] = $this->SupClause();
            if ($where[$nb_where] != "") {
                $nb_where++;
            }

            if ($nb_where > 0) {
                $i = 0;
                $query = $query . ' where ';
                reset($where);
                foreach ($where as $k => $v) {
                    if ($v != "") {
                        if ($i === 0) {
                            $query = $query . $v;
                        } else {
                            $query = $query . ' AND ' . $v;
                        }
                        $i++;
                    }
                }
            }
            // Order by
            if (($this->order_by != "") && (!$onlycont)) {
                $query = $query . " order by " . $this->order_by;
                if (isset($this->desc) && ($this->desc == "up")) {
                    $query = $query . " desc";
                }
            }
            if ($slice > 0) {
                $query .= " limit $slice";
            }
            if ($start > 0) {
                $query .= " offset $start";
            }
            $query .= ';';
        } else {
            $query = $p_query;
        }

        $this->slice = $slice;
        $this->start = $start;

        $this->LastQuery = $query;
        return $query;
    }

    /**
     * Perform the query : the result can be a table or a list of objects
     * depending on the third arg.
     *   the third ARG should be :
     *        LIST  : means a table of objects
     *        LISTC : means a table of completed objects
     *        TABLE : means a table of table fields
     *        ITEM  : means a ressource to step by step use table field rows
     *        ITER  : return class DbObjectList
     * @param int    $start
     * @param int    $slice
     * @param string $res_type
     * @param string $p_query
     * @return array|bool|\DbObjectList|resource|string
     * @throws \Anakeen\Database\Exception
     */
    public function query($start = 0, $slice = 0, $res_type = "LIST", $p_query = "")
    {
        $query = $this->initQuery($start, $slice, $p_query);
        $this->res_type = $res_type;
        $err = $this->basic_elem->query($query, 0, true);
        if ($res_type == "ITER") {
            if ($err) {
                throw new \Anakeen\Database\Exception("query fail : " . $err);
            }
            return new \DbObjectList($this->dbaccess, $this->basic_elem->res, $this->class);
        }

        if ($err != "") {
            return ($err);
        }

        $this->nb = $this->basic_elem->numrows();

        if ($this->nb === 0) {
            return false;
        }
        if ($res_type == "ITEM") {
            $this->cindex = 0; // current index row
            return $this->basic_elem->res;
        }


        if ($res_type == "TABLE") {
            $this->list = pg_fetch_all($this->basic_elem->res);
        } else {
            for ($c = 0; $c < $this->nb; $c++) {
                $result = $this->basic_elem->fetchArray($c);
                if (($res_type == "LIST") || ($res_type == "LISTC")) {
                    $this->list[$c] = new $this->class($this->dbaccess, "", $result, $this->basic_elem->dbid);
                } else {
                    foreach ($result as $k => $v) {
                        $this->list[$c][$k] = $v;
                    }
                }
            }
        }
        return ($this->list);
    }

    /**
     * Perform the query : return only the count fo rows returned
     * @param int $start
     * @param int $slice
     * @return string
     */
    public function count($start = 0, $slice = 0)
    {
        $query = $this->initQuery($start, $slice, "", true);
        $this->res_type = "TABLE";
        $err = $this->basic_elem->query($query);
        if ($err != "") {
            return ($err);
        }

        $result = $this->basic_elem->fetchArray(0);
        return ($result["count"]);
    }

    public function criteriaClause()
    {
        $out = "";
        if (isset($this->criteria) && ($this->criteria != "") && ($this->operator != "none")) {
            if ($this->casse == "NON") {
                $out = $out . " upper(" . $this->criteria . ") " . $this->operators[$this->operator]["oper"];
            } else {
                $out = $out . $this->criteria . " " . $this->operators[$this->operator]["oper"];
            }
            $string = "";
            switch ($this->operators[$this->operator]["param"]) {
                case "NORMAL":
                    $string = " {$this->string}";
                    break;

                case "PERCENT":
                    $string = " '%{$this->string}%'";
                    break;

                case "SEMIPERCENT":
                    $string = " '{$this->string}%'";
            }
            if (($this->operator != 'null') && ($this->operator != 'notn')) {
                if ($this->casse == "NON") {
                    $out .= " upper({$string})";
                } else {
                    $out .= $string;
                }
            }
        }
        return ($out);
    }

    public function alphaClause()
    {
        return '';
    }

    public function supClause()
    {
        $out = "";
        if (sizeof($this->basic_elem->sup_where) > 0) {
            reset($this->basic_elem->sup_where);
            $count = 0;
            foreach ($this->basic_elem->sup_where as $k => $v) {
                if ($count > 0) {
                    $out = $out . " AND (" . $v . ")";
                } else {
                    $out = "(" . $out . " " . $v . ")";
                }
                $count++;
            }
        }
        return ($out);
    }

    public function addQuery($contraint)
    {
        $this->basic_elem->sup_where[] = $contraint;
    }

    public function resetQuery()
    {
        $this->basic_elem->sup_where = array();
        unset($this->list);
    }

    public function addField($sqlattr, $resultname = "")
    {
        if ($resultname == "") {
            $this->basic_elem->sup_fields[] = $sqlattr;
        } else {
            $this->basic_elem->sup_fields[] = "$sqlattr as $resultname";
        }
    }
}
