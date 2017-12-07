<?php
namespace Sample\BusinessApp\Renders;
//use Dcp\AttributeIdentifiers\BA_CLIENT_CONTRACT as MyAttr;
use Dcp\HttpApi\V1\DocManager\DocManager;


class ClientView extends CommonView
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->frame()->showEmptyContent("<div>Aucunes informations</div>");
        $options->arrayAttribute()->setLabelPosition(\Dcp\ui\CommonRenderOptions::nonePosition);
        $options->htmltext()->setToolbar(\dcp\Ui\HtmltextRenderOptions::basicToolbar);

        /*$cert=null;
        if (!$document->getRawValue(MyAttr::cli_cert)) {
            $s=new \SearchDoc("", "BA_CERTIFICATION");
            $s->addFilter("cert_client = '%s'", $document->initid) ;
            $s->setObjectReturn(true);
            $s->search();
            if ($s->count() === 1) {
                $cert=$s->getNextDoc();
                $document->setValue(MyAttr::cli_cert, $cert->initid);
                $document->modify();
            }
        } else {
            $cert=DocManager::getDocument($document->getRawValue(MyAttr::cli_cert));
        }
        if ($cert) {

            $tplCert = sprintf('{{{attribute.htmlContent}}} <span class="client-state" style="background-color:%s">&nbsp;</span> <i>%s</i>', $cert->getStateColor(), _($cert->getStatelabel()));
            $options->docid(MyAttr::cli_cert)->setTemplate($tplCert);
        }*/


        return $options;
    }
    public function getCssReferences(\Doc $document = null)
    {

        $css = parent::getCssReferences($document);
        $ws=\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $css[__CLASS__] = "BUSINESS_APP/Families/ClientContract/Renders/client.css"."?ws=$ws";
        return $css;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $ws=\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js[__CLASS__] = "BUSINESS_APP/Families/ClientContract/Renders/client.js"."?ws=$ws";
        return $js;
    }
}
