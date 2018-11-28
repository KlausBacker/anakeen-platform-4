<?php

/**
 * Timer document
 */

namespace Anakeen\SmartStructures\Timer;

use Anakeen\Core\SEManager;
use Anakeen\SmartHooks;
use SmartStructure\Fields\Timer as TimerField;

class TimerHooks extends \Anakeen\SmartElement
{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            $this->convertRelativeDelay();
        });
    }


    /**
     * Constraint for interval fields
     * @param string $interval
     * @return string
     */
    protected function checkInterval($interval)
    {
        if ($interval) {
            $delay = strtotime($interval);
            if ($delay === false) {
                return sprintf(___("Interval \"%s\" is not correct", "timer"), $interval);
            }
        }
        return "";
    }

    /**
     * use for migrate old relative notation
     */
    protected function convertRelativeDelay()
    {
        $days = $this->getRawValue(TimerField::tm_refdaydelta);
        $hours = $this->getRawValue(TimerField::tm_refhourdelta);
        $interval = $this->getRawValue(TimerField::tm_deltainterval);

        if (($days || $hours) && !$interval) {
            $interval = sprintf("%d days %d hours", $days, $hours);
            $this->setValue(TimerField::tm_deltainterval, $interval);
            $this->clearValue(TimerField::tm_refdaydelta);
            $this->clearValue(TimerField::tm_refhourdelta);
        }

        $days = $this->getMultipleRawValues(TimerField::tm_delay);
        $hours = $this->getMultipleRawValues(TimerField::tm_hdelay);
        $intervals = $this->getMultipleRawValues(TimerField::tm_taskinterval);
        $deltaDay = 0;
        $deltaHour = 0;
        $conversionInterval = false;
        foreach ($days as $k => $day) {
            if (($days[$k] || $hours[$k])) {
                $deltaDay += intval($days[$k]);
                $deltaHour += intval($hours[$k]);
                if (empty($intervals[$k])) {
                    $intervals[$k] = sprintf("%d days %d hours", $deltaDay, $deltaHour);
                    $conversionInterval = true;
                }
            }
        }
        if ($conversionInterval === true) {
            $this->clearValue(TimerField::tm_delay);
            $this->clearValue(TimerField::tm_hdelay);
            $this->setValue(TimerField::tm_taskinterval, $intervals);
        }
    }

    /**
     * attach timer to a document
     *
     * @param \Anakeen\Core\Internal\SmartElement &$doc          the document where timer will be attached
     * @param \Anakeen\Core\Internal\SmartElement &$origin       the document which comes from the attachement
     * @param string                              $referenceDate reference date to trigger the actions
     *
     * @return string error - empty if no error -
     */
    public function attachDocument(&$doc, $origin, $referenceDate = null)
    {
        $dt = new \DocTimer($this->dbaccess);
        $dt->timerid = $this->id;
        $dt->docid = $doc->initid;
        $dt->title = $doc->title;
        $dt->attachdate = date('Y-m-d H:i:s'); // now
        if ($referenceDate === null) {
            $referenceDate = $dt->attachdate;
        }
        $dt->level = 0;
        if ($origin) {
            $dt->originid = $origin->id;
        }
        $dt->fromid = $doc->fromid;

        $err = "";
        $deltaInterval = $this->getRawValue(TimerField::tm_deltainterval);
        $refDate = new \DateTime($referenceDate);
        if ($deltaInterval) {
            $refDate->add(\DateInterval::createFromDateString($deltaInterval));
        }

        $tasks = $this->getArrayRawValues(TimerField::tm_t_config);

        if ((count($tasks) == 0)) {
            $err = sprintf(_("no processes specified in timer %s [%d]"), $this->title, $this->id);
        } else {
            foreach ($tasks as $task) {
                $dt->referencedate = $refDate->format('Y-m-d H:i:s');

                $todoDate = new \DateTime($dt->referencedate);
                if ($task[TimerField::tm_taskinterval]) {
                    $todoDate->add(\DateInterval::createFromDateString($task[TimerField::tm_taskinterval]));
                }
                $dt->tododate = $todoDate->format('Y-m-d H:i:s');
                $dt->actions = serialize([
                    "state" => $task[TimerField::tm_state],
                    "tmail" => array_filter($task[TimerField::tm_tmail], function ($mail) {
                        return !empty($mail);
                    }),
                    "method" => $task[TimerField::tm_method]
                ]);
                $dt->id = '';

                $err .= $dt->add();
            }
        }
        return $err;
    }

    /**
     * unattach timer to a document
     *
     * @param \Anakeen\Core\Internal\SmartElement $doc     the timer document
     * @param \Anakeen\Core\Internal\SmartElement &$origin the document which comes from the attachement
     * @param int                                 $c       count of deletion
     *
     * @return string error - empty if no error -
     * @throws \Dcp\Core\Exception
     */
    public function unattachAllDocument(&$doc, &$origin = null, &$c = 0)
    {

        $dt = new \DocTimer($this->dbaccess);
        if ($origin) {
            $err = $dt->unattachFromOrigin($doc->initid, $origin->initid, $c);
        } else {
            $err = $dt->unattachAll($doc->initid, $c);
        }

        return $err;
    }

    /**
     * unattach timer to a document
     *
     * @param \Anakeen\Core\Internal\SmartElement &$doc the timer document
     *
     * @return string error - empty if no error -
     */
    public function unattachDocument(&$doc)
    {

        $dt = new \DocTimer($this->dbaccess);
        $err = $dt->unattachDocument($doc->initid, $this->id);

        return $err;
    }

    /**
     * execute a level for a document
     *
     * @param array  $actionTodo description of action af the task
     * @param int    $docid      document to apply action
     *
     * @param string $msg        output message
     * @return string error - empty if no error -
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function executeTask($actionTodo, $docid, &$msg = "")
    {
        $msg = '';
        $doc = SEManager::getDocument($docid, true);
        if (!$doc || !$doc->isAlive()) {
            return sprintf(___("cannot execute : document %s is not found", "timer"), $docid);
        }

        $gerr = [];
        $tmsg = array();

        foreach ($actionTodo as $ka => $va) {
            if ($va) {
                switch ($ka) {
                    case "tmail":
                        foreach ($va as $idmail) {
                            /**
                             * @var \SmartStructure\MAILTEMPLATE $tm
                             */
                            $tm = SEManager::getDocument($idmail);
                            if ($tm && $tm->isAlive()) {
                                $msg = sprintf(_("send mail with template %s [%d]"), $tm->title, $tm->id);
                                $doc->addHistoryEntry(sprintf(_("execute timer %s  : %s"), $this->title, $msg));
                                $err = $tm->sendDocument($doc);
                                if ($err) {
                                    $msg .= ": $err";
                                }
                                $tmsg[] = $msg;
                                $gerr[]=$err;
                            }
                        }
                        break;

                    case "state":
                        $msg = sprintf(_("change state to %s"), _($va));
                        $doc->addHistoryEntry(sprintf(_("execute timer %s : %s"), $this->title, $msg));
                        $err = $doc->setState($va);
                        if ($err) {
                            $msg .= ": $err";
                            $gerr[]=$err;
                        }
                        $tmsg[] = $msg;
                        break;

                    case "method":
                        $msg = sprintf(_("apply method %s"), $va);
                        $doc->addHistoryEntry(sprintf(_("execute timer %s : %s"), $this->title, $msg));
                        $ret = $doc->applyMethod($va, "", -1, [], [], $err);
                        if ($err) {
                            $msg .= ": $err";
                            $gerr[]=$err;
                        } elseif ($ret) {
                            $msg .= ": $ret";
                        }
                        $tmsg[] = $msg;
                        break;
                }

                if ($gerr) {
                    $doc->addHistoryEntry(sprintf(_("execute timer %s : %s"), $this->title, implode(", ", $gerr)), \DocHisto::ERROR);
                }
            }
        }

        $msg = implode(".\n", $tmsg);
        return implode("\n", $gerr);
    }
}
