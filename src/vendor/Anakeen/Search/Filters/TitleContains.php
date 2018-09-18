<?php

namespace Anakeen\Search\Filters;

use Anakeen\Search;

class TitleContains extends StandardAttributeFilter implements ElementSearchFilter
{
    const NOT = 1;
    const REGEXP = 2;
    const NOCASE = 4;
    const NODIACRITIC = 8;
    private $NOT = false;
    private $REGEXP = false;
    private $NOCASE = false;
    private $NODIACRITIC = false;
    protected $value = null;
    protected $compatibleType = array(
        'text'
    );
    public function __construct($value)
    {
        parent::__construct('title');
        $this->value = $value;
        $argv = func_get_args();
        array_splice($argv, 0, 1);
        if (!empty($argv[0])) {
            $this->NOT = ($argv[0] & self::NOT);
            $this->REGEXP = ($argv[0] & self::REGEXP);
            $this->NOCASE = ($argv[0] & self::NOCASE);
            $this->NODIACRITIC = ($argv[0] & self::NODIACRITIC);
            /* NODIACRITIC toggles NOCASE */
            if ($this->NODIACRITIC) {
                $this->NOCASE = true;
            }
        }
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
        $sql = $this->_sqlInstruction();
        $search->addFilter($sql);
        return $this;
    }
    protected function _sqlInstruction()
    {
        $leftOperand = "title";
        $rightOperand = $this->value;
        if ($this->REGEXP) {
            /* REGEXP matching */
            $operator = ($this->NOCASE) ? '~*' : '~';
            $rightOperand = sprintf("E'%s'", pg_escape_string($rightOperand));
        } else {
            /* WORD matching */
            $operator = ($this->NOCASE) ? 'ILIKE' : 'LIKE';
            if ($this->NOCASE) {
                $rightOperand = mb_strtolower($rightOperand);
            }
            $rightOperand = pg_escape_literal('%' . $rightOperand . '%');
        }
        if ($this->NODIACRITIC) {
            $leftOperand = sprintf("unaccent(%s)", $leftOperand);
            $rightOperand = sprintf("unaccent(%s)", $rightOperand);
        }
        $sql = sprintf("%s %s %s", $leftOperand, $operator, $rightOperand);
        if ($this->NOT) {
            $sql = sprintf("NOT(%s)", $sql);
        }
        return $sql;
    }
}
