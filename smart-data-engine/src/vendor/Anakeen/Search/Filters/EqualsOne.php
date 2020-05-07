<?php

namespace Anakeen\Search\Filters;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SmartStructure\NormalAttribute;
use Anakeen\Search\SearchCriteria\SearchCriteriaTrait;

/**
 * Class EqualsOne
 *
 * Filter for single and multiple values
 * Verify if the field value is included in a set of filter values
 * e.g. B in [A, B, C]
 */
class EqualsOne extends StandardAttributeFilter implements ElementSearchFilter
{
    use SearchCriteriaTrait;

    const NOT = 1;

    public static function getOptionMap()
    {
        return array(
            self::NOT => "not",
        );
    }

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
    protected $NOT = false;
    protected $value = null;

    /**
     * ContainsValues constructor.
     * @param string $attrId
     * @param array $value
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\EqualsOne::$NOT</b>,
     * </p>
     */
    public function __construct($attrId, $value, $options = 0)
    {
        parent::__construct($attrId);
        $this->value = $value;
        if (isset($options)) {
            $this->NOT = ($options & self::NOT);
        }
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
        /**
         * @var NormalAttribute
         */
        $attr = $this->verifyCompatibility($search);

        if (!empty($this->value)) {
            $search->addFilter($this->_filter($attr, $this->value));
        }
        return $this;
    }

    /**
     * @param \Anakeen\Search\Internal\SearchSmartData $search
     * @return NormalAttribute
     * @throws Exception
     */
    public function verifyCompatibility(\Anakeen\Search\Internal\SearchSmartData &$search)
    {
        $attr =  parent::verifyCompatibility($search);
        if ($attr->isMultiple()) {
            throw new Exception("FLT0008", $attr->id);
        }
        if (!is_array($this->value)) {
            throw new Exception("FLT0009");
        }

        return $attr;
    }

    /**
     * @param $attr
     * @param $value
     * @return string
     */
    protected function _filter($attr, $value)
    {
        $pgArray = SmartElement::arrayToRawValue($value);
        $sql = sprintf("%s IS NOT NULL AND ARRAY[%s] <@ '%s'", pg_escape_identifier($attr->id), pg_escape_identifier($attr->id), $pgArray);
        if ($this->NOT) {
            $sql = sprintf("NOT(%s)", $sql);
        }
        return $sql;
    }
}
