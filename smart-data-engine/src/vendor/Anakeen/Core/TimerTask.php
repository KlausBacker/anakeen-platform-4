<?php

namespace Anakeen\Core;

class TimerTask
{
    public $id;
    public $actions;
    public $timerid;
    public $originid;
    public $docid;
    public $title;
    public $fromid;
    public $attachdate;
    public $donedate;
    public $referencedate;
    public $tododate;
    public $result;

    public function __construct(array $values)
    {
        foreach ($values as $k => $v) {
            switch ($k) {
                case "actions":
                    $this->actions = unserialize($v);
                    break;
                default:
                    $this->$k = $v;
            }
        }
    }
}
