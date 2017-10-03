<?php
namespace Sample\BusinessApp\Renders;

use Dcp\AttributeIdentifiers\Ba_Prospect as MyAttr;

class ProspectCreate extends CommonEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->htmltext()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->htmltext()->setToolbar(\dcp\Ui\HtmltextRenderOptions::basicToolbar);
        $options->text(MyAttr::pr_postalcode)->setMaxLength(5);
        $options->longtext(MyAttr::pr_question)->setMaxDisplayedLineNumber(20);
        $options->enum(MyAttr::pr_catg)->useOtherChoice(true);
        $options->arrayAttribute(MyAttr::pr_t_anx)->setRowMinDefault(1);

        return $options;
    }


    public function getMenu(\Doc $document)
    {
        $menu = parent::getMenu($document);

        $items=$menu->getElements();
        foreach ($items as $item) {
            if ($item->getId() === "create" || $item->getId() === "save") {
                /**
                 * @var \Dcp\Ui\ItemMenu $item
                 */
                 $item->setTextLabel("Envoyer");
                $item->setBeforeContent('<div class="fa fa-send" />');
            } else {
                $menu->removeElement($item->getId());
            }
        }
        return $menu;
    }


    public function getVisibilities(\Doc $document)
    {
        $vis=parent::getVisibilities($document);
        $vis->setVisibility(MyAttr::pr_t_cmp, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);

        return  $vis;
    }

    /**
     * View only menu
     * @param \Doc|null $document
     * @return array
     */
    public function getTemplates(\Doc $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["body"]["file"] = "BUSINESS_APP/Families/Prospect/Renders/prospectForm.mustache";
        return $templates;
    }
    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences($document);
        $css[__CLASS__] = "BUSINESS_APP/Families/Prospect/Renders/prospect.css";
        return $css;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $js[__CLASS__] = "BUSINESS_APP/Families/Prospect/Renders/prospect.js";
        return $js;
    }
}
