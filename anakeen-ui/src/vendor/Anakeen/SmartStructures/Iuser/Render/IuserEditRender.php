<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Iuser\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\SmartElementManager;
use Anakeen\Ui\DefaultConfigEditRender;
use Dcp\Ui\RenderAttributeVisibilities;
use Dcp\Ui\RenderOptions;
use Dcp\Ui\UIGetAssetPath;
use \SmartStructure\Fields\Iuser as myAttributes;

class IuserEditRender extends DefaultConfigEditRender
{
    use IuserMessage;

    protected $defaultGroup;

    public function getOptions(SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        $options->frame(myAttributes::us_fr_security)->setTemplate(
            <<< 'HTML'
            <span class="us_accexpiredate">{{{attributes.us_accexpiredate.label}}}</span> : {{{attributes.us_accexpiredate.htmlContent}}}
HTML
        );

        if (!$document->getRawValue(myAttributes::us_login)) {
            $options->text(myAttributes::us_login)->setInputTooltip(
                sprintf(___("<p>Set to \"<b>-</b>\" (<i>minus</i>) to explicit create user without login</p>", " smart iuser"))
            );
        }
        if ($document->getRawValue(myAttributes::us_daydelay)) {
            $options->text(myAttributes::us_daydelay)->setInputTooltip(
                sprintf(___("<p>Set to \"<b>-1</b>\"  to cancel expiration</p>", " smart iuser"))
            );
        }
        return $options;
    }

    /**
     * @param SmartElement $smartElement
     * @return \Dcp\Ui\RenderAttributeVisibilities
     * @throws \Dcp\Ui\Exception
     */
    public function getVisibilities(SmartElement $smartElement, \SmartStructure\Mask $mask = null) : RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($smartElement, $mask);

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
     * @param SmartElement $userAccount
     * @param              $data
     * @return mixed
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function setCustomClientData(SmartElement $userAccount, $data)
    {
        if (!$userAccount->getPropertyValue("initid") && isset($data["defaultGroup"])) {
            $this->defaultGroup = $data["defaultGroup"];
        }
        if (isset($data["setGroup"])) {
            $groupSE = SmartElementManager::getDocument($data["setGroup"]);
            /* @var $groupSE \SmartStructure\Igroup */
            $groupSE->insertDocument($userAccount->getPropertyValue("initid"));
        }
        return $data;
    }

    /**
     * Display warning message and set default group
     *
     * @param SmartElement $smartElement
     * @return mixed
     */
    public function getCustomServerData(SmartElement $smartElement)
    {
        $data = parent::getCustomServerData($smartElement);
        $data["EDIT_GROUP"] = true;
        if ($this->defaultGroup) {
            $data["defaultGroup"] = $this->defaultGroup;
        }
        $this->deleteIndirectRoles($smartElement);
        return $data;
    }

    protected function deleteIndirectRoles(SmartElement $iuser)
    {
        $iuser->disableAccessControl();
        $allRoles = $iuser->getArrayRawValues("us_t_roles");
        $iuser->clearArrayValues("us_t_roles");
        // get direct system role ids
        $roles = array();
        foreach ($allRoles as $arole) {
            if ($arole["us_rolesorigin"] != "group") {
                $roles[] = $arole["us_roles"];
            }
        }
        $iuser->setValue("us_roles", $roles);
        $iuser->restoreAccessControl();
    }

    public function getJsReferences(SmartElement $smartElement = null)
    {
        $js = parent::getJsReferences();

        $js["iuser"] = UIGetAssetPath::getCustomAssetPath('/uiAssets/Families/iuser/prod/iuser.js');
        if (UIGetAssetPath::isInDebug()) {
            $js["iuser"] = UIGetAssetPath::getCustomAssetPath('/uiAssets/Families/iuser/debug/iuser.js');
        }

        return $js;
    }

    /**
     * Add warning messages to display
     * @param SmartElement $smartElement
     * @return array
     */
    public function getMessages(SmartElement $smartElement)
    {
        $messages = parent::getMessages($smartElement);
        return array_merge($messages, $this->getUserMessages($smartElement));
    }
}
