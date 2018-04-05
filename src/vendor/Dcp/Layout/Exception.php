<?php

namespace Dcp\Layout;

class Exception extends \Dcp\Exception
{
    /**
     * @var string corrupted file which content generation
     */
    protected $corruptedFile = '';

    /**
     * get Corrupted file : partial layout generation
     *
     * @return string
     */
    public function getCorruptedFile()
    {
        return $this->corruptedFile;
    }

    /**
     * @param string $cf Corrupted file to set
     *
     * @return mixed
     */
    public function setCorruptedFile($cf)
    {
        return $this->corruptedFile = $cf;
    }

    public function __construct($code, $message, $corruptedFile = '')
    {
        $this->corruptedFile = $corruptedFile;
        parent::__construct($code, $message);
    }
}
