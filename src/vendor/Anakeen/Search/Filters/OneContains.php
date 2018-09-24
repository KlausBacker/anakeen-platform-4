<?php

namespace Anakeen\Search\Filters;

use Anakeen\Core\SmartStructure\NormalAttribute;
use Anakeen\Search;

class OneContains extends StandardAttributeFilter implements ElementSearchFilter
{
    const NOT = 1;
    const NOCASE = 2;
    const ALL = 4;
    protected $NOT = false;
    protected $NOCASE = false;
    protected $compatibleType = array(
        'text',
        'htmltext',
        'longtext'
    );
    protected $value = null;
    protected $ALL= false;

    public function __construct($attrId, $value)
    {
        parent::__construct($attrId);
        $this->value = $value;
        $argv = func_get_args();
        array_splice($argv, 0, 2);
        if (isset($argv[0])) {
            $this->NOT = $this->NOT | ($argv[0] & self::NOT);
            $this->NOCASE = $this->NOCASE | ($argv[0] & self::NOCASE);
            $this->ALL = $this->ALL | ($argv[0] & self::ALL);
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
     * @return string sql where condition
     */
    public function addFilter(\SearchDoc $search)
    {
        $attr = $this->verifyCompatibility($search);
        $search->addFilter($this->_filter($attr, $this->value));
        return $this;
    }
    protected function _filter(NormalAttribute & $attr, $value)
    {
        /*
         * Prevent chars in $value to be interpreted as REGEX codes.
         * The value is hence treated as a literal string.
         * - http://www.postgresql.org/docs/9.1/static/functions-matching.html#POSIX-METASYNTAX
        */
        $value = '***=' . $value;
        $sql = sprintf("%s IS NOT NULL AND %s ~%s< %s(%s)", pg_escape_identifier($attr->id) , pg_escape_literal($value) , ($this->NOCASE ? '*' : '') ,($this->ALL ? 'ALL' : 'ANY'), pg_escape_identifier($attr->id));
        if ($this->NOT) {
            $sql = sprintf("NOT(%s)", $sql);
        }
        return $sql;
    }
}
