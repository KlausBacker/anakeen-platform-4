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
    use IuserMessage;

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

    /**
     * @param \Anakeen\Core\Internal\SmartElement $smartElement
     * @return \Dcp\Ui\RenderAttributeVisibilities
     * @throws \Dcp\Ui\Exception
     */
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $smartElement)
    {
        $visibilities = parent::getVisibilities($smartElement);

        if ($smartElement->getRawValue("us_whatid") == \Anakeen\Core\Account::ANONYMOUS_ID) {
            // Anonymous has no password
            $visibilities->setVisibility(myAttributes::us_passwd1, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
            $visibilities->setVisibility(myAttributes::us_passwd2, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        }

        if (!$smartElement->getRawValue(myAttributes::us_fr_security)) {
            $visibilities->setVisibility(myAttributes::us_fr_security, \Dcp\Ui\RenderAttributeVisibilities::ReadWriteVisibility);
        }
        return $visibilities;
    }

    /**
     * Add default group to customServerData on the first
     * Handle setGroup on the second
     *
     * @param \Anakeen\Core\Internal\SmartElement $smartElement
     * @param $data
     * @return mixed
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function setCustomClientData(\Anakeen\Core\Internal\SmartElement $smartElement, $data)
    {
        if (!$smartElement->getPropertyValue("initid") && isset($data["defaultGroup"])) {
            $this->defaultGroup = $data["defaultGroup"];
        }
        if (isset($data["setGroup"])) {
            $groupSE = SmartElementManager::getDocument($data["setGroup"]);
            /* @var $groupSE \SmartStructure\Igroup */
            $groupSE->insertDocument($smartElement->getPropertyValue("initid"));
        }
        return $data;
    }

    /**
     * Display warning message and set default group
     *
     * @param \Anakeen\Core\Internal\SmartElement $smartElement
     * @return mixed
     */
    public function getCustomServerData(\Anakeen\Core\Internal\SmartElement $smartElement)
    {
        $data = parent::getCustomServerData($smartElement);
        $data["messages"] = $this->getUserMessage($smartElement);
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
