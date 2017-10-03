<?php
namespace Sample\BusinessApp\Renders;

use Dcp\AttributeIdentifiers\Ba_Prospect as MyAttr;

class ProspectAssign extends ProspectEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        $template=<<<'HTML'
<div class="prospect-assign">
<b>{{attributes.pr_society.attributeValue.displayValue}} </b>
<br/>
{{attributes.pr_firstname.attributeValue.displayValue}} {{attributes.pr_lastname.attributeValue.displayValue}}
<br/>
<b>Contact : </b><i class="fa fa-envelope" aria-hidden="true"></i> : 
 {{attributes.pr_mail.attributeValue.displayValue}}, <i class="fa fa-phone" aria-hidden="true"></i>
:  {{{attributes.pr_phone.attributeValue.displayValue}}}
<br/>
<b>Adresse :</b> {{attributes.pr_postalcode.attributeValue.displayValue}} {{attributes.pr_town.attributeValue.displayValue}}

</div>
HTML;

        $options->frame(MyAttr::pr_fr_ident)->setTemplate($template);



        $template=<<<'HTML'
<div class="prospect-assign">

<div class="prospect-subject">
<b>{{attributes.pr_subject.attributeValue.displayValue}} </b>
<br/><br/>
<i>{{attributes.pr_question.attributeValue.displayValue}} </i>
<br/>
<br/>
</div>
<br/>
<b>{{attributes.pr_typeaudit.label}}</b> :<br/>
<br/>
{{{attributes.pr_typeaudit.htmlContent}}}

<br/>
<b>{{attributes.pr_assignedto.label}}</b> :<br/>
<br/>
{{{attributes.pr_assignedto.htmlContent}}}


<br/>
<b>{{attributes.pr_complement.label}}</b> :<br/>
<br/>
{{{attributes.pr_complement.htmlContent}}}

</div>
HTML;

        $options->frame(MyAttr::pr_fr_ask)->setTemplate($template);

        return $options;
    }


    public function getVisibilities(\Doc $document)
    {
        $vis=parent::getVisibilities($document);

        $visibiliies=$vis->getVisibilities();
        foreach ($visibiliies as $attrid=> $visible) {
            $oa=$document->getAttribute($attrid);
            if ($oa->isNormal) {
                if ($visible === \Dcp\Ui\RenderAttributeVisibilities::ReadWriteVisibility) {
                    $vis->setVisibility($attrid, \Dcp\Ui\RenderAttributeVisibilities::StaticWriteVisibility);
                }
            }
        }


        $vis->setVisibility(MyAttr::pr_fr_cmp, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $vis->setVisibility(MyAttr::pr_assignedto, \Dcp\Ui\RenderAttributeVisibilities::ReadWriteVisibility);
        $vis->setVisibility(MyAttr::pr_typeaudit, \Dcp\Ui\RenderAttributeVisibilities::ReadWriteVisibility);
        $vis->setVisibility(MyAttr::pr_complement, \Dcp\Ui\RenderAttributeVisibilities::ReadWriteVisibility);

        return  $vis;
    }


    public function getMenu(\Doc $document)
    {
        $menu = parent::getMenu($document);

        $items=$menu->getElements();
        foreach ($items as $item) {
            if ($item->getId() === "saveAndClose") {
                /**
                 * @var \Dcp\Ui\ItemMenu $item
                 */
                $item->setTextLabel("Assigner");
                $item->setBeforeContent('<div class="fa fa-send" />');
            } else {
                $menu->removeElement($item->getId());
            }
        }




        return $menu;
    }

    public function getNeeded(\Doc $document)
    {
        $need= parent::getNeeded($document);
        $need->setNeeded(MyAttr::pr_assignedto,true);
        return $need;
    }
    /**
     * View only menu
     * @param \Doc|null $document
     * @return array
     */
    public function getTemplates(\Doc $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["body"]["file"] = "BUSINESS_APP/Families/Prospect/Renders/prospectAssign.mustache";
        return $templates;
    }

}
