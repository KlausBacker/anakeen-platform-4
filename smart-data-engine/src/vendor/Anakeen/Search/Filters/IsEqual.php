<?php

namespace Anakeen\Search\Filters;

use Anakeen\Core\Internal\SmartElement;

class IsEqual extends StandardAttributeFilter implements ElementSearchFilter
{
    const NOT = 1;
    protected $NOT = false;
    protected $value = null;

    /**
     * IsEqual constructor.
     * @param $attrId
     * @param $value
     * @param int $options<p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\IsEqual::$NOT</b>,
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

    public function verifyCompatibility(\Anakeen\Search\Internal\SearchSmartData & $search)
    {
        $attr = parent::verifyCompatibility($search);
        if (is_array($this->value)) {
            if (!$attr->isMultiple()) {
                throw new Exception("FLT0005");
            }
            $this->value = SmartElement::arrayToRawValue($this->value);
        } elseif (!is_scalar($this->value)) {
            throw new Exception("FLT0004");
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
        $this->verifyCompatibility($search);
        $query = $this->NOT ? '%s <> %s' : '%s = %s';
        $search->addFilter(sprintf($query, pg_escape_identifier($this->attributeId), pg_escape_literal($this->value)));
        return $this;
    }
}
