<?php

namespace Anakeen\Search\Filters;

use Anakeen\Core\SmartStructure\NormalAttribute;
use Anakeen\Search;

class StandardDocumentTitleFilter extends StandardAttributeFilter implements ElementSearchFilter
{
    const MATCH_REGEXP = 1;
    const NOCASE = 2;
    protected $MATCH_REGEXP = false;
    protected $NOCASE = false;
    protected $value = null;
    protected $compatibleType = array(
        'docid',
        'account',
    );
    public function __construct($attrId, $value)
    {
        parent::__construct($attrId);
        $this->value = $value;
        $argv = func_get_args();
        array_splice($argv, 0, 2);
        if (isset($argv[0])) {
            $this->MATCH_REGEXP = ($argv[0] & self::MATCH_REGEXP);
            $this->NOCASE = ($argv[0] & self::NOCASE);
        }
    }
    /**
     * Generate sql part
     * @param \SearchDoc $search
     * @return string sql where condition
     */
    public function addFilter(\SearchDoc $search)
    {
        $attr = $this->verifyCompatibility($search);
        // $famName = $attr->format;
        $search->join("id = docrel(sinitid)");
        $search->addFilter($this->_filter($attr, $this->value));
        return $this;
    }
    protected function _filter(NormalAttribute & $attr, $value)
    {
        $leftOperand = 'docrel.ctitle';
        $rightOperand = $value;
        if ($this->MATCH_REGEXP) {
            $operator = ($this->NOCASE) ? '~*' : '~';
            $rightOperand = sprintf("E'%s'", pg_escape_string($rightOperand));
        } else {
            $operator = '=';
            if ($this->NOCASE) {
                $leftOperand = sprintf("lower(%s)", $leftOperand);
                $rightOperand = sprintf("lower(%s)", pg_escape_literal($value));
            } else {
                $rightOperand = pg_escape_literal($value);
            }
        }
        $sql = sprintf("docrel.type = %s AND %s %s %s", pg_escape_literal(mb_strtolower($attr->id)) , $leftOperand, $operator, $rightOperand);
        return $sql;
    }
}
