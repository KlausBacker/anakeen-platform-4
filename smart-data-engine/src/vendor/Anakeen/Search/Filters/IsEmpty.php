<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters;

use Anakeen\Search\SearchCriteria\SearchCriteriaTrait;

class IsEmpty extends StandardAttributeFilter implements ElementSearchFilter
{

    use SearchCriteriaTrait;

    const NOT = 1;

    public static function getOptionMap()
    {
        return array(
            IsEmpty::NOT => "not"
        );
    }

    protected $NOT = false;

    /**
     * IsEmpty constructor.
     * @param string $attrId
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\IsEmpty::$NOT</b>,
     * </p>
     */
    public function __construct($attrId, $options = 0)
    {
        parent::__construct($attrId);
        if (isset($options)) {
            $this->NOT = ($options & self::NOT);
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
        $this->verifyCompatibility($search);
        $query = $this->NOT ? '%s IS NOT NULL' :'%s IS NULL';
        $search->addFilter(sprintf($query, pg_escape_string($this->attributeId)));
        return $this;
    }
}
