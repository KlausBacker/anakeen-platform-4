<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters;


class DocumentTitle extends StandardDocumentTitleFilter implements ElementSearchFilter
{
    public function verifyCompatibility(\SearchDoc & $search)
    {
        $attr = parent::verifyCompatibility($search);
        if ($attr->isMultiple()) {
            throw new Exception("FLT0008", $attr->id);
        }
        if (!is_scalar($this->value)) {
            throw new Exception("FLT0006");
        }
        return $attr;
    }
}
