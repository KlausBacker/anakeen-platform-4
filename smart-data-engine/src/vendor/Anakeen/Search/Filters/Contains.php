<?php

namespace Anakeen\Search\Filters;

class Contains extends StandardAttributeFilter implements ElementSearchFilter
{
    const NOT = 1;
    const NOCASE = 2;
    const NODIACRITIC = 4;
    protected $NOT = false;
    protected $NOCASE = false;
    private $NODIACRITIC = false;
    protected $value = null;
    protected $compatibleType = array(
        'text',
        'htmltext',
        'longtext'
    );
    protected $regexpPrefix='***=';
    protected $regexpPostfix='';

    /**
     * Contains constructor.
     * @param string $attrId
     * @param string $value
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\Contains::$NOT</b>,
     * <b>\Anakeen\Search\Filters\Contains::$NOCASE</b>
     * </p>
     */
    public function __construct($attrId, $value, $options = 0)
    {
        parent::__construct($attrId);
        $this->value = $value;

        if (isset($options)) {
            $this->NOT = ($options & self::NOT);
            $this->NOCASE = ($options & self::NOCASE);
            $this->NODIACRITIC = ($options & self::NODIACRITIC);
            /* NODIACRITIC toggles NOCASE */
            if ($this->NODIACRITIC) {
                $this->NOCASE = true;
            }
        }
    }

    /**
     * @param \Anakeen\Search\Internal\SearchSmartData $search
     * @return \Anakeen\Core\SmartStructure\NormalAttribute
     * @throws Exception
     */
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

        $leftOperand = pg_escape_identifier($attr->id);
        $operator = $this->NOCASE ? '~*' : '~';
        $rightOperand = pg_escape_literal($value);

        if ($this->NODIACRITIC) {
            $leftOperand = sprintf("unaccent(%s)", $leftOperand);
            $rightOperand = sprintf("unaccent(%s)", $rightOperand);
        }
        $sql = sprintf("%s IS NOT NULL AND %s %s %s", $leftOperand, $leftOperand, $operator, $rightOperand);
        if ($this->NOT) {
            $sql = sprintf("NOT(%s)", $sql);
        }
        $search->addFilter($sql);
        return $this;
    }
}
