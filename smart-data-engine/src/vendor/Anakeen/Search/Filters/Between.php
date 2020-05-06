<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters;

use Anakeen\Search;

class Between extends StandardAttributeFilter implements ElementSearchFilter
{
    use Search\SearchCriteria\SearchCriteriaTrait;

    const NOT = 1;
    const EQUALLEFT = 2;
    const EQUALRIGHT = 4;

    public static function getOptionMap()
    {
        return array(
            self::NOT => "not",
            self::EQUALLEFT => "equalLeft",
            self::EQUALRIGHT => "equalRight",
        );
    }

    protected $value = null;
    protected $compatibleType = array(
        'int',
        'double',
        'money',
        'date',
        'timestamp',
        'time'
    );
    private $NOT = false;
    private $EQUALLEFT = false;
    private $EQUALRIGHT = false;

    /**
     * IsBetween constructor.
     * @param $attrId
     * @param array $value must have 2 values
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\IsLesser::$EQUAL</b>,
     * <b>\Anakeen\Search\Filters\IsLesser::$ALL</b>
     * </p>
     */
    public function __construct($attrId, $value, $options = 0)
    {
        parent::__construct($attrId);
        $this->value = $value;
        if (isset($options)) {
            $this->NOT = ($options & self::NOT);
            $this->EQUALLEFT = ($options & self::EQUALLEFT);
            $this->EQUALRIGHT = ($options & self::EQUALRIGHT);
        }
    }

    /**
     * Generate sql part
     *
     * @param \Anakeen\Search\Internal\SearchSmartData $search
     *
     * @return string sql where condition
     * @throws Exception
     */
    public function addFilter(\Anakeen\Search\Internal\SearchSmartData $search)
    {
        $attr = $this->verifyCompatibility($search);
        if (empty($this->value[0])) {
            $this->value[0] = -INF;
        }
        if (empty($this->value[1])) {
            $this->value[1] = INF;
        }
        sort($this->value);
        $query = sprintf(
            '%s <%s %s AND %s >%s %s',
            pg_escape_literal($this->value[0]),
            ($this->EQUALLEFT ? '=' : ''),
            pg_escape_identifier($attr->id),
            pg_escape_literal($this->value[1]),
            ($this->EQUALRIGHT ? '=' : ''),
            pg_escape_identifier($attr->id)
        );
        if ($this->NOT) {
            $query = sprintf('NOT(%s)', $query);
        }
        $search->addFilter($query);
        return $this;
    }

    /**
     * @param Search\Internal\SearchSmartData $search
     * @return \Anakeen\Core\SmartStructure\NormalAttribute
     * @throws Exception
     */
    public function verifyCompatibility(\Anakeen\Search\Internal\SearchSmartData &$search)
    {
        $attr = parent::verifyCompatibility($search);
        if (!is_array($this->value) || count($this->value) !== 2) {
            throw new Exception("FLT0020");
        }
        if ($attr->isMultiple()) {
            throw new Exception("FLT0008", $attr->id);
        }
        return $attr;
    }
}
