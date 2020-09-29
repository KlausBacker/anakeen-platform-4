<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Iuser\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\SmartElementManager;
use Anakeen\Ui\CommonRenderOptions;
use Anakeen\Ui\DefaultConfigEditRender;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Iuser as myAttributes;

class IuserEditRender extends DefaultConfigEditRender
{
    use IuserMessage;

    protected $defaultGroup;

    public function getOptions(SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        if (!$document->getRawValue(myAttributes::us_login)) {
            $options->text(myAttributes::us_login)->setInputTooltip(
                sprintf(___(
                    "<p>Set to \"<b>-</b>\" (<i>minus</i>) to explicit create user without login</p>",
                    " smart iuser"
                ))
            );
        }
        if ($document->getRawValue(myAttributes::us_daydelay)) {
            $options->text(myAttributes::us_daydelay)->setInputTooltip(
                sprintf(___("<p>Set to \"<b>-1</b>\"  to cancel expiration</p>", " smart iuser"))
            );
        }

        $options->frame(myAttributes::us_fr_substitute)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => "45rem"]
            ]
        );

        $substituteMessage = $this->getSubstituteMessages($document);
        if ($substituteMessage) {
            $options->frame(myAttributes::us_fr_substitute)->setDescription(
                $substituteMessage,
                CommonRenderOptions::bottomPosition
            );
        }

        return $options;
    }

    /**
     * @param SmartElement $smartElement
     * @return \Anakeen\Ui\RenderAttributeVisibilities
     * @throws \Anakeen\Ui\Exception
     */
    public function getVisibilities(
        SmartElement $smartElement,
        \SmartStructure\Mask $mask = null
    ): RenderAttributeVisibilities {
        $visibilities = parent::getVisibilities($smartElement, $mask);

        if ($smartElement->getRawValue("us_whatid") == \Anakeen\Core\Account::ANONYMOUS_ID) {
            // Anonymous has no password
            $visibilities->setVisibility(
                myAttributes::us_passwd1,
                \Anakeen\Ui\RenderAttributeVisibilities::HiddenVisibility
            );
            $visibilities->setVisibility(
                myAttributes::us_passwd2,
                \Anakeen\Ui\RenderAttributeVisibilities::HiddenVisibility
            );
        }

        if (!$smartElement->getRawValue(myAttributes::us_fr_security)) {
            $visibilities->setVisibility(
                myAttributes::us_fr_security,
                \Anakeen\Ui\RenderAttributeVisibilities::ReadWriteVisibility
            );
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

        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        $js["iuser"] = $path["Iuser"]["js"];

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
