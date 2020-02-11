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

    /**
     * TitleContains constructor.
     * @param $value
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\TitleContains::$NOT</b>,
     * <b>\Anakeen\Search\Filters\TitleContains::$REGEXP</b>,
     * <b>\Anakeen\Search\Filters\TitleContains::$NOCASE</b>,
     * <b>\Anakeen\Search\Filters\TitleContains::$NODIACRITIC</b>,
     * </p>
     */
    public function __construct($value, $options = 0)
    {
        parent::__construct('title');
        $this->value = $value;
        if (!empty($options)) {
            $this->NOT = ($options & self::NOT);
            $this->REGEXP = ($options & self::REGEXP);
            $this->NOCASE = ($options & self::NOCASE);
            $this->NODIACRITIC = ($options & self::NODIACRITIC);
            /* NODIACRITIC toggles NOCASE */
            if ($this->NODIACRITIC) {
                $this->NOCASE = true;
            }
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
        $this->verifyCompatibility($search);
        $sql = $this->_sqlInstruction();
        $search->addFilter($sql);
        return $this;
    }

    /**
     * @return string
     */
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
