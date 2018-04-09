<?php

namespace Dcp\Utils\CSVFormatDetector;

class Sample
{
    public $str = null;
    public $count = 0;
    public $weight = 1;
    public $score = 0;
    
    public function __construct($str, $count, $weight = 1)
    {
        $this->str = $str;
        $this->count = $count;
        $this->weight = $this->count * $weight;
    }
}


