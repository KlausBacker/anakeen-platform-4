<?php

namespace Anakeen\Search\Filters;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SmartStructure\NormalAttribute;
use Anakeen\SmartCriteria\SmartCriteriaTrait;

/**
 * Class OneEmpty
 *
 * Filter for multiple values
 * Verify if one of the value is empty
 */
class OneEmpty extends StandardAttributeFilter implements ElementSearchFilter
{

    use SmartCriteriaTrait;

    public static function getOptionMap()
    {
        return array(
            self::NOT => "not",
            self::ALL => "all",
        );
    }

    const NOT = 1;
    const ALL = 2;
    protected $NOT = false;
    protected $ALL = false;
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
     * OneEmpty constructor.
     * @param string $attrId
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\OneEmpty::NOT</b>,
     * <b>\Anakeen\Search\Filters\OneEmpty::ALL</b>,
     * </p>
     */
    public function __construct($attrId, $options = 0)
    {
        parent::__construct($attrId);
        if (isset($options)) {
            $this->NOT = ($options & self::NOT);
            $this->ALL = ($options & self::ALL);
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
        $search->addFilter($this->_filter($attr));
        return $this;
    }

    /**
     * @param NormalAttribute $attr
     * @return string
     */
    protected function _filter(NormalAttribute & $attr)
    {
        return sprintf(
            "%s IS %s NULL %s true = %s(select unnest(%s) IS %s NULL)",
            pg_escape_identifier($attr->id),
            $this->NOT ? 'NOT' : '',
            $this->NOT ? 'AND' : 'OR',
            $this->ALL ? 'ALL' : 'ANY',
            pg_escape_identifier($attr->id),
            $this->NOT ? 'NOT' : ''
        );
    }
}
