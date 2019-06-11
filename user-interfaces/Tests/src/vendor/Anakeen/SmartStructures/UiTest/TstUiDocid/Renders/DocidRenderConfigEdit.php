<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\SmartStructures\UiTest\TstUiDocid\Renders;

use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_docid as myAttributes;

class DocidRenderConfigEdit extends \Anakeen\Ui\DefaultEdit
{
    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "Docid Edit";
    }
    
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->docid(myAttributes::test_ddui_docid__single1)->setDisplay(\Anakeen\Ui\DocidRenderOptions::listDisplay);
        $options->docid(myAttributes::test_ddui_docid__single2)->setDisplay(\Anakeen\Ui\DocidRenderOptions::autocompletionDisplay);
        $options->docid(myAttributes::test_ddui_docid__single3)->setDisplay(\Anakeen\Ui\DocidRenderOptions::multipleSingleDisplay);

        $options->docid(myAttributes::test_ddui_docid__single2)->setFormat('<i class="fa fa-history"/> {{displayValue}} (H)');
        $viewDoc=new \Anakeen\Ui\CreateDocumentOptions();
        $viewDoc->formValues=[
            myAttributes::test_ddui_docid__titleref => "Création auto depuis {{properties.id}}",
            myAttributes::test_ddui_docid__title => sprintf("{{attributes.%s.attributeValue}}", myAttributes::test_ddui_docid__single3),
        ];
        $options->docid(myAttributes::test_ddui_docid__single2)->addCreateDocumentButton($viewDoc);

        $viewDoc=new \Anakeen\Ui\CreateDocumentOptions();
        
        $options->docid(myAttributes::test_ddui_docid__title)->addCreateDocumentButton($viewDoc);

        return $options;
    }
}
