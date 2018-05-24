<?php

namespace Anakeen\Core\Internal;

use Anakeen\Core\ContextManager;
use Monolog\Formatter\LineFormatter;

class LogLineFormatter extends LineFormatter
{

    public function format(array $record)
    {
        $line = parent::format($record);
        $line = str_replace('%user%', ContextManager::getCurrentUser()->login, $line);
        return $line;
    }
}