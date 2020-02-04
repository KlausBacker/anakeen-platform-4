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

    /**
     * ContainsValues constructor.
     * @param string $attrId
     * @param string $value
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\ContainsValues::$NOT</b>,
     * </p>
     */
    public function __construct($attrId, $value, $options = 0)
    {
        parent::__construct($attrId);
        $this->value = $value;
        $argv = func_get_args();
        array_splice($argv, 0, 2);
        if (isset($argv[0])) {
            $this->NOT = ($argv[0] & self::NOT);
        }
    }

    /**
     * @param \Anakeen\Search\Internal\SearchSmartData $search
     * @return NormalAttribute
     * @throws Exception
     */
    public function verifyCompatibility(\Anakeen\Search\Internal\SearchSmartData & $search)
    {
        $attr = parent::verifyCompatibility($search);
        if (!$attr->isMultiple()) {
            throw new Exception("FLT0007", $attr->id);
        }
        return $attr;
    }
    /**
     * Generate sql part
     *
     * @param \Anakeen\Search\Internal\SearchSmartData $search
     *
     * @throws Exception
     * @return string sql where condition
     */
    public function addFilter(\Anakeen\Search\Internal\SearchSmartData $search)
    {
        $attr = $this->verifyCompatibility($search);
        $value = $this->value;
        if (!is_array($value)) {
            $value = [$value];
        }
        $search->addFilter($this->_filter($attr, $value));
        return $this;
    }

    /**
     * @param NormalAttribute $attr
     * @param $value
     * @return string
     */
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
