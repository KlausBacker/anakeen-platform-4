<?php

namespace Anakeen\Pu\FulltextSearch;

use Anakeen\Fullsearch\ImportSearchConfiguration;
use Anakeen\Pu\Config\TestCaseConfig;

class FulltextSearchConfig extends TestCaseConfig
{

    public static function importSearchConfiguration($xmlData)
    {
        $import=new ImportSearchConfiguration($xmlData);
        $import->import();
    }
}
