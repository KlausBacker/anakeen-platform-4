<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEmployee\Renders;

use Anakeen\Ui\RenderOptions;
use \SmartStructure\Fields\Tst_ddui_employee as myAttribute;

class EmployeeFrameViewSRCLRRender extends \Anakeen\Ui\DefaultView
{
    public static function setColumn(\Anakeen\Ui\RenderOptions &$options, $direction = \Anakeen\Ui\FrameRenderOptions::leftRightDirection)
    {

        $options->frame()->setResponsiveColumns([["number" => 2, "minWidth" => "70rem", "grow" => true, "direction" => $direction]]);

        $options->arrayAttribute(myAttribute::tst_adp_phone_array)->setTranspositionWidthLimit("350px");
        $options->frame(myAttribute::tst_f_identite)->setResponsiveColumns([
            ["number" => 2, "minWidth" => "70rem", "maxWidth" => "100rem", "direction" => $direction],
            ["number" => 3, "minWidth" => "100rem", "maxWidth" => "110rem", "direction" => $direction],
            ["number" => 4, "minWidth" => "110rem", "maxWidth" => "120rem", "direction" => $direction],
            ["number" => 5, "minWidth" => "120rem", "maxWidth" => "130rem", "direction" => $direction, "grow" => false],
            ["number" => 6, "minWidth" => "130rem", "direction" => $direction, "grow" => false]
        ]);

        $options->frame(myAttribute::tst_f_adresseperso)->setResponsiveColumns([
            ["number" => 2, "minWidth" => "600px", "maxWidth" => "800px", "direction" => $direction],
            ["number" => 3, "direction" => $direction],
        ]);
        $options->frame(myAttribute::tst_f_dombancaire)->setResponsiveColumns([
            ["number" => 2, "minWidth" => "500px", "maxWidth" => "700px", "direction" => $direction],
            ["number" => 3, "maxWidth" => "800px", "direction" => $direction],
            ["number" => 4, "maxWidth" => "1900px", "direction" => $direction],
            ["number" => 6, "maxWidth" => "2200px", "direction" => $direction],
            ["number" => 12]
        ]);

    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->commonOption()->showEmptyContent("Pas d'information");
        self::setColumn($options);


        return $options;
    }
}
