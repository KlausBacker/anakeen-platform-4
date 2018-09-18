<?php

namespace Anakeen\Search\Filters;

use Anakeen\Search;

class _OneEquals extends StandardAttributeFilter implements ElementSearchFilter
{
    const NOT = 1;
    protected $NOT = false;
    protected $compatibleType = array(
        'text',
        'htmltext',
        'longtext',
        'int',
        'double',
        'money',
        'date',
        'timestamp',
        'time',
        'enum',
        'docid',
        'account'
    );
    protected $value = null;
    public function __construct($attrId, $value)
    {
        parent::__construct($attrId);
        $this->value = $value;
        $argv = func_get_args();
        array_splice($argv, 0, 2);
        if (isset($argv[0])) {
            $this->NOT = ($argv[0] & self::NOT);
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
    protected function _filter(\NormalAttribute & $attr, $value)
    {
        $sql = sprintf("%s IS NOT NULL AND %s = ANY(%s)", pg_escape_identifier($attr->id) , pg_escape_literal($value) , pg_escape_identifier($attr->id));
        if ($this->NOT) {
            $sql = sprintf("NOT(%s)", $sql);
        }
        return $sql;
    }
}
