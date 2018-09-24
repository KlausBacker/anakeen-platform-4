<?php

namespace Anakeen\Search\Filters;

use Anakeen\Core\SmartStructure\NormalAttribute;

class Filename extends StandardAttributeFilter implements ElementSearchFilter
{
    const NOT = 1;
    const EQUAL = 2;
    protected $compatibleType = array(
        'file',
        'image'
    );
    protected $NOT = false;
    protected $EQUAL = false;
    protected $value = null;
    public function __construct($attrId, $value)
    {
        parent::__construct($attrId);
        $this->value = $value;
        $argv = func_get_args();
        array_splice($argv, 0, 2);
        if (isset($argv[0])) {
            $this->NOT = $this->NOT | ($argv[0] & self::NOT);
            $this->EQUAL = $this->EQUAL | ($argv[0] & self::EQUAL);
        }
    }
    public function verifyCompatibility(\SearchDoc & $search)
    {
        $attr = parent::verifyCompatibility($search);
        if (!is_scalar($this->value)) {
            throw new Exception("FLT0006");
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
    protected function _filter(NormalAttribute $attr, $value)
    {
        if ($this->EQUAL) {
            $re = sprintf("E'^[^\\\\|]*\\\\|\\\\d+\\\\|%s$'", pg_escape_string($value));
        } else {
            $re = sprintf("E'^[^\\\\|]*\\\\|\\\\d+\\\\|.*%s'", pg_escape_string($value));
        }
        if ($attr->isMultiple()) {
            $sql = sprintf("%s IS NOT NULL AND %s ~< ANY(%s)", pg_escape_identifier($attr->id) , $re, pg_escape_identifier($attr->id));
        } else {
            $sql = sprintf("%s IS NOT NULL AND %s ~ %s", pg_escape_identifier($attr->id) , pg_escape_identifier($attr->id) , $re);
        }
        if ($this->NOT) {
            $sql = sprintf("NOT(%s)", $sql);
        }
        return $sql;
    }
}
