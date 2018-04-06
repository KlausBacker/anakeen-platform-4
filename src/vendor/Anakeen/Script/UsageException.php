<?php

namespace Anakeen\Script;

class UsageException extends \Dcp\Exception
{
    private $usage = '';

    public function __construct($code, $text, $usage = '')
    {
        parent::__construct($code, $text);
        $this->usage = $usage;
    }

    public function getUsage()
    {
        if ($this->usage) {
            return $this->usage;
        }
        return null;
    }
}
