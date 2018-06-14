<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Iuser\Render;

use Anakeen\SmartElementManager;
use Anakeen\Ui\DefaultConfigEditRender;
use \SmartStructure\Attributes\Iuser as myAttributes;

class IuserEditRender extends DefaultConfigEditRender
{
    protected $defaultGroup;

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        $options->frame(myAttributes::us_fr_security)->setTemplate(
            <<< 'HTML'
            <span class="us_accexpiredate">{{{attributes.us_accexpiredate.label}}}</span> : {{{attributes.us_accexpiredate.htmlContent}}}
HTML
        );
        return $options;
    }
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document)
    {
        $visibilities = parent::getVisibilities($document);

        if (!$document->getRawValue(myAttributes::us_fr_security)) {
            $visibilities->setVisibility(myAttributes::us_fr_security, \Dcp\Ui\RenderAttributeVisibilities::ReadWriteVisibility);
        }
        return $visibilities;
    }

    public function setCustomClientData(\Anakeen\Core\Internal\SmartElement $document, $data)
    {
        if (!$document->getPropertyValue("initid") && isset($data["defaultGroup"])) {
            $this->defaultGroup = $data["defaultGroup"];
        }
        if (isset($data["setGroup"])) {
            $groupSE = SmartElementManager::getDocument($data["setGroup"]);
            /* @var $groupSE \SmartStructure\Igroup */
            $groupSE->insertDocument($document->getPropertyValue("initid"));
        }
        return $data;
    }

    public function getCustomServerData(\Anakeen\Core\Internal\SmartElement $document)
    {
        $data = parent::getCustomServerData($document);
        $data["EDIT_GROUP"] = true;
        if ($this->defaultGroup) {
            $data["defaultGroup"] = $this->defaultGroup;
        }
        return $data;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $smartElement = null)
    {
        $js = parent::getJsReferences();
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");

        $js["iuser"] = '/uiAssets/Families/iuser/prod/iuser.js?ws=' . $version;
        if (\Dcp\Ui\UIGetAssetPath::isInDebug()) {
            $js["iuser"] = '/uiAssets/Families/iuser/debug/iuser.js?ws=' . $version;
        }

        return $js;
    }


}
