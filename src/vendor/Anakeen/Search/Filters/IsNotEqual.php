<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Search;

class IsNotEqual extends StandardAttributeFilter implements ElementSearchFilter
{
    protected $value = null;
    public function __construct($attrId, $value)
    {
        parent::__construct($attrId);
        $this->value = $value;
    }
    public function verifyCompatibility(\SearchDoc & $search)
    {
        $attr = parent::verifyCompatibility($search);
        if (is_array($this->value)) {
            if (!$attr->isMultiple()) {
                throw new Exception("FLT0005");
            }
            $this->value = SmartElement::arrayToRawValue($this->value);
        } else if (!is_scalar($this->value)) {
            throw new Exception("FLT0004");
        }
        return $attr;
    }
    /**
     * Generate sql part
     * @param \SearchDoc $search
     * @return string sql where condition
     */
    public function addFilter(\SearchDoc $search)
    {
        $attr = $this->verifyCompatibility($search);
        $search->addFilter(sprintf('%s IS NULL OR %s <> %s', pg_escape_identifier($attr->id) , pg_escape_identifier($attr->id) , pg_escape_literal($this->value)));
        return $this;
    }
}
