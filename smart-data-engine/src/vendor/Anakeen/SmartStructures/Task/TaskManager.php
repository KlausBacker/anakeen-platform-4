<?php

namespace Anakeen\SmartStructures\Task;

use Anakeen\Search\SearchElements;
use SmartStructure\Fields\Task as TaskFields;

class TaskManager
{
    /**
     * @return \Anakeen\Search\ElementList
     */
    public static function getTaskToExecute()
    {
        $s = new SearchElements("TASK");

        $s->addFilter("%s < now()", TaskFields::task_nextdate);
        $s->addFilter("%s = 'active'", TaskFields::task_status);

        $s->search();
        return $s->getResults();
    }
}
