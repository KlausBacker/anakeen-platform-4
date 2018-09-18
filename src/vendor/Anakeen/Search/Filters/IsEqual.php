<?php

namespace Anakeen\Search\Filters;

use Anakeen\Core\Internal\SmartElement;

class IsEqual extends StandardAttributeFilter implements ElementSearchFilter
{
    protected $value = null;
    public function __construct($attrId, $value)
    {
        parent::__construct($attrId);
        $this->value = $value;
    }
    public function verifyCompatibility(\SearchDoc & $search)
    {
        $attr = parent::verifyCompatibility($search);
        if (is_array($this->value)) {
            if (!$attr->isMultiple()) {
                throw new Exception("FLT0005");
            }
            $this->value = SmartElement::arrayToRawValue($this->value);
        } else if (!is_scalar($this->value)) {
            throw new Exception("FLT0004");
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
        $this->verifyCompatibility($search);
        $search->addFilter(sprintf('%s = %s', pg_escape_identifier($this->attributeId) , pg_escape_literal($this->value)));
        return $this;
    }
}
