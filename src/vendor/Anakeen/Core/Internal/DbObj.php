<?php

namespace Anakeen\Core\Internal;

use Anakeen\Core\ContextManager;

/**
 * This class is a generic DB Class that can be used to create objects
 * based on the description of a DB Table. More Complex Objects will
 * inherit from this basic Class.
 *
 */
class DbObj
{
    /**
     * the database connection resource
     *
     * @var resource
     */
    public $dbid = -1;
    /**
     * coordinates to access to database
     *
     * @var string
     */
    public $dbaccess = '';
    /**
     * array of SQL fields use for the object
     *
     * @var array
     */
    public $fields
        = array(
            '*'
        );
    /**
     * name of the SQL table
     *
     * @var string
     */
    public $dbtable = '';

    public $id_fields;

    public $criterias = array();
    /**
     * array of other SQL fields, not in attribute of object
     *
     * @var array
     */
    public $sup_fields = array();
    public $sup_where = array();
    public $sup_tables = array();
    public $fulltextfields = array();
    /**
     * sql field to order
     *
     * @var string
     */
    public $order_by = "";
    /**
     * indicates if fields has been affected
     *
     * @var string
     * @see Affect()
     */
    public $isset = false; // indicate if fields has been affected (call affect methods)
    public static $savepoint = array();
    public static $lockpoint = array();
    public static $sqlStrict = true;
    /**
     * @var string error message
     */
    public $msg_err = '';
    /**
     * @var int
     */
    public $err_code = '';
    /**
     * @var resource
     */
    public $res = '';
    /**
     * @var bool
     */
    public $debug = false;
    public $sqlcreate;
    public $sqlinit;
    /**
     * @var \Anakeen\Core\Internal\Log DbObj Log Object
     */
    public $log;
    private $selectstring;
    //----------------------------------------------------------------------------

    /**
     * Database Object constructor
     *
     *
     * @param string $dbaccess database specification
     * @param string $id       identifier of the object
     * @param string $res      array of result issue to QueryDb {@link QueryDb::Query()}
     * @param int    $dbid     the database connection resource
     *
     * @throws \Dcp\Core\Exception
     */
    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        if (!$dbaccess) {
            $dbaccess = \Anakeen\Core\DbManager::getDbAccess();
        }
        $this->dbaccess = $dbaccess;
        $this->init_dbid();
        //global ${$this->oname};
        $this->log = new \Anakeen\Core\Internal\Log("", "DbObj", $this->dbtable);

        if ($this->dbid == 0) {
            $this->dbid = -1;
        }

        $this->selectstring = "";
        // SELECTED FIELDS
        reset($this->fields);
        foreach ($this->fields as $k => $v) {
            $this->selectstring = $this->selectstring . $this->dbtable . "." . $v . ",";
            $this->$v = "";
        }

        reset($this->sup_fields);
        foreach ($this->sup_fields as $k => $v) {
            $this->selectstring = $this->selectstring . "" . $v . ",";
            $this->$v = "";
        }
        $this->selectstring = substr($this->selectstring, 0, strlen($this->selectstring) - 1);
        if (self::$sqlStrict === null) {
            self::$sqlStrict = (\Anakeen\Core\ContextManager::getApplicationParam('CORE_SQLSTRICT') != 'no');
        }
        // select with the id
        if (($id != '') || (is_array($id)) || (!isset($this->id_fields[0]))) {
            $ret = $this->Select($id);

            return ($ret);
        }
        // affect with a query result
        if (is_array($res)) {
            $this->Affect($res);
        }

        return true;
    }

    /**
     * Select object from its fields
     * if fields has more then one variable, need to use an array
     *
     * @param int|array $id
     *
     * @return bool|string
     */
    public function Select($id)
    {
        if (!$id) {
            return false;
        }
        if ($this->dbid == -1) {
            return false;
        }

        $msg = $this->PreSelect($id);
        if ($msg != '') {
            return $msg;
        }

        if ($this->dbtable == '') {
            return ("error : No Tables");
        }
        $fromstr = "{$this->dbtable}";
        if (is_array($this->sup_tables)) {
            reset($this->sup_tables);
            foreach ($this->sup_tables as $k => $v) {
                $fromstr .= "," . $v;
            }
        }
        $sql = "select {$this->selectstring} from {$fromstr} ";

        $count = 0;
        if (is_array($id)) {
            $count = 0;
            $wherestr = " where ";
            reset($this->id_fields);
            foreach ($this->id_fields as $k => $v) {
                if ($count > 0) {
                    $wherestr = $wherestr . " AND ";
                }
                $wherestr = $wherestr . "( " . $this->dbtable . "." . $v . "=E'" . pg_escape_string($id[$k]) . "' )";
                $count = $count + 1;
            }
        } else {
            if (isset($this->id_fields[0])) {
                $wherestr = "where " . $this->dbtable . "." . $this->id_fields[0] . "=E'" . pg_escape_string($id) . "'";
            } else {
                $wherestr = "";
            }
        }
        if (is_array($this->sup_where)) {
            reset($this->sup_where);
            foreach ($this->sup_where as $k => $v) {
                $wherestr = $wherestr . " AND ";
                $wherestr = $wherestr . "( " . $v . " )";
                $count = $count + 1;
            }
        }

        $sql = $sql . " " . $wherestr;

        $this->exec_query($sql);

        if ($this->numrows() > 0) {
            $res = $this->fetch_array(0);
            $this->Affect($res);
        } else {
            return false;
        }
        $msg = $this->PostSelect($id);
        if ($msg != '') {
            return $msg;
        }
        return true;
    }

    /**
     * get all values in indexed array
     *
     * @return array
     */
    public function getValues()
    {
        $r = array();
        foreach ($this->fields as $k => $v) {
            $r[$v] = $this->$v;
        }
        return $r;
    }

    /**
     * affect object specific column values from this item
     * use only when object is already affected
     *
     * @param array $fields sql field to affect
     *
     * @param bool  $reset
     *
     * @return bool true if OK false else
     */
    public function affectColumn($fields, $reset = true)
    {
        if ($this->dbid == -1) {
            return false;
        }

        if (!$this->isAffected()) {
            return false;
        }
        if (count($fields) == 0) {
            return true;
        }
        if ($this->dbtable == '') {
            return ("error : No Tables");
        }
        $fromstr = $this->dbtable;
        $w = array();
        foreach ($this->id_fields as $id) {
            $w[] = "($id = E'" . pg_escape_string($this->$id) . "') ";
        }
        $sqlwhere = implode("and", $w);
        $sqlselect = implode(",", $fields);

        $sql = "select $sqlselect from $fromstr where $sqlwhere";

        $this->exec_query($sql);

        if ($this->numrows() > 0) {
            $res = $this->fetch_array(0);
            $this->affect($res, false, $reset);
        } else {
            return false;
        }
        return true;
    }

    /**
     * affect object with a set of values
     *
     * @param array $array indexed array of values , index if the column attribute
     * @param bool  $more
     * @param bool  $reset
     */
    public function affect($array, $more = false, $reset = true)
    {
        foreach ($array as $k => $v) {
            if (!is_integer($k)) {
                $this->$k = $v;
            }
        }
        $this->Complete();
        $this->isset = true;
    }

    /**
     * verify that the object exists in database
     * test if object has fields id set
     * if true values of the object has been set
     *
     * @æpi test if object if affected
     * @see affect
     * @return bool
     */
    public function isAffected()
    {
        return $this->isset;
    }

    /**
     * @see affect
     */
    public function Complete()
    {
        // This function should be replaced by the Child Class
    }

    /**
     * Method use before Add method
     * This method should be replaced by the Child Class
     * if return error message, modify is aborded
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
     * if return error message, modify is aborded
     *
     * @return string error message, if no error empty string
     * @see Modify()
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
     * @see Modify()
     */
    public function postUpdate()
    {
        return '';
        // This function should be replaced by the Child Class
    }

    /**
     * if return error message, deletion is aborded
     *
     * @see delete
     * @return string
     */
    public function preDelete()
    {
        return '';
        // This function should be replaced by the Child Class
    }

    /**
     * Method use after delete method
     *
     * @see delete
     * @return string
     */
    public function postDelete()
    {
        return '';
        // This function should be replaced by the Child Class
    }

    /**
     * Method use before select method
     *
     * @param mixed $id the id use by select
     *
     * @see select
     * @return string
     */
    public function preSelect($id)
    {
        // This function should be replaced by the Child Class
        return '';
    }

    /**
     * Method use after select method
     *
     * @param mixed $id the id use by select
     *
     * @see select
     * @return string
     */
    public function postSelect($id)
    {
        return '';
        // This function should be replaced by the Child Class
    }

    /**
     * Add the object to the database
     *
     * @param bool $nopost PostInsert method not apply if true
     * @param bool $nopre  PreInsert method not apply if true
     *
     * @return string error message, if no error empty string
     * @see PreInsert()
     * @see PostInsert()
     */
    public function Add($nopost = false, $nopre = false)
    {
        if ($this->dbid == -1) {
            return false;
        }
        $msg = '';
        if (!$nopre) {
            $msg = $this->PreInsert();
        }
        if ($msg != '') {
            return $msg;
        }

        $sfields = implode(",", $this->fields);
        $sql = "insert into " . $this->dbtable . "($sfields) values (";

        $valstring = "";
        reset($this->fields);
        foreach ($this->fields as $k => $v) {
            $valstring = $valstring . $this->lw(isset($this->$v) ? $this->$v : '') . ",";
        }
        $valstring = substr($valstring, 0, strlen($valstring) - 1);
        $sql = $sql . $valstring . ")";
        // requery execution
        $msg_err = $this->exec_query($sql);

        if ($msg_err != '') {
            return $msg_err;
        }
        $this->isset = true;
        if (!$nopost) {
            $msg = $this->PostInsert();
        }
        return $msg;
    }

    /**
     * Save the object to the database
     *
     * @param bool   $nopost  PostUpdate() and method not apply if true
     * @param string $sfields only this column will ne updated if empty all fields
     * @param bool   $nopre   PreUpdate() method not apply if true
     *
     * @return string error message, if no error empty string
     * @see PreUpdate()
     * @see PostUpdate()
     */
    public function modify($nopost = false, $sfields = "", $nopre = false)
    {
        $msg = '';
        if ($this->dbid == -1) {
            return false;
        }
        if (!$nopre) {
            $msg = $this->PreUpdate();
        }
        if ($msg != '') {
            return $msg;
        }
        $sql = "update " . $this->dbtable . " set ";

        $nb_keys = 0;

        if (!is_array($sfields)) {
            $fields = $this->fields;
        } else {
            $fields = $sfields;
            foreach ($this->id_fields as $k => $v) {
                $fields[] = $v;
            }
        }

        $wstr = "";
        foreach ($this->id_fields as $k => $v) {
            $notset[$v] = "Y";
            $nb_keys++;
            $val = pg_escape_string($this->$v);
            $wstr = $wstr . " " . $v . "=E'" . $val . "' AND";
        }

        $setstr = "";
        foreach ($fields as $k => $v) {
            if (!isset($notset[$v])) {
                $setstr = $setstr . " " . $v . "=" . $this->lw(isset($this->$v) ? $this->$v : '') . ",";
            }
        }
        $setstr = substr($setstr, 0, strlen($setstr) - 1);
        $wstr = substr($wstr, 0, strlen($wstr) - 3);
        $sql .= $setstr;
        if ($nb_keys > 0) {
            $sql .= " where " . $wstr . ";";
        }

        $msg_err = $this->exec_query($sql);
        // sortie
        if ($msg_err != '') {
            return $msg_err;
        }

        if (!$nopost) {
            $msg = $this->PostUpdate();
        }

        return $msg;
    }

    /**
     * Delete the object on the database
     *
     * @param bool $nopost PostUpdate() and method not apply if true
     *
     * @return string error message, if no error empty string
     */
    public function delete($nopost = false)
    {
        $msg = $this->PreDelete();
        if ($msg != '') {
            return $msg;
        }
        $wherestr = "";
        $count = 0;

        reset($this->id_fields);
        foreach ($this->id_fields as $k => $v) {
            if ($count > 0) {
                $wherestr = $wherestr . " AND ";
            }
            $wherestr = $wherestr . "( " . $v . "=E'" . pg_escape_string($this->$v) . "' )";
            $count++;
        }
        // suppression de l'enregistrement
        $sql = "delete from " . $this->dbtable . " where " . $wherestr . ";";

        $msg_err = $this->exec_query($sql);

        if ($msg_err != '') {
            return $msg_err;
        }

        if (!$nopost) {
            $msg = $this->PostDelete();
        }
        return $msg;
    }

    /**
     * Add several objects to the database
     * no post neither preInsert are called
     *
     * @param      $tcopy
     * @param bool $nopost PostInsert method not apply if true
     *
     * @return string error message, if no error empty string
     * @see PreInsert()
     * @see PostInsert()
     */
    public function Adds(&$tcopy, $nopost = false)
    {
        if ($this->dbid == -1) {
            return false;
        }
        if (!is_array($tcopy)) {
            return false;
        }
        $msg = '';

        $trow = array();
        foreach ($tcopy as $kc => $vc) {
            $trow[$kc] = "";
            foreach ($this->fields as $k => $v) {
                $trow[$kc] .= "" . ((isset($vc[$v])) ? $vc[$v] : ((($this->$v) != '') ? $this->$v : '\N')) . "\t";
            }
            $trow[$kc] = substr($trow[$kc], 0, -1);
        }
        // query execution
        $berr = pg_copy_from($this->dbid, $this->dbtable, $trow, "\t");

        if (!$berr) {
            return sprintf(_("DbObj::Adds error in multiple insertion"));
        }

        if (!$nopost) {
            $msg = $this->PostInsert();
        }
        return $msg;
    }

    public function lw($prop)
    {
        $result = (($prop == '') && ($prop !== 0)) ? "null" : "E'" . pg_escape_string($prop) . "'";
        return $result;
    }

    public function CloseConnect()
    {
        pg_close("$this->dbid");
        return true;
    }

    public function Create($nopost = false)
    {
        $msg = "";
        if (isset($this->sqlcreate)) {
            // step by step
            if (is_array($this->sqlcreate)) {
                foreach ($this->sqlcreate as $k => $sqlquery) {
                    $msg .= $this->exec_query($sqlquery, 1);
                }
            } else {
                $sqlcmds = explode(";", $this->sqlcreate);
                foreach ($sqlcmds as $k => $sqlquery) {
                    $msg .= $this->exec_query($sqlquery, 1);
                }
            }
            $this->log->debug("DbObj::Create : " . print_r($this->sqlcreate, true));
        }
        if (isset($this->sqlinit)) {
            $msg = $this->exec_query($this->sqlinit, 1);
            $this->log->debug("Init : {$this->sqlinit}");
        }
        if ($msg != '') {
            $this->log->info("DbObj::Create $msg");
            return $msg;
        }
        if (!$nopost) {
            $this->PostInit();
        }
        return ($msg);
    }

    public function postInit()
    {
    }

    public function init_dbid()
    {
        if ($this->dbaccess == "") {
            // don't test if file exist or must be searched in include_path
            $this->dbaccess = \Anakeen\Core\DbManager::getDbAccess();
        }
        $this->dbid = \Anakeen\Core\DbManager::getDbid();
        if ($this->dbid == 0) {
            error_log(__METHOD__ . "null dbid");
        }
        return $this->dbid;
    }

    protected function tryCreate()
    {
        $this->err_code = pg_result_error_field($this->res, PGSQL_DIAG_SQLSTATE);

        $action_needed = "";

        if ($this->err_code != "") {
            // http://www.postgresql.org/docs/8.3/interactive/errcodes-appendix.html
            switch ($this->err_code) {
                case "42P01":
                    // UNDEFINED TABLE
                    $action_needed = "create";
                    break;

                case "42703":
                    // UNDEFINED COLUMN
                    $action_needed = "update";
                    break;

                case "42P07":
                    // DUPLICATE TABLE
                    $action_needed = "none";
                    break;

                default:
                    break;
            }
        }

        $originError = $this->msg_err;
        switch ($action_needed) {
            case "create":
                $st = $this->Create();
                if ($st == "") {
                    return true;
                } else {
                    $err = \ErrorCode::getError('DB0003', $this->dbtable, $st);
                    $this->msg_err = $originError . "\n" . $err;
                }
                break;

            case "update":
                return false;
                // no more auto update
                /*
                $st = $this->Update();
                if ($st == "") {
                    return true;
                } else {
                
                    $err= \ErrorCode::getError('DB0004', $this->dbtable, $st);
                    $this->msg_err = $originError . "\n" . $err;
                }
                */
                break;

            case "none":
                $this->msg_err = "";
                return true;
                break;

            default:
                break;
        }
        return false;
    }

    /**
     * Send a request to database
     *
     * @param string $sql     the query
     * @param int    $lvl     level set to 0 (internal purpose only)
     * @param bool   $prepare set to true to use pg_prepare, restrict to use single query
     *
     * @throw Dcp\Db\Exception if query fail
     * @return string error message if not strict mode
     */
    public function exec_query($sql, $lvl = 0, $prepare = false)
    {
        global $SQLDELAY, $SQLDEBUG;

        if ($sql == "") {
            return '';
        }
        $sqlt1 = '';
        if ($SQLDEBUG) {
            $sqlt1 = microtime();
        } // to test delay of request
        $this->init_dbid();
        $this->log->debug("exec_query : $sql");
        $this->msg_err = $this->err_code = '';
        if ($prepare) {
            if (pg_send_prepare($this->dbid, '', $sql) === false) {
                $this->msg_err = \ErrorCode::getError('DB0006', pg_last_error($this->dbid));
                error_log(__METHOD__ . " " . $this->msg_err);
                return $this->msg_err;
            }
            $this->res = pg_get_result($this->dbid);
            $err = pg_result_error($this->res);
            if ($err) {
                $this->msg_err= \ErrorCode::getError('DB0005', $err);
                $this->err_code = pg_result_error_field($this->res, PGSQL_DIAG_SQLSTATE);
            }

            if ($this->msg_err == "") {
                if (pg_send_execute($this->dbid, '', array()) === false) {
                    $this->msg_err= \ErrorCode::getError('DB0007', pg_last_error($this->dbid));
                    $this->setError($sql);

                    return $this->msg_err;
                }
                $this->res = pg_get_result($this->dbid);
                $err = pg_result_error($this->res);
                if ($err) {
                    $this->msg_err= \ErrorCode::getError('DB0002', $err);
                    $this->err_code = pg_result_error_field($this->res, PGSQL_DIAG_SQLSTATE);
                }
            }
        } else {
            if (pg_send_query($this->dbid, $sql) === false) {
                $this->msg_err= \ErrorCode::getError('DB0008', pg_last_error($this->dbid));

                $this->setError($sql);
                return $this->msg_err;
            }
            $this->res = pg_get_result($this->dbid);
            /** @noinspection PhpStatementHasEmptyBodyInspection */
            while (pg_get_result($this->dbid)) {
                ;
            } // purge following queries
            $err = pg_result_error($this->res);
            if ($err) {
                $this->msg_err= \ErrorCode::getError('DB0001', $err);
                $this->err_code = pg_result_error_field($this->res, PGSQL_DIAG_SQLSTATE);
            }
        }

        if ($this->msg_err && ($lvl == 0)) {
            $orierr = $this->msg_err;
            try {
                if ($this->tryCreate()) {
                    // redo the query if create table is done
                    $this->msg_err = $this->exec_query($sql, 1, $prepare);
                }
            } catch (\Exception $e) {
                $this->msg_err = $orierr;
            }
        }
        if ($this->msg_err != "") {
            $this->log->warning("exec_query :" . $sql);
            $this->log->warning("PostgreSQL Error : " . $this->msg_err);
            //trigger_error('<pre>'.$this->msg_err."\n".$sql.'</pre>');
            // throw new Exception($this->msg_err);
            $this->setError($sql);
        }

        if ($SQLDEBUG) {
            global $TSQLDELAY;
            $SQLDELAY += microtime_diff(microtime(), $sqlt1); // to test delay of request
            $TSQLDELAY[] = array(
                "t" => sprintf("%.04f", microtime_diff(microtime(), $sqlt1)),
                "s" => str_replace(array(
                    "from",
                    'where'
                ), array(
                    "\nfrom",
                    "\nwhere"
                ), $sql),
                "st" => getDebugStack(1)
            );
        }

        return ($this->msg_err);
    }

    /**
     * number of return rows after exec_query
     *
     * @see exec_query
     * @return int
     */
    public function numrows()
    {
        if ($this->msg_err == "") {
            return (pg_num_rows($this->res));
        } else {
            return (0);
        }
    }

    public function fetch_array($c, $type = PGSQL_ASSOC)
    {
        return (pg_fetch_array($this->res, $c, $type));
    }

    public function update()
    {
        $err= \ErrorCode::getError('DB0009', $this->dbtable);

        return $err;
    }

    public function setError($moreerr = '')
    {
        if ($moreerr == '') {
            $err = $this->msg_err;
        } else {
            $err = $this->msg_err . "\n" . $moreerr . "\n";
        }
        if (self::$sqlStrict) {
            throw new \Dcp\Db\Exception($err);
        }
        logDebugStack(2, $err);
    }

    /**
     * @deprecated not used now
     * @return string
     */
    public function autoUpdate()
    {
        print $this->msg_err;
        print (" - need update table " . $this->dbtable);
        $this->log->error("need Update table " . $this->dbtable);

        $this->log->info("Update table " . $this->dbtable);
        // need to exec altering queries
        $objupdate = new DbObj($this->dbaccess);
        // ------------------------------
        // first : save table to updated
        $dumpfile = uniqid(ContextManager::getTmpDir() . "/" . $this->dbtable);
        $err = $objupdate->exec_query("COPY " . $this->dbtable . "  TO '" . $dumpfile . "'");
        $this->log->info("Dump table " . $this->dbtable . " in " . $dumpfile);

        if ($err != "") {
            return ($err);
        }
        // ------------------------------
        // second : rename table to save data
        //$err = $objupdate-> exec_query("CREATE  TABLE ".$this->dbtable."_old ( ) INHERITS (".$this->dbtable.")",1);
        //$err = $objupdate-> exec_query("COPY ".$this->dbtable."_old FROM '".$dumpfile."'",				1 );
        $err = $objupdate->exec_query("ALTER TABLE " . $this->dbtable . " RENAME TO " . $this->dbtable . "_old", 1);

        if ($err != "") {
            return ($err);
        }
        // remove index : will be recreated in the following step (create)
        $this->exec_query("select indexname from pg_indexes where tablename='" . $this->dbtable . "_old'", 1);
        $nbidx = $this->numrows();
        for ($c = 0; $c < $nbidx; $c++) {
            $row = $this->fetch_array($c, PGSQL_ASSOC);
            $objupdate->exec_query("DROP INDEX " . $row["indexname"], 1);
        }
        // --------------------------------------------
        // third : Create new table with new attributes
        $this->Create(true);
        // ---------------------------------------------------
        // 4th : copy compatible data from old table to new table
        $first = true;
        $fields = '';
        $this->exec_query("SELECT * FROM " . $this->dbtable . "_old");
        $nbold = $this->numrows();
        for ($c = 0; $c < $nbold; $c++) {
            $row = $this->fetch_array($c, PGSQL_ASSOC);

            if ($first) {
                // compute compatible fields
                $inter_fields = array_intersect(array_keys($row), $this->fields);
                reset($this->fields);
                $fields = "(";
                foreach ($inter_fields as $k => $v) {
                    $fields .= $v . ",";
                }
                $fields = substr($fields, 0, strlen($fields) - 1); // remove last comma
                $fields .= ")";
                $first = false;
            }
            // compute compatible values
            $values = "(";
            reset($inter_fields);
            foreach ($inter_fields as $k => $v) {
                $values .= "E'" . pg_escape_string($row[$v]) . "',";
            }
            $values = substr($values, 0, strlen($values) - 1); // remove last comma
            $values .= ")";
            // copy compatible values
            $sqlInsert = sprintf("INSERT INTO %s %s VALUES ", $this->dbtable, $fields, $values);
            $err = $objupdate->exec_query($sqlInsert, 1);
            if ($err != "") {
                return ($err);
            }
        }
        // ---------------------------------------------------
        // 5th :delete old table (has been saved before - dump file)
        $err = $objupdate->exec_query("DROP TABLE " . $this->dbtable . "_old", 1);

        return ($err);
    }
}
