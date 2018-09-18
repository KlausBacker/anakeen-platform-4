<?php

namespace Anakeen\Search\Filters;

use Anakeen\Core\SmartStructure\NormalAttribute;
use Anakeen\Search;

class OneGreaterThan extends StandardAttributeFilter implements ElementSearchFilter
{
    const EQUAL = 1;
    const ALL = 2;
    protected $EQUAL = false;
    protected $ALL = false;
    protected $compatibleType = array(
        'int',
        'double',
        'money',
        'date',
        'timestamp',
        'time'
    );
    protected $value = null;
    public function __construct($attrId, $value)
    {
        parent::__construct($attrId);
        $this->value = $value;
        $argv = func_get_args();
        array_splice($argv, 0, 2);
        if (isset($argv[0])) {
            $this->EQUAL = $this->EQUAL | ($argv[0] & self::EQUAL);
            $this->ALL = $this->ALL | ($argv[0] & self::ALL);
        }
    }
    public function verifyCompatibility(\SearchDoc & $search)
    {
        $attr = parent::verifyCompatibility($search);
        if (!$attr->isMultiple()) {
            throw new Exception("FLT0007", $attr->id);
        }
        return $attr;
    }
    /**
     * Generate sql part
     * @param \SearchDoc $search
     * @throws Exception
     * @return string sql where condition
     */
    public function addFilter(\SearchDoc $search)
    {
        $attr = $this->verifyCompatibility($search);
        $search->addFilter($this->_filter($attr, $this->value));
        return $this;
    }
    protected function _filter(NormalAttribute & $attr, $value)
    {
        $sql = sprintf("%s IS NOT NULL AND %s <%s %s(%s)", pg_escape_identifier($attr->id) , pg_escape_literal($value) , ($this->EQUAL ? '=' : '') , ($this->ALL ? 'ALL' : 'ANY') , pg_escape_identifier($attr->id));
        return $sql;
    }
}
