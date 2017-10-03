<?php
namespace Sample\BusinessApp\Renders;

use Dcp\AttributeIdentifiers\Ba_Prospect as MyAttr;
use Dcp\Ui\DefaultView;

class ProspectAckView extends ProspectView
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);




        return $options;
    }

    /**
     * View only menu
     * @param \Doc|null $document
     * @return array
     */
    public function getTemplates(\Doc $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["body"]["file"] = "BUSINESS_APP/Families/Prospect/Renders/prospectAck.mustache";
        return $templates;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $js["logout"] = "BUSINESS_APP/Families/Prospect/Renders/logout.js";
        return $js;
    }

}
