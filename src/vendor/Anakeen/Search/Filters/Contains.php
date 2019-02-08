<?php

namespace Anakeen\Search\Filters;

class Contains extends StandardAttributeFilter implements ElementSearchFilter
{
    const NOT = 1;
    const NOCASE = 2;
    protected $NOT = false;
    protected $NOCASE = false;
    protected $value = null;
    protected $compatibleType = array(
        'text',
        'htmltext',
        'longtext'
    );
    protected $regexpPrefix='***=';
    protected $regexpPostfix='';

    public function __construct($attrId, $value)
    {
        parent::__construct($attrId);
        $this->value = $value;
        $argv = func_get_args();
        array_splice($argv, 0, 2);
        if (isset($argv[0])) {
            $this->NOT = ($argv[0] & self::NOT);
            $this->NOCASE = ($argv[0] & self::NOCASE);
        }
    }

    public function verifyCompatibility(\Anakeen\Search\Internal\SearchSmartData & $search)
    {
        $attr = parent::verifyCompatibility($search);
        if (!is_scalar($this->value)) {
            throw new Exception("FLT0006");
        }
        if ($attr->isMultiple()) {
            throw new Exception("FLT0008", $attr->id);
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
        /*
         * Prevent chars in $value to be interpreted as REGEX codes.
         * The value is hence treated as a literal string.
         * - http://www.postgresql.org/docs/9.1/static/functions-matching.html#POSIX-METASYNTAX
        */
        $value = $this->regexpPrefix . $this->value. $this->regexpPostfix;

        $sql = sprintf("%s IS NOT NULL AND %s ~%s %s", pg_escape_identifier($attr->id), pg_escape_identifier($attr->id), ($this->NOCASE ? '*' : ''), pg_escape_literal($value));
        if ($this->NOT) {
            $sql = sprintf("NOT(%s)", $sql);
        }
        $search->addFilter($sql);
        return $this;
    }
}
