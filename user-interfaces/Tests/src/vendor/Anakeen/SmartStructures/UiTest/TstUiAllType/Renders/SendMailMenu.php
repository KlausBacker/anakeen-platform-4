<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Ui\BarMenu;
use Anakeen\Ui\HtmltextRenderOptions;
use Anakeen\Ui\ItemMenu;
use Anakeen\Ui\MenuTargetOptions;
use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class SendMailMenu extends \Anakeen\Ui\DefaultView
{


    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);


        $options->frame()->setCollapse(true);
        $options->frame(myAttributes::test_ddui_all__fr_text)->setCollapse(false);
        $options->htmltext()->setToolbar(HtmltextRenderOptions::basicToolbar);


        return $options;
    }


    public function getMenu(SmartElement $document): BarMenu
    {
        $menu = parent::getMenu($document);
        if (!$document->control("send")) {
            // Mail standard (par défaut)
            $mail = new ItemMenu("zou-mail");
            $url = "/api/v2/smart-elements/MAIL/views/!defaultCreation.html";
            $url .= sprintf("?customClientData=%s", urlencode(json_encode(["targetDocument" => $document->initid])));
            $mail->setUrl($url);

            $option = new MenuTargetOptions();
            $option->windowHeight = 400;
            $option->windowWidth = 700;
            $mail->setTarget("_dialog", $option);
            $mail->setTextLabel("Mail par défaut");
            $mail->setBeforeContent('<i class="fa fa-envelope" />');

            $menu->appendElement($mail);

            // Mail de changement de mot de passe
            $mail = new ItemMenu("zou-pass");
            $url = "/api/v2/smart-elements/MAIL/views/!defaultCreation.html";
            $url .= sprintf("?customClientData=%s", urlencode(json_encode([
                "targetDocument" => ContextManager::getCurrentUser()->fid,
                "mailTemplate" => [
                    "name" => "AUTH_TPLMAILASKPWD",
                    "keys" => [
                        "LINK_CHANGE_PASSWORD" => "<h1>HOHO</h1>"
                    ]
                ]
            ])));
            $mail->setUrl($url);

            $option = new MenuTargetOptions();
            $option->windowHeight = 500;
            $option->windowWidth = 700;
            $mail->setTarget("_dialog", $option);
            $mail->setTextLabel("Mot de passe oublié");
            $mail->setBeforeContent('<i class="fa fa-envelope" />');

            $menu->appendElement($mail);
        }
        return $menu;
    }
}
