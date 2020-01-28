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
    protected $compatibleType
        = array(
            'docid',
            'account',
        );

    /**
     * StandardDocumentTitleFilter constructor.
     * @param $attrId
     * @param $value
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\StandardDocumentTitleFilter::$MATCH_REGEXP</b>,
     * <b>\Anakeen\Search\Filters\StandardDocumentTitleFilter::$NOCASE</b>,
     * </p>
     */
    public function __construct($attrId, $value, $options = 0)
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
     *
     * @param \Anakeen\Search\Internal\SearchSmartData $search
     *
     * @return string sql where condition
     */
    public function addFilter(\Anakeen\Search\Internal\SearchSmartData $search)
    {
        $attr = $this->verifyCompatibility($search);
        $docTitle = $attr->getOption("doctitle");
        if ($docTitle) {
            if ($docTitle === "auto") {
                $docTitle = $attr->id . "_title";
            }
            $operator = ($this->NOCASE) ? '~*' : '~';

            $search->addFilter("%s %s '%s'", $docTitle, $operator, $this->MATCH_REGEXP ? $this->value : preg_quote($this->value));
        } else {
            // $famName = $attr->format;
            $search->join("id = docrel(sinitid)");
            $search->addFilter($this->_filter($attr, $this->value));
        }
        return $this;
    }

    /**
     * @param NormalAttribute $attr
     * @param $value
     * @return string
     */
    protected function _filter(NormalAttribute & $attr, $value)
    {
        $leftOperand = 'docrel.ctitle';
        $rightOperand = $value;

        if (!$this->MATCH_REGEXP) {
            $rightOperand = preg_quote($rightOperand);
        }
        $operator = ($this->NOCASE) ? '~*' : '~';
        $rightOperand = sprintf("E'%s'", pg_escape_string($rightOperand));
        $sql = sprintf("docrel.type = %s AND %s %s %s", pg_escape_literal(mb_strtolower($attr->id)), $leftOperand, $operator, $rightOperand);
        return $sql;
    }
}
