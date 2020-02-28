<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters;

use Anakeen\Search;

/**
 * Class NAFilter
 * @package Anakeen\Search\Filters
 *
 * Empty
 */
class NAFilter extends StandardAttributeFilter implements ElementSearchFilter
{

    use Search\SearchCriteria\SearchCriteriaTrait;

    /**
     * @param Search\Internal\SearchSmartData $search
     * @return $this|string
     */
    public function addFilter(\Anakeen\Search\Internal\SearchSmartData $search)
    {
        return $this;
    }
}
