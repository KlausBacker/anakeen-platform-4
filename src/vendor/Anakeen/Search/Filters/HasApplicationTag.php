<?php

namespace Anakeen\Search\Filters;


class HasApplicationTag extends StandardAttributeFilter implements ElementSearchFilter
{
    protected $value = null;
    protected $compatibleType = array(
        'text'
    );
    public function __construct($value)
    {
        parent::__construct('atags');
        $this->value = $value;
    }
    /**
     * Generate sql part
     * @param \SearchDoc $search
     * @throws Exception
     * @return string sql where condition
     */
    public function addFilter(\SearchDoc $search)
    {
        $sql = sprintf("atags->'%s' is not null", pg_escape_string($this->value));
        $search->addFilter($sql);
        return $this;
    }
}
