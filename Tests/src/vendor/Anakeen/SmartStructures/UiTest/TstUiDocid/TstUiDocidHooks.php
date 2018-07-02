<?php

namespace Anakeen\SmartStructures\UiTest\TstUiDocid;

use Anakeen\SmartHooks;
use \SmartStructure\Fields\Tst_ddui_docid as TST_DDUI_DOCID_Attributes;

class TstUiDocidHooks extends \Anakeen\SmartElement

{

    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            $this->setValue(TST_DDUI_DOCID_Attributes::test_ddui_docid__histo1, $this->getRawValue(TST_DDUI_DOCID_Attributes::test_ddui_docid__single1, " "));
            $this->setValue(TST_DDUI_DOCID_Attributes::test_ddui_docid__link_histo, $this->getRawValue(TST_DDUI_DOCID_Attributes::test_ddui_docid__single_link, " "));
        });
    }
}
