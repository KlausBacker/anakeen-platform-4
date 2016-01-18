<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

namespace Dcp\Test\Ddui;

use \Dcp\AttributeIdentifiers\TST_DDUI_DOCID as TST_DDUI_DOCID_Attributes;

class TST_DDUI_DOCID extends \Dcp\Family\Document
{
    public function postStore() {
        $this->setValue(TST_DDUI_DOCID_Attributes::test_ddui_docid__histo1, $this->getRawValue(TST_DDUI_DOCID_Attributes::test_ddui_docid__single1, " "));
        $this->setValue(TST_DDUI_DOCID_Attributes::test_ddui_docid__link_histo, $this->getRawValue(TST_DDUI_DOCID_Attributes::test_ddui_docid__single_link," "));
    }
}
