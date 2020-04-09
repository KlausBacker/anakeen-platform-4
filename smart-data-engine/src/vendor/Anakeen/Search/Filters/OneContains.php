<?php

namespace Anakeen\Search\Filters;

use Anakeen\Core\SmartStructure\NormalAttribute;

class OneContains extends StandardAttributeFilter implements ElementSearchFilter
{
    const NOT = 1;
    const NOCASE = 2;
    const ALL = 4;
    const NODIACRITIC = 8;
    protected $NOT = false;
    protected $NOCASE = false;
    protected $NODIACRITIC = false;
    protected $compatibleType
        = array(
            'text',
            'htmltext',
            'longtext'
        );
    protected $value = null;
    protected $ALL = false;

    /**
     * OneContains constructor.
     * @param $attrId
     * @param $value
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\OneContains::$NOT</b>,
     * <b>\Anakeen\Search\Filters\OneContains::$NOCASE</b>
     * <b>\Anakeen\Search\Filters\OneContains::$NODIACRITIC</b>
     * <b>\Anakeen\Search\Filters\OneContains::$ALL</b>
     * </p>
     */
    public function __construct($attrId, $value, $options = 0)
    {
        parent::__construct($attrId);
        $this->value = $value;
        if (isset($options)) {
            $this->NOT = $this->NOT | ($options & self::NOT);
            $this->NOCASE = $this->NOCASE | ($options & self::NOCASE);
            $this->ALL = $this->ALL | ($options & self::ALL);
            $this->NODIACRITIC = $this->NODIACRITIC | ($options & self::NODIACRITIC);
        }
    }

    /**
     * @param \Anakeen\Search\Internal\SearchSmartData $search
     * @return NormalAttribute
     * @throws Exception
     */
    public function verifyCompatibility(\Anakeen\Search\Internal\SearchSmartData & $search)
    {
        $attr = parent::verifyCompatibility($search);
        if (!$attr->isMultiple()) {
            throw new Exception("FLT0007", $attr->id);
        }
        return $attr;
    }

    /**
     * Generate sql part
     *
     * @param \Anakeen\Search\Internal\SearchSmartData $search
     *
     * @return string sql where condition
     */
    public function addFilter(\Anakeen\Search\Internal\SearchSmartData $search)
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
        $sqlOperator = '~<';
        if ($this->NOCASE && $this->NODIACRITIC) {
            $sqlOperator = '~%*<';
        } elseif ($this->NOCASE) {
            $sqlOperator = '~*<';
        } elseif ($this->NODIACRITIC) {
            $sqlOperator = '~%<';
        }

        $sql = sprintf(
            "%s IS NOT NULL AND %s %s %s(%s)",
            pg_escape_identifier($attr->id),
            pg_escape_literal($value),
            $sqlOperator,
            ($this->ALL ? 'ALL' : 'ANY'),
            pg_escape_identifier($attr->id)
        );
        if ($this->NOT) {
            $sql = sprintf("NOT(%s)", $sql);
        }
        return $sql;
    }
}
