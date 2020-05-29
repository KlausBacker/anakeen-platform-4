<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters;

use Anakeen\Search;
use Anakeen\Search\SearchCriteria\SearchCriteriaTrait;

class IsGreater extends StandardAttributeFilter implements ElementSearchFilter
{
    use SearchCriteriaTrait;

    public static function getOptionMap()
    {
        return array(
            self::EQUAL => "equal",
        );
    }

    const EQUAL = 1;
    private $EQUAL = false;
    protected $value = null;
    protected $compatibleType = array(
        'int',
        'double',
        'money',
        'date',
        'timestamp',
        'time'
    );

    /**
     * IsGreater constructor.
     * @param $attrId
     * @param $value
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\IsGreater::$EQUAL</b>,
     * </p>
     */
    public function __construct($attrId, $value, $options = 0)
    {
        parent::__construct($attrId);
        $this->value = $value;
        if (isset($options)) {
            $this->EQUAL = ($options & self::EQUAL);
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
        if (!is_scalar($this->value)) {
            throw new Exception("FLT0006", $attr->id);
        }
        if ($attr->isMultiple()) {
            throw new Exception("FLT0008", $attr->id);
        }

        if ($this->value === null || $this->value === "") {
            throw new Exception("FLT0022", $attr->id);
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

        $search->addFilter(sprintf(
            '%s <%s %s',
            pg_escape_literal($this->value),
            ($this->EQUAL ? '=' : ''),
            pg_escape_identifier($attr->id)
        ));

        return $this;
    }
}
