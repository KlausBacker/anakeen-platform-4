<?php

namespace Anakeen\Search\Filters;

use Anakeen\Core\SmartStructure\NormalAttribute;

class OneGreaterThan extends StandardAttributeFilter implements ElementSearchFilter
{
    const EQUAL = 1;
    const ALL = 2;
    protected $EQUAL = false;
    protected $ALL = false;
    protected $compatibleType
        = array(
            'int',
            'double',
            'money',
            'date',
            'timestamp',
            'time'
        );
    protected $value = null;

    /**
     * OneGreaterThan constructor.
     * @param $attrId
     * @param $value
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\OneGreaterThan::$EQUAL</b>,
     * <b>\Anakeen\Search\Filters\OneGreaterThan::$ALL</b>,
     * </p>
     */
    public function __construct($attrId, $value, $options = 0)
    {
        parent::__construct($attrId);
        $this->value = $value;
        if (isset($options)) {
            $this->EQUAL = $this->EQUAL | ($options & self::EQUAL);
            $this->ALL = $this->ALL | ($options & self::ALL);
        }
    }

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
        $search->addFilter($this->_filter($attr, $this->value));
        return $this;
    }

    protected function _filter(NormalAttribute & $attr, $value)
    {
        $sql = sprintf(
            "%s IS NOT NULL AND %s <%s %s(%s)",
            pg_escape_identifier($attr->id),
            pg_escape_literal($value),
            ($this->EQUAL ? '=' : ''),
            ($this->ALL ? 'ALL' : 'ANY'),
            pg_escape_identifier($attr->id)
        );
        return $sql;
    }
}
