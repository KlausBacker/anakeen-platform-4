<?php

namespace Anakeen\Search\Filters;

use Anakeen\SmartCriteria\SmartCriteriaTrait;

class Contains extends StandardAttributeFilter implements ElementSearchFilter
{
    use SmartCriteriaTrait;

    const NOT = 1;
    const NOCASE = 2;
    const NODIACRITIC = 4;
    const STARTSWITH = 8;

    public static function getOptionMap()
    {
        return array(
            self::NOT => "not",
            self::NOCASE => "noCase",
            self::NODIACRITIC => "noDiacritic",
            self::STARTSWITH => "startsWith"
        );
    }

    protected $NOT = false;
    protected $NOCASE = false;
    private $NODIACRITIC = false;
    private $STARTSWITH = false;
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
            $this->STARTSWITH = ($options & self::STARTSWITH);
            /* NODIACRITIC toggles NOCASE */
            if ($this->NODIACRITIC) {
                $this->NOCASE = true;
            }

            if ($this->STARTSWITH) {
                $this->regexpPrefix = '^';
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
            throw new Exception("FLT0006", $attr->id);
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

        if (!empty($this->value)) {
            $value = $this->regexpPrefix . $this->value . $this->regexpPostfix;

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
        }
        return $this;
    }
}
