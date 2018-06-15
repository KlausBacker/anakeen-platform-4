<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Iuser\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Router\Exception;
use Anakeen\Ui\DefaultConfigViewRender;
use SmartStructure\Attributes\Iuser as myAttributes;

class IuserViewRender extends DefaultConfigViewRender
{
    use IuserMessage;

    public function getOptions(SmartElement $document)
    {
        $options = parent::getOptions($document);

        $break2 = "50rem";
        $break3 = "70rem";
        $options->frame(myAttributes::us_fr_ident)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => $break2, "maxWidth" => $break3],
                ["number" => 3, "minWidth" => $break3, "grow" => false]
            ]
        );
        $options->frame(myAttributes::us_fr_security)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => $break2]
            ]
        );

        $options->frame(myAttributes::us_fr_intranet)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => $break3]
            ]
        );

        return $options;
    }

    public function getMenu(SmartElement $smartElement)
    {
        $menus = parent::getMenu($smartElement);

        $listMenu = new \Dcp\UI\ListMenu("accountManagement", ___("Account management"));

        try {
            $listMenu->appendElement($menus->getElement("vid-EGROUP"));
            $menus->removeElement("vid-EGROUP");
            $listMenu->appendElement($menus->getElement("vid-ESUBSTITUTE"));
            $menus->removeElement("vid-ESUBSTITUTE");

            /* @var $smartElement \SmartStructure\Iuser */
            if ($this->checkMenuAccess($smartElement, "resetLoginFailure")) {
                $menu = new \Dcp\UI\ItemMenu("resetLoginFailure", ___("Reset login failure", "iuser_ui"));
                $menu->setUrl("#action/customIuserMenu:resetLoginFailure");
                $listMenu->appendElement($menu);
            }

            if ($this->checkMenuAccess($smartElement, "activateAccount")) {
                $menu = new \Dcp\UI\ItemMenu("activateAccount", ___("Activate account", "iuser_ui"));
                $menu->setUrl("#action/customIuserMenu:activateAccount");
                $listMenu->appendElement($menu);
            }

            if ($this->checkMenuAccess($smartElement, "deactivateAccount")) {
                $menu = new \Dcp\UI\ItemMenu("deactivateAccount", ___("Deactivate account", "iuser_ui"));
                $menu->setUrl("#action/customIuserMenu:deactivateAccount");
                $listMenu->appendElement($menu);
            }

            $menus->insertAfter("modify", $listMenu);
        } catch (\Exception $e) {
        }

        return $menus;
    }

    public function getJsReferences(SmartElement $smartElement = null)
    {
        $js = parent::getJsReferences();
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");

        $js["dduiMenu"] = '/uiAssets/Families/iuser/prod/iuser.js?ws=' . $version;
        if (\Dcp\Ui\UIGetAssetPath::isInDebug()) {
            $js["dduiMenu"] = '/uiAssets/Families/iuser/debug/iuser.js?ws=' . $version;
        }

        return $js;
    }

    public function getCustomServerData(SmartElement $smartElement)
    {
        $data = parent::getCustomServerData($smartElement);
        $data["ADD_CUSTOM_MENU"] = true;

        return $data;
    }

    protected function checkMenuAccess(SmartElement $smartElement, $menuId)
    {
        //Do not show if the right is not ok
        try {
            if (\Anakeen\Router\RouterAccess::hasPermission("admincenter:admin") === false) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        // Do not show the menu if the user has no edit rights on the document
        if ($smartElement->canEdit() != '') {
            return false;
        }
        // Do not show the menu on the 'admin' user
        if ($smartElement->getRawValue('us_whatid') == 1) {
            return false;
        }
        if ($menuId === "resetLoginFailure" && $smartElement->getRawValue("us_loginfailure") > 0) {
            return true;
        }
        if ($menuId === "activateAccount" && $smartElement->getRawValue('us_status', 'A') != 'A') {
            return true;
        }
        if ($menuId === "deactivateAccount" && $smartElement->getRawValue('us_status', 'A') == 'A') {
            return true;
        }
        return false;
    }

    /**
     * Add warning messages to display
     *
     * @param SmartElement $smartElement
     * @return array
     */
    public function getMessages(SmartElement $smartElement)
    {
        $messages = parent::getMessages($smartElement);
        return array_merge($messages, $this->getUserMessages($smartElement));
    }
}
