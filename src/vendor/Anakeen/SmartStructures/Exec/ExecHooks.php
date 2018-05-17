<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * Method for processes family
 *
 */

namespace Anakeen\SmartStructures\Exec;

use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Script\ShellManager;
use Anakeen\SmartHooks;

class ExecHooks extends \Anakeen\SmartStructures\Document
{
    private $execuserid;

    /**
     * execute the action describe in the object
     *
     * @apiExpose
     *
     * @param string $comment
     *
     * @return int shell status (0 means OK).
     */
    public function bgExecute($comment = "")
    {
        /**
         * @var \Anakeen\Core\Internal\Action $action
         */
        global $action;

        if (!$this->canExecuteAction()) {
            \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("Error : need edit privilege to execute")));
        } else {
            return $this->_execute($action, $comment);
        }
        return -2;
    }

    /**
     * cancel next execution
     *
     * @apiExpose
     * @return string
     */
    public function resetExecute()
    {
        $this->clearValue("exec_status");
        $this->clearValue("exec_statusdate");
        $err = $this->modify();
        return $err;
    }

    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            $this->setValue("exec_nextdate", $this->getNextExecDate());
        });
    }

    /**
     * return the wsh command which be send
     */
    public function bgCommand($masteruserid = false)
    {
        $bgapp = $this->getRawValue("exec_application");
        $bgact = $this->getRawValue("exec_action");
        $bgapi = $this->getRawValue("exec_api");

        $tp = $this->getArrayRawValues("exec_t_parameters");

        $cmd = ShellManager::getAnkCmd(true);
        if ($masteruserid) {
            $fuid = $this->getRawValue("exec_iduser");
            $fu = SEManager::getRawDocument($fuid);
            $wuid = $fu["us_whatid"];
            $this->execuserid = $fuid;
        } else {
            $wuid = ContextManager::getCurrentUser()->id;
            $this->execuserid = $this->getUserId();
        }
        $cmd .= " --login=$wuid";
        if (!$bgapi) {
            $cmd .= sprintf(" --app=%s --action=%s", escapeshellarg($bgapp), escapeshellarg($bgact));
        } else {
            $cmd .= sprintf(" --script=%s", escapeshellarg($bgapi));
        }

        foreach ($tp as $k => $v) {
            $b = sprintf(" --%s=%s", escapeshellarg($v["exec_idvar"]), escapeshellarg($v["exec_valuevar"]));
            $cmd .= $b;
        }
        return $cmd;
    }

    /**
     * return the document user id for the next execution
     *
     * @return string
     */
    public function getExecUserID()
    {
        return $this->execuserid;
    }

    /**
     * return the next date to execute process
     *
     * @return string date
     */
    public function getNextExecDate()
    {
        $ndh = $this->getRawValue("exec_handnextdate");
        if ($ndh == "") {
            $nday = intval($this->getRawValue("exec_periodday", 0));
            $nhour = intval($this->getRawValue("exec_periodhour", 0));
            $nmin = intval($this->getRawValue("exec_periodmin", 0));
            if (($nday + $nhour + $nmin) > 0) {
                $ndh = $this->getDate($nday, $nhour, $nmin);
            } else {
                $ndh = " ";
            }
        }

        return $ndh;
    }

    public function getPrevExecDate()
    {
        if ($this->revision > 0) {
            $pid = $this->getLatestId(true);
            $td = getTDoc($this->dbaccess, $pid);
            $ndh = getv($td, "exec_date");

            return $ndh;
        }
        return '';
    }


    public function canExecuteAction()
    {
        $err = $this->control('edit');
        return ($err == "");
    }

    /**
     * return true if the date is in the future (one day after at less)
     *
     * @param string $date date JJ/MM/AAAA or AAAA-MM-DD
     *
     * @return array
     */
    public static function isFutureDate($date)
    {
        $err = "";
        $sug = array(); // suggestions
        if ($date != "") {
            $date = stringDateToIso($date);
            if (!preg_match("|^[0-9]{4}-[0-9]{2}-[0-9]{2}|", $date)) {
                $err = _("the date syntax must be like : AAAA-MM-DD");
            } else {
                list($yy, $mm, $dd) = explode("-", $date);
                $yy = intval($yy);
                $mm = intval($mm);
                $dd = intval($dd);
                $ti = mktime(0, 0, 0, $mm, $dd + 1, $yy);
                if ($ti < time()) {
                    $err = sprintf(_("the date %s is in the past: today is %s"), date("d/m/Y", mktime(0, 0, 0, $mm, $dd, $yy)), date("d/m/Y", time()));
                    $sug[] = date("d/m/Y", time());
                }
            }
        }
        return array(
            "err" => $err,
            "sug" => $sug
        );
    }

    public function executeNow()
    {
        /**
         * Logging in bgexecute
         */
        $status = $this->bgExecute(_("dynacase cron try execute"));
        $del = new_Doc($this->dbaccess, $this->getLatestId(false, true));
        /**
         * @var ExecHooks $del
         */
        $del->clearValue("exec_status");
        $del->clearValue("exec_handnextdate");
        $err = $del->store();

        if ($status == 0) {
            print sprintf("Execute %s [%d] (%s) : %s\n", $del->title, $del->id, $del->getRawValue("exec_handnextdate"), $err);
        } else {
            print sprintf("Error executing %s [%d] (%s) : %s (%s)\n", $del->title, $del->id, $del->getRawValue("exec_handnextdate"), $err, $status);
        }
    }

    public function _execute(\Anakeen\Core\Internal\Action & $action, $comment = '')
    {
        \Anakeen\Core\Utils\System::setMaxExecutionTimeTo(3600);
        /*
        $cmd = getWshCmd(true);
        $cmd.= " --api=fdl_execute";
        $cmd.= " --docid=" . $this->id;

        $cmd.= " --userid=" . $this->userid;
        if ($comment != "") $cmd.= " --comment=" . base64_encode($comment); // prevent hack
        */
        $time_start = microtime(true);
        // system($cmd, $status);
        $status = $this->__execute($action, $this->id, $comment);
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        if ($status == 0) {
            \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("Process %s [%d] executed"), $this->title, $this->id));
            $action->log->info(sprintf(_("Process %s [%d] executed in %.03f seconds"), $this->title, $this->id, $time));
        } else {
            \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("Error : Process %s [%d]: status %d"), $this->title, $this->id, $status));
            $action->log->error(sprintf(_("Error : Process %s [%d]: status %d in %.03f seconds"), $this->title, $this->id, $status, $time));
        }
        return $status;
    }

    public function __execute(\Anakeen\Core\Internal\Action & $action, $docid, $comment = '')
    {
        $doc = new_Doc($action->dbaccess, $docid);
        /**
         * @var ExecHooks $doc
         */
        if ($doc->locked == -1) { // it is revised document
            $doc = new_Doc($action->dbaccess, $doc->getLatestId());
        }

        $doc->setValue("exec_status", "progressing");
        $doc->setValue("exec_statusdate", $doc->getTimeDate());
        $doc->modify(true, array(
            "exec_status",
            "exec_statusdate"
        ), true);
        $cmd = $doc->bgCommand($action->user->id == 1);
        $f = uniqid(ContextManager::getTmpDir() . "/fexe");
        $fout = "$f.out";
        $ferr = "$f.err";
        $cmd .= ">$fout 2>$ferr";
        $m1 = microtime();
        system($cmd, $statut);
        $m2 = microtime_diff(microtime(), $m1);
        $ms = gmstrftime("%H:%M:%S", $m2);

        if (file_exists($fout)) {
            $doc->setValue("exec_detail", file_get_contents($fout));
            unlink($fout);
        }
        if (file_exists($ferr)) {
            $doc->setValue("exec_detaillog", file_get_contents($ferr));
            unlink($ferr);
        }

        $doc->clearValue("exec_nextdate");
        $doc->setValue("exec_elapsed", $ms);
        $doc->setValue("exec_date", date("d/m/Y H:i "));
        $doc->clearValue("exec_status");
        $doc->clearValue("exec_statusdate");
        $doc->setValue("exec_state", (($statut == 0) ? "OK" : $statut));
        $puserid = $doc->getRawValue("exec_iduser"); // default exec user
        $doc->setValue("exec_iduser", $doc->getExecUserID());
        $doc->refresh();
        $err = $doc->modify();
        if ($err == "") {
            if ($comment != "") {
                $doc->addHistoryEntry($comment);
            }
            $err = $doc->revise(sprintf(_("execution by %s done %s"), $doc->getTitle($doc->getExecUserID()), $statut));
            if ($err == "") {
                $doc->clearValue("exec_elapsed");
                $doc->clearValue("exec_detail");
                $doc->clearValue("exec_detaillog");
                $doc->clearValue("exec_date");
                $doc->clearValue("exec_state");
                $doc->setValue("exec_iduser", $puserid);
                $doc->refresh();
                $err = $doc->modify();
            }
        } else {
            $doc->addHistoryEntry($err, \DocHisto::ERROR);
        }

        if ($err != "") {
            return 1;
        }
        return 0;
    }
}
