<?php

namespace Anakeen\Search\Filters;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SmartStructure\NormalAttribute;

/**
 * Class ContainsValues
 *
 * Filter for multiple values
 * Verify if values are included in value set of field
 */
class ContainsValues extends StandardAttributeFilter implements ElementSearchFilter
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
        $value = $this->value;
        if (!is_array($value)) {
            $value = [$value];
        }
        $search->addFilter($this->_filter($attr, $value));
        return $this;
    }
    protected function _filter(NormalAttribute & $attr, $value)
    {
        $pgArray = SmartElement::arrayToRawValue($value);
        $sql = sprintf("%s IS NOT NULL AND %s @> '%s'", pg_escape_identifier($attr->id), pg_escape_identifier($attr->id), $pgArray);
        if ($this->NOT) {
            $sql = sprintf("NOT(%s)", $sql);
        }
        return $sql;
    }
}
