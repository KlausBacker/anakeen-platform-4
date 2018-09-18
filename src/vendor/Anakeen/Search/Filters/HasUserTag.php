<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters;

use Anakeen\Search;

class HasUserTag extends StandardAttributeFilter implements ElementSearchFilter
{
    protected $uid = null;
    protected $value = null;
    protected $compatibleType = array(
        'text'
    );
    public function __construct($uid, $value)
    {
        parent::__construct('atags');
        $this->uid = $uid;
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
        $search->join("id = docutag(id)");
        $sql = sprintf("(docutag.uid = %s AND docutag.tag = %s)", pg_escape_literal($this->uid) , pg_escape_literal($this->value));
        $search->addFilter($sql);
        return $this;
    }
}
