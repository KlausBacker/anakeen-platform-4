<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * to record timer attached to documents
 *
 * @author Anakeen
 * @version $Id: Class.DocTimer.php,v 1.7 2009/01/07 18:04:27 eric Exp $
 * @package FDL
 */
/**
 */

include_once ("Class.DbObj.php");
class DocTimer extends DbObj
{
    public $fields = array(
        "timerid", // timer id
        "level", // current level
        "originid", // doc which create attach
        "docid", // document attached
        "title", // title document attached
        "fromid", // fromid of docid
        "attachdate", // date of attachement
        "referencedate", // reference date
        "tododate", // date to execute
        "donedate", // executed date
        "actions", // actions to execute
        "result"
        // result text
        
    );
    public $sup_fields = array(
        "id"
    ); // not be in fields auto computed
    
    /**
     * identifier of timer
     * @public int
     */
    public $id;
    /**
     * comment date to execute
     * @public date
     */
    public $tododate;
    /**
     * level of timer (number of iterations)
     * @public int
     */
    public $level;
    /**
     * Timer identifier
     * @public int $timerid
     */
    public $timerid;
    /**
     * Document identifier
     * @public int $docid
     */
    public $docid;
    /**
     * Reference date to compute process execution date
     * @public string $referencedate
     */
    public $referencedate;
    /**
     * Executed date
     * @public string $donedate
     */
    public $donedate;
    /**
     * Attach date to document
     * @public string $attachdate
     */
    public $attachdate;
    /**
     * Action result
     * @public string $result
     */
    public $result;
    /**
     * Actions to be executed
     * @public string $actions
     */
    public $actions;
    /**
     * Timer title
     * @public string title
     */
    public $title;
    public $id_fields = array(
        "id"
    );
    
    public $dbtable = "doctimer";
    
    public $sqlcreate = "
create table doctimer ( id serial,
                   timerid int not null,                  
                   level int not null default 0,
                   originid int,                    
                   docid int not null,            
                   title text,
                   fromid int not null,
                   attachdate timestamp,
                   referencedate timestamp,
                   tododate timestamp,
                   donedate timestamp,
                   actions text,
                   result text  );
";
    
    function preInsert()
    {
        include_once ("Class.QueryDb.php");
        $docid = intval($this->docid);
        $timerid = intval($this->timerid);
        $q = new QueryDb($this->dbaccess, $this->dbtable);
        $q->addQuery("docid=$docid");
        $q->addQuery("tododate is not null");
        $q->addQuery("timerid=$timerid");
        $c = $q->count();
        
        if ($c > 0) return _("timer already set");
        return "";
    }
    /**
     * delete all timers which comes from same origin
     * @param int $docid initial doc identifier to detach
     * @param int $originid initial origin id
     * @param int &$c count of deletion
     * @return string error - empty if no error -
     */
    function unattachFromOrigin($docid, $originid, &$c = 0)
    {
        $docid = intval($docid);
        $originid = intval($originid);
        $err = "";
        if ($docid == 0) $err = _("cannot detach : document id is not set");
        if ($originid == 0) $err.= _("cannot detach : origin id is not set");
        if ($err == "") {
            $q = new QueryDb($this->dbaccess, $this->dbtable);
            $q->addQuery("docid=$docid");
            $q->addQuery("tododate is not null");
            $q->addQuery("originid=$originid");
            $c = $q->count();
            
            $err = $this->exec_query("delete from doctimer where docid=$docid and originid=$originid and tododate is not null");
        }
        return $err;
    }
    /**
     * delete all timers for a document
     * @param int $docid initial doc identifier to detach
     * @param int &$c count of deletion
     * @return string error - empty if no error -
     */
    function unattachAll($docid, &$c)
    {
        $docid = intval($docid);
        $err = "";
        if ($docid == 0) $err = _("cannot detach : document id is not set");
        if ($err == "") {
            $q = new QueryDb($this->dbaccess, $this->dbtable);
            $q->addQuery("docid=$docid");
            $q->addQuery("tododate is not null");
            $c = $q->count();
            
            $err = $this->exec_query("delete from doctimer where docid=$docid and tododate is not null");
        }
        return $err;
    }
    /**
     * delete a specific timer for a document
     * @param int $docid initial doc identifier to detach
     * @param int $timerid timerc identifier to detach
     * @return string error - empty if no error -
     */
    function unattachDocument($docid, $timerid)
    {
        $docid = intval($docid);
        $timerid = intval($timerid);
        $err = "";
        if ($docid == 0) $err = _("cannot detach : document id is not set");
        if ($timerid == 0) $err = _("cannot detach : timer id is not set");
        if ($err == "") $err = $this->exec_query("delete from doctimer where docid=$docid and tododate is not null and timerid=$timerid");
        return $err;
    }
    /**
     * get all actions need to be executed now
     */
    function getActionsToExecute()
    {
        $q = new QueryDb($this->dbaccess, "DocTimer");
        $q->addQuery("tododate is not null");
        $q->addQuery("tododate < now()");
        $timerhourlimit = getParam("FDL_TIMERHOURLIMIT", 2);
        if ((int)$timerhourlimit <= 0) {
            $timerhourlimit = 2;
        }
        $q->addQuery(sprintf("tododate > now() - interval '%d hour'", $timerhourlimit));
        $l = $q->Query(0, 0, "TABLE");
        if ($q->nb > 0) return $l;
        return array();
    }
    
    function executeTimerNow()
    {
        $timer = new_doc($this->dbaccess, $this->timerid);
        /**
         * @var \Dcp\Family\TIMER $timer
         */
        if (!$timer->isAlive()) return sprintf(_("cannot execute timer : timer %s is not found") , $this->timerid);
        
        $err = $timer->executeLevel($this->level, $this->docid, $msg, $gonextlevel);
        if ($gonextlevel) {
            $yetalivetimer = new DocTimer($this->dbaccess, $this->id);
            if ($yetalivetimer->isAffected()) {
                $this->donedate = $timer->getTimeDate();
                $this->tododate = "";
                $this->result = $msg;
                $err = $this->modify();
                $this->id = "";
                $this->level++;
                $acts = $timer->getPrevisions($this->referencedate ? $this->referencedate : $this->attachdate, false, $this->level, 1);
                if (count($acts) == 1) {
                    $act = current($acts);
                    if ($act["execdate"]) {
                        $this->donedate = '';
                        $this->result = '';
                        $this->tododate = $act["execdate"];
                        $this->actions = serialize($act["actions"]);
                        $err = $this->Add();
                    }
                }
            }
        }
        return $err;
    }
}
?>