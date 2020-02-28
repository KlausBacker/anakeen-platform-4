<?php

namespace Anakeen\Search\Filters;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SmartStructure\NormalAttribute;
use Anakeen\Search\SearchCriteria\SearchCriteriaTrait;

/**
 * Class OneEqualsMulti
 *
 * Filter for multiple fields and multiple values
 * Verify if the field value is included in a set of filter values
 * e.g. [B, D] oneEqualsMulti [A, B, C]
 */
class OneEqualsMulti extends StandardAttributeFilter implements ElementSearchFilter
{

    use SearchCriteriaTrait;

    const NOT = 1;
    const ALL = 2;

    public static function getOptionMap()
    {
        return array(
            self::NOT => "not",
            self::ALL => "all",
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
    protected $ALL = false;
    protected $value = null;

    /**
     * OneEqualsMulti constructor.
     * @param string $attrId
     * @param array $value
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\OneEqualsMulti::$NOT</b>,
     * <b>\Anakeen\Search\Filters\OneEqualsMulti::$ALL</b>,
     * </p>
     */
    public function __construct($attrId, $value, $options = 0)
    {
        parent::__construct($attrId);
        $this->value = $value;
        if (isset($options)) {
            $this->NOT = ($options & self::NOT);
            $this->ALL = ($options & self::ALL);
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
        error_log(print_r($this->value, true));
        $attr = $this->verifyCompatibility($search);
        error_log(print_r($this->value[0], true));
        if (is_array($this->value) && isset($this->value[0]) && $this->value[0] !== "") {
            $search->addFilter($this->_filter($attr, $this->value));
        }
        return $this;
    }

    public function verifyCompatibility(\Anakeen\Search\Internal\SearchSmartData &$search)
    {
        $attr =  parent::verifyCompatibility($search);
        if (!$attr->isMultiple()) {
            throw new Exception("FLT0007", $attr->id);
        }
        if (isset($this->value) && !is_array($this->value)) {
//            throw new Exception("FLT0009"); TODO: remove comment
            $this->value = array($this->value);
        }
        return $attr;
    }

    /**
     * @param NormalAttribute $attr
     * @param $value
     * @return string
     */
    protected function _filter(NormalAttribute & $attr, $value)
    {
        $pgArray = SmartElement::arrayToRawValue($value);
        $sql = sprintf("%s IS NOT NULL AND %s && '%s'", pg_escape_identifier($attr->id), pg_escape_identifier($attr->id), $pgArray);
        if ($this->NOT) {
            $sql = sprintf("NOT(%s)", $sql);
        }
        return $sql;
    }
}
