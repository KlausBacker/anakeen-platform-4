<?php

namespace Anakeen\Search\Filters;

use Anakeen\Search;

/**
 * Class OneEquals
 *
 * Filter for multiple values
 * Verify if the value is one of values of field
 */
class OneEquals extends ContainsValues implements ElementSearchFilter
{
    public function verifyCompatibility(\Anakeen\Search\Internal\SearchSmartData & $search)
    {
        $attr = parent::verifyCompatibility($search);
        if (is_array($this->value)) {
            throw new Exception("FLT0009");
        }
        return $attr;
    }
}
