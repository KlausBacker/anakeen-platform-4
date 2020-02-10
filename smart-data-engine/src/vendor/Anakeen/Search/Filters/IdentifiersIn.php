<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters;

use Anakeen\Search;

class IdentifiersIn extends StandardAttributeFilter implements ElementSearchFilter
{
    const INITID = 1;
    private $INITID = false;
    protected $value = null;
    protected $compatibleType = array(
        'int'
    );

    /**
     * IdentifiersIn constructor.
     * @param $value
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\IdentifiersIn::$INITID</b>,
     * </p>
     */
    public function __construct($value, $options = 0)
    {
        parent::__construct('id');
        if (!is_array($value)) {
            $value = array(
                $value
            );
        }
        $this->value = $value;
        if (isset($options)) {
            $this->INITID = ($options & self::INITID);
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
        $leftOperand = ($this->INITID) ? 'initid' : 'id';
        $this->value = array_map(function ($v) {
            return pg_escape_literal($v);
        }, $this->value);
        $sql = sprintf("%s IN (%s)", pg_escape_identifier($leftOperand), join(', ', $this->value));
        $search->addFilter($sql);
        return $this;
    }
}
