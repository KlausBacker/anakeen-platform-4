<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEmployee\Renders;

use Anakeen\Ui\RenderOptions;
use \SmartStructure\Fields\Tst_ddui_employee as myAttribute;

class EmployeeTabViewRender extends \Anakeen\Ui\DefaultView
{
    public static function setColumn(\Anakeen\Ui\RenderOptions &$options)
    {

        $options->tab()->setResponsiveColumns([
            ["number" => 2, "minWidth" => "100rem", "grow" => true]

        ]);

        $options->tab(myAttribute::tst_t_infos_administratives)->setResponsiveColumns([
            ["number" => 2, "minWidth" => "70rem", "maxWidth" => "100rem"],
            ["number" => 3, "maxWidth" => "130rem"],
            ["number" => 4]
        ]);

        $options->frame()->setResponsiveColumns([["number" => 2, "minWidth" => "400px", "grow" => true]]);
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->commonOption()->showEmptyContent("Pas d'information");

        self::setColumn($options);


        return $options;
    }
}
