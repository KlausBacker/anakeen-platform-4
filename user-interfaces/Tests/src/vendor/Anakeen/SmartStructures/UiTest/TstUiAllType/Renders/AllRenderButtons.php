<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderButtons extends \Anakeen\Ui\DefaultEdit
{

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $viewDoc = new \Anakeen\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<i class="fa fa-eye"></i>';
        $viewDoc->url = sprintf("/api/v2/smart-elements/{{value}}.html");
        $viewDoc->target = "_dialog";
        $viewDoc->windowWidth = "400px";

        $options->docid()->addButton($viewDoc);


        $cogButton = new \Anakeen\Ui\ButtonOptions();
        $cogButton->htmlContent = '<i class="fa fa-cog"></i>';
        $options->text()->addButton($cogButton);


        $superButton = new \Anakeen\Ui\ButtonOptions();
        $superButton->htmlContent = '<i class="fa fa-superpowers"></i>';
        $options->commonOption()->addButton($superButton);


        $options->docid()->addButton($superButton);
        return $options;
    }
}
