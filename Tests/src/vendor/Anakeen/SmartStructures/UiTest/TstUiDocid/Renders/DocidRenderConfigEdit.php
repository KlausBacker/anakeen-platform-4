<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\SmartStructures\UiTest\TstUiDocid\Renders;

use SmartStructure\Attributes\TST_DDUI_DOCID as myAttributes;

class DocidRenderConfigEdit extends \Dcp\Ui\DefaultEdit
{
    public function getLabel(\Doc $document = null)
    {
        return "Docid Edit";
    }
    
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->docid(myAttributes::test_ddui_docid__single1)->setDisplay(\Dcp\Ui\DocidRenderOptions::listDisplay);
        $options->docid(myAttributes::test_ddui_docid__single2)->setDisplay(\Dcp\Ui\DocidRenderOptions::autocompletionDisplay);
        $options->docid(myAttributes::test_ddui_docid__single3)->setDisplay(\Dcp\Ui\DocidRenderOptions::multipleSingleDisplay);

        $options->docid(myAttributes::test_ddui_docid__single2)->setFormat('<i class="fa fa-history"/> {{displayValue}} (H)');
        $viewDoc=new \Dcp\Ui\CreateDocumentOptions();
        $viewDoc->formValues=[
            myAttributes::test_ddui_docid__titleref => "Création auto depuis {{properties.id}}",
            myAttributes::test_ddui_docid__title => sprintf("{{attributes.%s.attributeValue}}", myAttributes::test_ddui_docid__single3),
        ];
        $options->docid(myAttributes::test_ddui_docid__single2)->addCreateDocumentButton($viewDoc);

        $viewDoc=new \Dcp\Ui\CreateDocumentOptions();
        
        $options->docid(myAttributes::test_ddui_docid__title)->addCreateDocumentButton($viewDoc);

        return $options;
    }
}
