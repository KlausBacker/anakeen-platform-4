<?php
namespace Sample\BusinessApp\Renders;

use Dcp\AttributeIdentifiers\Ba_Prospect as MyAttr;

class ProspectCompleteForm extends ProspectCreate
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->frame(MyAttr::pr_fr_ident)->setCollapse(true);
        $options->frame(MyAttr::pr_fr_ask)->setCollapse(true);
        $options->arrayAttribute(MyAttr::pr_t_cmp)->setRowMinDefault(1);


        return $options;
    }



    public function getVisibilities(\Doc $document)
    {
        $vis=parent::getVisibilities($document);
        $vis->setVisibility(MyAttr::pr_t_cmp, \Dcp\Ui\RenderAttributeVisibilities::ReadWriteVisibility);

        return  $vis;
    }
}
