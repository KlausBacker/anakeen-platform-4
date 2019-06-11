<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * to record timer attached to documents
 *
 * @author  Anakeen
 * @version $Id: Class.DocTimer.php,v 1.7 2009/01/07 18:04:27 eric Exp $
 * @package FDL
 */

/**
 */
class DocTimer extends \Anakeen\Core\Internal\DbObj
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
    public $originid;
    public $fromid;
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


    /**
     * delete all timers which comes from same origin
     * @param int $docid    initial doc identifier to detach
     * @param int $originid initial origin id
     * @param int &$c       count of deletion
     * @return string error - empty if no error -
     */
    public function unattachFromOrigin($docid, $originid, &$c = 0)
    {
        $docid = intval($docid);
        $originid = intval($originid);
        $err = "";
        if ($docid === 0) {
            $err = _("cannot detach : document id is not set");
        }
        if ($originid === 0) {
            $err .= _("cannot detach : origin id is not set");
        }
        if ($err == "") {
            $q = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
            $q->addQuery("docid=$docid");
            $q->addQuery("tododate is not null");
            $q->addQuery("originid=$originid");
            $c = $q->count();

            $err = $this->query("delete from doctimer where docid=$docid and originid=$originid and tododate is not null");
        }
        return $err;
    }

    /**
     * delete all timers for a document
     * @param int $docid initial doc identifier to detach
     * @param int &$c    count of deletion
     * @return string error - empty if no error -
     */
    public function unattachAll($docid, &$c)
    {
        $docid = intval($docid);
        $err = "";
        if ($docid === 0) {
            $err = _("cannot detach : document id is not set");
        }
        if ($err === "") {
            $q = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
            $q->addQuery("docid=$docid");
            $q->addQuery("tododate is not null");
            $c = $q->count();

            $err = $this->query("delete from doctimer where docid=$docid and tododate is not null");
        }
        return $err;
    }

    /**
     * delete a specific timer for a document
     * @param int $docid   initial doc identifier to detach
     * @param int $timerid timerc identifier to detach
     * @return string error - empty if no error -
     */
    public function unattachDocument($docid, $timerid)
    {
        $docid = intval($docid);
        $timerid = intval($timerid);
        $err = "";
        if ($docid == 0) {
            $err = _("cannot detach : document id is not set");
        }
        if ($timerid == 0) {
            $err = _("cannot detach : timer id is not set");
        }
        if ($err == "") {
            $err = $this->query("delete from doctimer where docid=$docid and tododate is not null and timerid=$timerid");
        }
        return $err;
    }



    public function executeTimerNow()
    {
        $timer = \Anakeen\Core\SEManager::getDocument($this->timerid);
        /**
         * @var \Anakeen\SmartStructures\Timer\TimerHooks $timer
         */
        if (!$timer || !$timer->isAlive()) {
            return sprintf(_("cannot execute timer : timer %s is not found"), $this->timerid);
        }
        $err = $timer->executeTask(unserialize($this->actions), $this->docid, $msg);
        $this->donedate = date('Y-m-d H:i:s');
        $this->result = $msg;
        $err .= $this->modify();

        return $err;
    }
}
