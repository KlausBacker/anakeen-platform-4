<?php

namespace Anakeen\Search\Filters;

use Anakeen\Core\SmartStructure\NormalAttribute;

class FileContent extends StandardAttributeFilter implements ElementSearchFilter
{
    const NOT = 1;
    const MATCH_REGEXP = 2;
    protected $compatibleType = array(
        'file'
    );
    protected $NOT = false;
    protected $MATCH_REGEXP = false;
    protected $value = null;

    /**
     * FileContent constructor.
     * @param $attrId
     * @param $value
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\FileContent::$NOT</b>,
     * <b>\Anakeen\Search\Filters\FileContent::$MATCH_REGEXP</b>
     * </p>
     */
    public function __construct($attrId, $value, $options = 0)
    {
        parent::__construct($attrId);
        $this->value = $value;
        if (isset($options)) {
            $this->NOT = ($options & self::NOT);
            $this->MATCH_REGEXP = ($options & self::MATCH_REGEXP);
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
        if (!is_scalar($this->value)) {
            throw new Exception("FLT0006");
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
        $search->addFileFilter($this->_filter($attr, $this->value));
        return $this;
    }

    /**
     * @param NormalAttribute $attr
     * @param $value
     * @return string
     */
    protected function _filter(NormalAttribute $attr, $value)
    {
        if ($this->NOT) {
            if ($this->MATCH_REGEXP) {
                return $this->_filterNotRegexp($attr, $value);
            } else {
                return $this->_filterNotWord($attr, $value);
            }
        } else {
            if ($this->MATCH_REGEXP) {
                return $this->_filterRegexp($attr, $value);
            } else {
                return $this->_filterWord($attr, $value);
            }
        }
    }

    /**
     * @param NormalAttribute $attr
     * @param $value
     * @return string
     */
    protected function _filterWord(NormalAttribute $attr, $value)
    {
        $attrVecId = sprintf("%s_vec", $attr->id);
        if ($attr->isMultiple()) {
            $filter = sprintf("(%s IS NOT NULL) AND (to_tsquery(%s) @@ ANY (%s))", pg_escape_identifier($attrVecId), pg_escape_literal($value), pg_escape_identifier($attrVecId));
        } else {
            $filter = sprintf("(%s IS NOT NULL) AND (to_tsquery(%s) @@ %s)", pg_escape_identifier($attrVecId), pg_escape_literal($value), pg_escape_identifier($attrVecId));
        }
        return $filter;
    }

    /**
     * @param NormalAttribute $attr
     * @param $value
     * @return string
     */
    protected function _filterNotWord(NormalAttribute $attr, $value)
    {
        $filter = sprintf("NOT(%s)", $this->_filterWord($attr, $value));
        return $filter;
    }

    /**
     * @param NormalAttribute $attr
     * @param $value
     * @return string
     */
    protected function _filterRegexp(NormalAttribute $attr, $value)
    {
        $attrTxtId = sprintf("%s_txt", $attr->id);
        /*
         * Escape REs with pg_escape_string() instead of pg_escape_literal(), otherwise '\x' characters would work correctly.
        */
        if ($attr->isMultiple()) {
            $filter = sprintf("(%s IS NOT NULL) AND (E'%s' ~*< ANY (%s))", pg_escape_identifier($attrTxtId), pg_escape_string($value), pg_escape_identifier($attrTxtId));
        } else {
            $filter = sprintf("(%s IS NOT NULL) AND (%s ~* E'%s')", pg_escape_identifier($attrTxtId), pg_escape_identifier($attrTxtId), pg_escape_string($value));
        }
        return $filter;
    }

    /**
     * @param NormalAttribute $attr
     * @param $value
     * @return string
     */
    protected function _filterNotRegexp(NormalAttribute $attr, $value)
    {
        $filter = sprintf("NOT(%s)", $this->_filterRegexp($attr, $value));
        return $filter;
    }
}
