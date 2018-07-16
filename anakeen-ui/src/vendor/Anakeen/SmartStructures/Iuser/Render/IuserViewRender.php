<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Iuser\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Router\Exception;
use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\Routes\Ui\CallMenuResponse;
use Anakeen\Ui\DefaultConfigViewRender;
use Dcp\Ui\ArrayRenderOptions;
use Dcp\Ui\BarMenu;
use Dcp\Ui\CallableMenu;
use Dcp\Ui\RenderOptions;
use SmartStructure\Fields\Iuser as myAttributes;
use SmartStructure\Iuser;

class IuserViewRender extends DefaultConfigViewRender
{
    use IuserMessage;

    public function getOptions(SmartElement $document): RenderOptions
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
        $options->frame(myAttributes::us_fr_sysident)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => $break2]
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
        $options->arrayAttribute(myAttributes::us_t_roles)
            ->setCollapse(ArrayRenderOptions::collapseNone)
            ->showEmptyContent(xml_entity_encode(___("No roles", "smart iuser")));

        $options->arrayAttribute(myAttributes::us_groups)
            ->setCollapse(ArrayRenderOptions::collapseNone)
            ->showEmptyContent(xml_entity_encode(___("No groups", "smart iuser")));

        return $options;
    }

    public function getMenu(SmartElement $smartElement): BarMenu
    {
        $menus = parent::getMenu($smartElement);


        try {
            $vidMenuEgroup = $menus->getElement("vid-EGROUP");
            $vidMenuESubstitute = $menus->getElement("vid-ESUBSTITUTE");

            $listMenu = new \Dcp\Ui\ListMenu("accountManagement", ___("Account management", "smart iuser"));


            if ($vidMenuEgroup) {
                $listMenu->appendElement($vidMenuEgroup);
                $menus->removeElement("vid-EGROUP");
            }
            if ($vidMenuESubstitute) {
                $listMenu->appendElement($vidMenuESubstitute);
                $menus->removeElement("vid-ESUBSTITUTE");
            }

            /* @var \SmartStructure\Iuser $smartElement */
            if ($this->checkMenuAccess($smartElement, "resetLoginFailure")) {
                // $menu = new \Dcp\UI\ItemMenu("resetLoginFailure", ___("Reset login failure", "iuser_ui"));
                //  $menu->setUrl("#action/customIuserMenu:resetLoginFailure");

                $menu = new CallableMenu("resetLoginFailure", ___("Reset login failure", "smart iuser"));
                $menu->setCallable(function () use ($smartElement): CallMenuResponse {
                    /* @var \SmartStructure\Iuser $smartElement */
                    $err = $smartElement->resetLoginFailure();
                    $msg = new ApiMessage();
                    if ($err) {
                        $msg->type = ApiMessage::ERROR;
                        $msg->contentText = sprintf(___($err, "smart iuser"));
                    } else {
                        $msg->type = ApiMessage::SUCCESS;
                        $msg->contentText = sprintf(___("Login fail count reseted", "smart iuser"));
                    }
                    $response = new CallMenuResponse();
                    $response->setReload(true);

                    return $response->setMessage($msg);
                });
                $listMenu->appendElement($menu);
            }


            if ($this->checkMenuAccess($smartElement, "activateAccount")) {
                // $menu = new \Dcp\UI\ItemMenu("activateAccount", ___("Activate account", "smart iuser"));
                //  $menu->setUrl("#action/customIuserMenu:activateAccount");
                $menu = new CallableMenu("activateAccount", ___("Activate account", "smart iuser"));
                $menu->setCallable(function () use ($smartElement): CallMenuResponse {
                    /* @var \SmartStructure\Iuser $smartElement */
                    $err = $smartElement->activateAccount();
                    $msg = new ApiMessage();
                    if ($err) {
                        $msg->type = ApiMessage::ERROR;
                        $msg->contentText = sprintf(___($err, "smart iuser"));
                    } else {
                        $msg->type = ApiMessage::SUCCESS;
                        $msg->contentText = sprintf(___("Account has been activated", "smart iuser"));
                    }
                    $response = new CallMenuResponse();
                    $response->setReload(true);

                    return $response->setMessage($msg);
                });

                $listMenu->appendElement($menu);
            }

            if ($this->checkMenuAccess($smartElement, "deactivateAccount")) {
                // $menu = new \Dcp\UI\ItemMenu("deactivateAccount", ___("Deactivate account", "smart iuser"));
                // $menu->setUrl("#action/customIuserMenu:deactivateAccount");
                $menu = new CallableMenu("activateAccount", ___("Deactivate account", "smart iuser"));
                $menu->setCallable(function () use ($smartElement): CallMenuResponse {
                    /* @var \SmartStructure\Iuser $smartElement */
                    $err = $smartElement->deactivateAccount();
                    $msg = new ApiMessage();
                    if ($err) {
                        $msg->type = ApiMessage::ERROR;
                        $msg->contentText = sprintf(___($err, "smart iuser"));
                    } else {
                        $msg->type = ApiMessage::SUCCESS;
                        $msg->contentText = sprintf(___("Account has been deactivated", "smart iuser"));
                    }
                    $response = new CallMenuResponse();
                    $response->setReload(true);

                    return $response->setMessage($msg);
                });
                $listMenu->appendElement($menu);
            }

            if ($listMenu->length() > 0) {
                $menus->insertAfter("modify", $listMenu);
            }
        } catch (\Exception $e) {
        }

        return $menus;
    }

    public function getJsReferences(SmartElement $smartElement = null)
    {
        $js = parent::getJsReferences();
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");

        $js["dduiMenu"] = '/uiAssets/Families/iuser/prod/iuser.js?ws=' . $version;
        if (\Dcp\Ui\UIGetAssetPath::isInDebug()) {
            $js["dduiMenu"] = '/uiAssets/Families/iuser/debug/iuser.js?ws=' . $version;
        }

        return $js;
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
        /**
         * @var Iuser $smartElement
         */
        $messages = parent::getMessages($smartElement);

        // Update role array to display : can be not up to date if parent group are moved or if they have new roles
        $smartElement->refreshRoles();

        return array_merge($messages, $this->getAccountMessages($smartElement), $this->getUserMessages($smartElement));
    }
}
