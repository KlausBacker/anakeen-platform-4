<?php

namespace Anakeen\Search\Filters;

use Anakeen\Search;

class OneEquals extends ContainsValues implements ElementSearchFilter
{
    public function verifyCompatibility(\SearchDoc & $search)
    {
        $attr = parent::verifyCompatibility($search);
        if (is_array($this->value)) {
            throw new Exception("FLT0009");
        }
        return $attr;
    }
}
