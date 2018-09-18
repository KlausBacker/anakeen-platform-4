<?php

namespace Anakeen\Search\Filters;

use Anakeen\Core\SmartStructure\NormalAttribute;
use Anakeen\Search;

class OneLesserThan extends OneGreaterThan implements ElementSearchFilter
{
    protected function _filter(NormalAttribute & $attr, $value)
    {
        $sql = sprintf("%s IS NOT NULL AND %s >%s %s(%s)", pg_escape_identifier($attr->id) , pg_escape_literal($value) , ($this->EQUAL ? '=' : '') , ($this->ALL ? 'ALL' : 'ANY') , pg_escape_identifier($attr->id));
        return $sql;
    }
}
