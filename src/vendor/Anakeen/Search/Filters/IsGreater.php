<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters;

use Anakeen\Search;

class IsGreater extends StandardAttributeFilter implements ElementSearchFilter
{
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
    public function __construct($attrId, $value)
    {
        parent::__construct($attrId);
        $this->value = $value;
        $argv = func_get_args();
        array_splice($argv, 0, 2);
        if (isset($argv[0])) {
            $this->EQUAL = ($argv[0] & self::EQUAL);
        }
    }
    public function verifyCompatibility(\SearchDoc & $search)
    {
        $attr = parent::verifyCompatibility($search);
        if (!is_scalar($this->value)) {
            throw new Exception("FLT0006");
        }
        if ($attr->isMultiple()) {
            throw new Exception("FLT0008", $attr->id);
        }
        return $attr;
    }
    /**
     * Generate sql part
     * @param \SearchDoc $search
     * @throws Exception
     * @return string sql where condition
     */
    public function addFilter(\SearchDoc $search)
    {
        $attr = $this->verifyCompatibility($search);
        $search->addFilter(sprintf('%s <%s %s', pg_escape_literal($this->value) , ($this->EQUAL ? '=' : '') , pg_escape_identifier($attr->id)));
        return $this;
    }
}
