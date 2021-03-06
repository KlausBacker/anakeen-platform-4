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
    const successStatus="success";
    const waitingStatus="waiting";
    const failStatus="failed";
    const expiredStatus="expired";
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
        "donestatus", // done states
        "actions", // actions to execute
        "result"
        // result text

    );
    public $sup_fields = array(
        "id"
    ); // not be in fields auto computed

    /**
     * identifier of timer
     * @var int
     */
    public $id;
    /**
     * comment date to execute
     * @var string
     */
    public $tododate;
    /**
     * level of timer (number of iterations)
     * @var int
     */
    public $level;
    /**
     * Timer identifier
     * @var int $timerid
     */
    public $timerid;
    /**
     * Document identifier
     * @var int $docid
     */
    public $docid;
    /**
     * Reference date to compute process execution date
     * @var string $referencedate
     */
    public $referencedate;
    /**
     * Executed date
     * @var string $donedate
     */
    public $donedate;
    /**
     * Attach date to document
     * @var string $attachdate
     */
    public $attachdate;
    /**
     * Action result
     * @var string $result
     */
    public $result;
    /**
     * Actions to be executed
     * @var string $actions
     */
    public $actions;
    /**
     * @var string success, failed, waiting
     */
    public $donestatus;
    public $originid;
    public $fromid;
    /**
     * Timer title
     * @var string title
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
                   donestatus text,
                   actions text,
                   result text  );
";


    public function preInsert()
    {
        $this->donestatus=self::waitingStatus;
    }

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
    public function unattachDocument(int $docid, int $timerid)
    {
        $docid = intval($docid);
        $timerid = intval($timerid);
        $err = "";
        if ($docid === 0) {
            $err = _("cannot detach : document id is not set");
        }
        if ($timerid === 0) {
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
        try {
            $err = $timer->executeTask(unserialize($this->actions), $this->docid, $msg);
            $this->result = $msg;
            if ($err) {
                 $this->result = sprintf("ERROR: %s.\n%s", $err, $msg);
                $this->donestatus = self::failStatus;
            } else {
                $this->donestatus = self::successStatus;
            }
        } catch (Exception $e) {
            $err = $e->getMessage();
            $this->result = sprintf("ERROR: %s", $err);
        }

        $this->donedate = date('Y-m-d H:i:s');
        $err .= $this->modify();

        return $err;
    }
}
