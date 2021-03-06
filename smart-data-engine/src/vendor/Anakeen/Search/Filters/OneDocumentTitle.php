<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters;

use Anakeen\Search;

class OneDocumentTitle extends StandardDocumentTitleFilter implements ElementSearchFilter
{
    public function verifyCompatibility(\Anakeen\Search\Internal\SearchSmartData &$search)
    {
        $attr = parent::verifyCompatibility($search);
        if (!$attr->isMultiple()) {
            throw new Exception("FLT0007", $attr->id);
        }
        if (!is_scalar($this->value)) {
            throw new Exception("FLT0006", $attr->id);
        }
        return $attr;
    }
}
