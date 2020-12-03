<?php
/*
 * @author Anakeen
 */

/**
 * This class is a generic DB Class that can be used to create objects
 * based on the description of a DB Table. More Complex Objects will
 * inherit from this basic Class.
 *
 */
class PgObj
{
    /**
     * the database connection resource
     * @var resource|-1
     */
    public $dbid = -1;
    /**
     * coordinates to access to database
     * @var string
     */
    public $dbaccess = '';
    /**
     * array of SQL fields use for the object
     * @var array
     */
    public $fields = array(
        '*'
    );

    public $id_fields = array();
    /**
     * name of the SQL table
     * @var string
     */
    public $dbtable = '';

    public $criterias = array();
    /**
     * array of other SQL fields, not in attribute of object
     * @var array
     */
    public $sup_fields = array();
    public $sup_where = array();
    public $sup_tables = array();
    public $fulltextfields = array();
    /**
     * sql field to order
     * @var string
     */
    public $order_by = "";
    /**
     * indicates if fields has been affected
     * @var string
     * @see affect()
     */
    public $isset = false; // indicate if fields has been affected (call affect methods)
    public $sqlcreate;
    public $sqlinit;
    /**
     * @var resource
     */
    public $res = '';
    protected $msg_err = '';
    //----------------------------------------------------------------------------
    /**
     * @var false|string
     */
    protected $selectstring;

    /**
     * Database Object constructor
     *
     * @param string $dbaccess database specification
     * @param int|string $id identificator of the object
     * @param array|string $res array of result issue to QueryDb {@link QueryDb::Query()}
     * @param int|resource $dbid the database connection resource
     * @return \PgObj|bool false if error occured
     */
    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        $this->dbaccess = $dbaccess;
        $this->initDbid();

        if (empty($this->dbid)) {
            $this->dbid = -1;
        }

        $this->selectstring = "";
        // SELECTED FIELDS
        foreach ($this->fields as $v) {
            $this->selectstring = $this->selectstring . $this->dbtable . "." . $v . ",";
            $this->$v = "";
        }

        foreach ($this->sup_fields as $v) {
            $this->selectstring = $this->selectstring . "" . $v . ",";
            $this->$v = "";
        }
        $this->selectstring = substr($this->selectstring, 0, strlen($this->selectstring) - 1);
        // select with the id
        if (($id != '') || (is_array($id)) || (!isset($this->id_fields[0]))) {
            $ret = $this->select($id);

            return ($ret);
        }
        // affect with a query result
        if (is_array($res)) {
            $this->affect($res);
        }

        return true;
    }

    public function select($id)
    {
        if ($this->dbid == -1) {
            return false;
        }

        $msg = $this->preSelect($id);
        if ($msg != '') {
            return $msg;
        }

        if ($this->dbtable == '') {
            return ("error : No Tables");
        }
        $fromstr = "{$this->dbtable}";
        if (is_array($this->sup_tables)) {
            foreach ($this->sup_tables as $v) {
                $fromstr .= "," . $v;
            }
        }
        $sql = "select {$this->selectstring} from {$fromstr} ";

        $count = 0;
        if (is_array($id)) {
            $count = 0;
            $wherestr = " where ";
            foreach ($this->id_fields as $k => $v) {
                if ($count > 0) {
                    $wherestr = $wherestr . " AND ";
                }
                $wherestr = $wherestr . "( " . $this->dbtable . "." . $v . "='" . pg_escape_string($id[$k]) . "' )";
                $count = $count + 1;
                //$this->$v = $id[$k];
            }
        } else {
            if (isset($this->id_fields[0])) {
                $wherestr = "where " . $this->dbtable . "." . $this->id_fields[0] . "='" . pg_escape_string($id) . "'";
            } else {
                $wherestr = "";
            }
        }
        if (is_array($this->sup_where)) {
            foreach ($this->sup_where as $v) {
                $wherestr = $wherestr . " AND ";
                $wherestr = $wherestr . "( " . $v . " )";
                $count = $count + 1;
            }
        }

        $sql = $sql . " " . $wherestr;

        $this->execQuery($sql);

        if ($this->numrows() > 0) {
            $res = $this->fetchArray(0);
            $this->affect($res);
        } else {
            return false;
        }
        $msg = $this->postSelect($id);
        if ($msg != '') {
            return $msg;
        }
        return true;
    }

    public function affect($array)
    {
        foreach ($array as $k => $v) {
            if (!is_integer($k)) {
                $this->$k = $v;
            }
        }
        $this->complete();
        $this->isset = true;
    }

    /**
     * verify that the object exists
     *
     * if true values of the object has been set
     * @return bool
     */
    public function isAffected()
    {
        return $this->isset;
    }

    public function complete()
    {
        // This function should be replaced by the Child Class
        return '';
    }

    /**
     * Method use before Add method
     * This method should be replaced by the Child Class
     *
     * @return string error message, if no error empty string
     * @see Add()
     */
    public function preInsert()
    {
        // This function should be replaced by the Child Class
        return '';
    }

    /**
     * Method use after Add method
     * This method should be replaced by the Child Class
     *
     * @return string error message, if no error empty string, if message
     * error not empty the Add method is not completed
     * @see Add()
     */
    public function postInsert()
    {
        // This function should be replaced by the Child Class
        return '';
    }

    /**
     * Method use before Modify method
     * This method should be replaced by the Child Class
     *
     * @return string error message, if no error empty string
     * @see modify()
     */
    public function preUpdate()
    {
        // This function should be replaced by the Child Class
        return '';
    }

    /**
     * Method use after Modify method
     * This method should be replaced by the Child Class
     *
     * @return string error message, if no error empty string, if message
     * error not empty the Modify method is not completed
     * @see modify()
     */
    public function postUpdate()
    {
        // This function should be replaced by the Child Class
        return '';
    }

    public function preDelete()
    {
        // This function should be replaced by the Child Class
        return '';
    }

    public function postDelete()
    {
        // This function should be replaced by the Child Class
        return '';
    }

    public function preSelect($id)
    {
        // This function should be replaced by the Child Class
        return '';
    }

    public function postSelect($id)
    {
        // This function should be replaced by the Child Class
        return '';
    }

    public function postInit()
    {
        // This function should be replaced by the Child Class
        return '';
    }

    /**
     * Add the object to the database
     * @param bool $nopost PostInsert method not apply if true
     * @return string error message, if no error empty string
     * @see PreInsert()
     * @see PostInsert()
     */
    public function add($nopost = false)
    {
        if ($this->dbid == -1) {
            return false;
        }

        $msg = $this->preInsert();
        if ($msg) {
            return $msg;
        }

        $sfields = implode(",", $this->fields);
        $sql = "insert into " . $this->dbtable . "($sfields) values (";

        $valstring = "";
        foreach ($this->fields as $k => $v) {
            $valstring = $valstring . $this->lw($this->$v) . ",";
        }
        $valstring = substr($valstring, 0, strlen($valstring) - 1);
        $sql = $sql . $valstring . ")";
        // requery execution
        $msg = $this->execQuery($sql);

        if ($msg) {
            return $msg;
        }

        $this->isset = true;
        if (!$nopost) {
            $msg = $this->postInsert();
        }

        return $msg;
    }

    /**
     * update the object in database
     * @param bool $nopost PostUpdate() and method not apply if true
     * @param string $sfields only this column will ne updated if empty all fields
     * @param bool $nopre PreUpdate() method not apply if true
     * @return string error message, if no error empty string
     * @see PreUpdate()
     * @see PostUpdate()
     */
    public function modify($nopost = false, $sfields = "", $nopre = false)
    {
        if ($this->dbid == -1) {
            return false;
        }

        if (!$nopre) {
            $msg = $this->preUpdate();
            if ($msg) {
                return $msg;
            }
        }

        $sql = "update " . $this->dbtable . " set ";

        $nb_keys = 0;
        foreach ($this->id_fields as $v) {
            $notset[$v] = "Y";
            $nb_keys++;
        }

        if (!is_array($sfields)) {
            $fields = $this->fields;
        } else {
            $fields = $sfields;
            foreach ($this->id_fields as $v) {
                $fields[] = $v;
            }
        }

        $setstr = "";
        $wstr = "";
        foreach ($fields as $v) {
            if (!isset($notset[$v])) {
                $setstr = $setstr . " " . $v . "=" . $this->lw($this->$v) . ",";
            } else {
                $val = pg_escape_string($this->$v);
                $wstr = $wstr . " " . $v . "='" . $val . "' AND";
            }
        }
        $setstr = substr($setstr, 0, strlen($setstr) - 1);
        $wstr = substr($wstr, 0, strlen($wstr) - 3);
        $sql .= $setstr;
        if ($nb_keys > 0) {
            $sql .= " where " . $wstr . ";";
        }

        $msg = $this->execQuery($sql);
        if ($msg) {
            return $msg;
        }

        if (!$nopost) {
            $msg = $this->postUpdate();
        }

        return $msg;
    }

    public function delete($nopost = false)
    {
        $msg = $this->preDelete();
        if ($msg) {
            return $msg;
        }
        $wherestr = "";
        $count = 0;

        foreach ($this->id_fields as $k => $v) {
            if ($count > 0) {
                $wherestr = $wherestr . " AND ";
            }
            $wherestr = $wherestr . "( " . $v . "='" . AddSlashes($this->$v) . "' )";
            $count++;
        }
        // suppression de l'enregistrement
        $sql = "delete from " . $this->dbtable . " where " . $wherestr . ";";

        $msg = $this->execQuery($sql);
        if ($msg) {
            return $msg;
        }

        if (!$nopost) {
            $msg = $this->postDelete();
        }

        return $msg;
    }

    public function lw($prop)
    {
        return ($prop == '' ? "null" : "'" . pg_escape_string($prop) . "'");
    }

    public function closeConnect()
    {
        return pg_close($this->dbid);
    }

    public function create($nopost = false)
    {
        $msg = "";

        if (isset($this->sqlcreate)) {
            // step by step
            if (is_array($this->sqlcreate)) {
                foreach ($this->sqlcreate as $sqlquery) {
                    $msg .= $this->execQuery($sqlquery, 1);
                }
            } else {
                $sqlcmds = explode(";", $this->sqlcreate);
                foreach ($sqlcmds as $sqlquery) {
                    $msg .= $this->execQuery($sqlquery, 1);
                }
            }
        }
        if (isset($this->sqlinit)) {
            $msg = $this->execQuery($this->sqlinit, 1);
        }
        if ($msg) {
            return $msg;
        }

        if (!$nopost) {
            $msg = $this->postInit();
        }

        return ($msg);
    }

    public static function closeMyPgConnections()
    {
        global $_DBID;

        $pid = getmypid();

        if (!isset($_DBID[$pid])) {
            return;
        }
        foreach ($_DBID[$pid] as $conn) {
            @pg_close($conn);
        }
        unset($_DBID[$pid]);
    }

    public function initDbid()
    {
        global $_DBID;

        $pid = getmypid();

        if (isset($_DBID[$pid]) && isset($_DBID[$pid][$this->dbaccess]) && is_resource($_DBID[$pid][$this->dbaccess])) {
            $status = pg_connection_status($_DBID[$pid][$this->dbaccess]);
            if ($status !== PGSQL_CONNECTION_OK) {
                pg_connection_reset($_DBID[$pid][$this->dbaccess]);
            }
        } else {
            $_DBID[$pid][$this->dbaccess] = pg_connect($this->dbaccess, PGSQL_CONNECT_FORCE_NEW);
        }
        $this->dbid = $_DBID[$pid][$this->dbaccess];

        return $this->dbid;
    }

    public function execQuery($sql, int $lvl = 0)
    {
        global $SQLDELAY, $SQLDEBUG;

        if (!$sql) {
            return '';
        }

        if ($SQLDEBUG) {
            $sqlt1 = microtime();
        }

        $this->initDbid();

        $this->res = @pg_query($this->dbid, $sql);

        $pgmess = pg_last_error($this->dbid);

        $this->msg_err = chop(preg_replace("/ERROR: {2}/", "", $pgmess));
        // Use Postgresql error codes instead of localized text messages
        $action_needed = "";
        if ($lvl === 0) { // to avoid recursivity
            if ($this->msg_err != "") {
                if ((preg_match(
                    "/Relation ['\"]([a-zA-Z_]*)['\"] does not exist/i",
                    $this->msg_err
                ) || preg_match("/Relation (.*) n'existe pas/i", $this->msg_err) || preg_match("/class \"([a-zA-Z_]*)\" not found/i", $this->msg_err))) {
                    $action_needed = "create";
                } else {
                    if ((preg_match(
                        "/No such attribute or function '([a-zA-Z_0-9]*)'/i",
                        $this->msg_err
                    )) || (preg_match("/Attribute ['\"]([a-zA-Z_0-9]*)['\"] not found/i", $this->msg_err))) {
                        $action_needed = "update";
                    } else {
                        if (preg_match(
                            "/relation ['\"](.*)['\"] already exists/i",
                            $this->msg_err
                        ) || preg_match("/relation (.*) existe d/i", $this->msg_err)) {
                            $action_needed = "none";
                        }
                    }
                }
            }
        }

        switch ($action_needed) {
            case "create":
                $st = $this->create();
                if ($st == "") {
                    $this->msg_err = $this->execQuery($sql);
                } else {
                    return "Table {$this->dbtable} doesn't exist and can't be created";
                }
                break;

            case "update":
                return "Table {$this->dbtable} cannot be updated";
                break;

            case "none":
                $this->msg_err = "";
                break;

            default:
                break;
        }

        if ($SQLDEBUG) {
            global $TSQLDELAY;
            /** @noinspection PhpUndefinedVariableInspection */
            $SQLDELAY += te_microtime_diff(microtime(), $sqlt1); // to test delay of request
            $TSQLDELAY[] = array(
                "t" => sprintf("%.04f", te_microtime_diff(microtime(), $sqlt1)),
                "s" => str_replace("from", "<br/>from", $sql)
            );
        }
        return ($this->msg_err);
    }

    public function numrows()
    {
        if ($this->msg_err == "") {
            return (pg_num_rows($this->res));
        } else {
            return (0);
        }
    }

    public function fetchArray($c, $type = PGSQL_ASSOC)
    {
        return (pg_fetch_array($this->res, $c, $type));
    }
}
