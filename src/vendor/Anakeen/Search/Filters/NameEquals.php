<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters;

use Anakeen\Search;

class NameEquals extends StandardAttributeFilter implements ElementSearchFilter
{
    protected $value = null;
    protected $compatibleType = array(
        'text'
    );
    public function __construct($value)
    {
        parent::__construct('name');
        $this->value = $value;
    }
    /**
     * Generate sql part
     * @param \SearchDoc $search
     * @throws Exception
     * @return string sql where condition
     */
    public function addFilter(\SearchDoc $search)
    {
        $sql = sprintf("%s = %s", pg_escape_identifier("name") , pg_escape_literal($this->value));
        $search->addFilter($sql);
        return $this;
    }
}
