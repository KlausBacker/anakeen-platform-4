<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Search;
use Anakeen\SmartCriteria\SmartCriteriaTrait;

class OneBetween extends StandardAttributeFilter implements ElementSearchFilter
{

    use SmartCriteriaTrait;

    const NOT = 1;
    const LEFTEQUAL = 2;
    const RIGHTEQUAL = 4;
    const ALL = 8;

    public static function getOptionMap()
    {
        return array(
            self::NOT => "not",
            self::LEFTEQUAL => "leftEqual",
            self::RIGHTEQUAL => "rightEqual",
            self::ALL => "all",
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
    private $LEFTEQUAL = false;
    private $RIGHTEQUAL = false;
    private $ALL = false;

    /**
     * IsBetween constructor.
     * @param $attrId
     * @param array $value must have 2 values
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\OneBetween::$LEFTEQUAL</b>,
     * <b>\Anakeen\Search\Filters\OneBetween::$RIGHTEQUAL</b>,
     * </p>
     */
    public function __construct($attrId, $value, $options = 0)
    {
        parent::__construct($attrId);
        $this->value = $value;
        if (isset($options)) {
            $this->NOT = ($options & self::NOT);
            $this->LEFTEQUAL = ($options & self::LEFTEQUAL);
            $this->RIGHTEQUAL = ($options & self::RIGHTEQUAL);
            $this->ALL = ($options & self::ALL);
        }
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
     * @throws Exception
     */
    public function addFilter(\Anakeen\Search\Internal\SearchSmartData $search)
    {
        $attr = $this->verifyCompatibility($search);
        sort($this->value);
        if (isset($this->value[0]) && isset($this->value[1])) {
            $this->value = SmartElement::arrayToRawValue($this->value);
            $pgBetweenArray = sprintf("'%s'", $this->value);
            $pgBetweenArray = $pgBetweenArray . '::text[]';
            $operator = ">~<";
            if ($this->LEFTEQUAL && $this->RIGHTEQUAL) {
                $operator = ">=~<=";
            } elseif ($this->LEFTEQUAL) {
                $operator = ">=~<";
            } elseif ($this->RIGHTEQUAL) {
                $operator = ">~<=";
            }
            $query = sprintf(
                "%s IS NOT NULL AND %s %s %s(%s::text[])",
                pg_escape_identifier($attr->id),
                $pgBetweenArray,
                $operator,
                ($this->ALL ? 'ALL' : 'ANY'),
                $attr->id
            );
            if ($this->NOT) {
                $query = sprintf('NOT(%s)', $query);
            }
            $search->addFilter($query);
        }
        return $this;
    }
}
