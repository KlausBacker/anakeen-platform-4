<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Ui\BarMenu;
use Anakeen\Ui\ItemMenu;
use Anakeen\Ui\MenuTargetOptions;

class SendMailMenu extends \Anakeen\Ui\DefaultView
{
    public function getMenu(SmartElement $se): BarMenu
    {
        $menu = parent::getMenu($se);
        if (!$se->control("send")) {
            // ---------------------------------------
            // Mail standard (par défaut)
            $mail = new ItemMenu("zou-mail");

            $url = sprintf(
                "/api/v2/ui/mail/form/%d.html",
                $se->id
            );
            $mail->setUrl($url);

            $option = new MenuTargetOptions();
            $option->windowHeight = 400;
            $option->windowWidth = 700;
            $mail->setTarget("_dialog", $option);
            $mail->setTextLabel("Envoyer");
            $mail->setBeforeContent('<i class="fa fa-envelope" />');
            $menu->appendElement($mail);

            // ---------------------------------------
            // Mail custom (par défaut)
            $mail = new ItemMenu("zou-perso");
            $url = sprintf(
                "/api/v2/ui/mail/form/%d.html?mailTemplate=%s&keys=%s",
                $se->id,
                "MY_MESSAGE",
                urlencode(json_encode([
                    "my_you" => ContextManager::getCurrentUser()->getAccountName(),
                    "my_name" => "John",
                    "my_now" => strftime("%d %B %Y")
                ]))
            );
            $mail->setUrl($url);

            $option = new MenuTargetOptions();
            $option->windowHeight = 500;
            $option->windowWidth = 700;

            $mail->setTarget("_dialog", $option);
            $mail->setTextLabel("Message personnel");
            $mail->setBeforeContent('<i class="fa fa-envelope" />');

            $menu->appendElement($mail);

            // ---------------------------------------
            // Mail de changement de mot de passe
            $mail = new ItemMenu("zou-pass");
            $url = sprintf(
                "/api/v2/ui/mail/form/%d.html?mailTemplate=%s&keys=%s",
                ContextManager::getCurrentUser()->fid,
                "AUTH_TPLMAILASKPWD",
                urlencode(json_encode([
                    "LINK_CHANGE_PASSWORD" => "<h1>HOHO</h1>"
                ]))
            );
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
