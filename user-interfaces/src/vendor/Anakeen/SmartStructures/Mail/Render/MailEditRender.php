<?php

namespace Anakeen\SmartStructures\Mail\Render;

use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Exception;
use Anakeen\SmartElementManager;
use Anakeen\SmartStructures\Mailtemplate\MailTemplateHooks;
use Anakeen\Ui\BarMenu;
use Anakeen\Ui\CommonRenderOptions;
use Anakeen\Ui\DefaultConfigEditRender;
use Anakeen\Ui\HtmltextRenderOptions;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Mail as MyAttr;
use SmartStructure\Fields\Mailtemplate;

class MailEditRender extends DefaultConfigEditRender
{

    public function getLabel(SmartElement $document = null)
    {
        return "Mail Edit";
    }

    public function getOptions(SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $targetId = $this->customClientData["targetDocument"] ?: null;
        if ($targetId) {
            $target = SEManager::getDocument($targetId);
            if ($target) {
                $options->text(MyAttr::mail_subject)->setPlaceHolder($target->getTitle());
                $this->initFormMailValues($target, $document);
            }
        }

        //$options->enum(MyAttr::mail_sendformat)->setDisplay(\Anakeen\Ui\EnumRenderOptions::boolDisplay);
        $options->enum(MyAttr::mail_copymode)->setAttributeLabel(" ");
        $options->enum(MyAttr::mail_recip)->setAttributeLabel(" ");
        $options->enum(MyAttr::mail_copymode)->setPlaceHolder(___("Send type", "mail"));
        $options->enum(MyAttr::mail_copymode)->setDisplay(\Anakeen\Ui\EnumRenderOptions::horizontalDisplay);
        $options->enum(MyAttr::mail_format)->setDisplay(\Anakeen\Ui\EnumRenderOptions::horizontalDisplay);
        $options->enum(MyAttr::mail_savecopy)
            ->setDisplay(\Anakeen\Ui\EnumRenderOptions::horizontalDisplay)
            ->setLabelPosition(CommonRenderOptions::leftPosition);
        $options->enum()->displayDeleteButton(false);
        $options->arrayAttribute(MyAttr::mail_dest)->setRowMinDefault(1);

        $options->htmltext(MyAttr::mail_body)->setToolbar(HtmltextRenderOptions::basicToolbar)->setHeight("auto")->setLabelPosition(CommonRenderOptions::nonePosition);
        $options->frame(MyAttr::mail_fr_cm)->setLabelPosition(CommonRenderOptions::nonePosition);
        $options->frame(MyAttr::mail_fr)->setLabelPosition(CommonRenderOptions::nonePosition);
        $options->arrayAttribute(MyAttr::mail_dest)->setLabelPosition(CommonRenderOptions::nonePosition);
        $options->arrayAttribute(MyAttr::mail_subject)->setLabelPosition(CommonRenderOptions::leftPosition);
        $options->arrayAttribute(MyAttr::mail_dest)->disableRowDel(true)->disableRowMove(true);

        return $options;
    }

    public function getVisibilities(
        \Anakeen\Core\Internal\SmartElement $document,
        \SmartStructure\Mask $mask = null
    ): RenderAttributeVisibilities {
        $vis = parent::getVisibilities($document, $mask);
        $vis->setVisibility(MyAttr::mail_recipid, RenderAttributeVisibilities::HiddenVisibility);
        $vis->setVisibility(MyAttr::mail_sendformat, RenderAttributeVisibilities::HiddenVisibility);
        $vis->setVisibility(MyAttr::mail_format, RenderAttributeVisibilities::HiddenVisibility);
        $vis->setVisibility(MyAttr::mail_savecopy, RenderAttributeVisibilities::HiddenVisibility);
        return $vis;
    }


    public function getMenu(SmartElement $document): BarMenu
    {
        $menu = parent::getMenu($document);

        $items = $menu->getElements();
        foreach ($items as $item) {
            $id = $item->getId();
            if ($id !== "close") {
                $menu->removeElement($id);
            }
        }

        $mail = new \Anakeen\Ui\ItemMenu("sendmail");
        $mail->setUrl("#action/sendmail");

        $mail->setTextLabel(___("Send", "mail"));
        $mail->setBeforeContent('<i class="fa fa-send" />');

        $menu->appendElement($mail);
        return $menu;
    }


    public function getTemplates(SmartElement $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["body"]["content"] = "{{> content}} {{> menu}} {{> footer}}";


        return $templates;
    }


    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences();
        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        $js["mailEdit"] = $path["MailEdit"]["js"];

        return $js;
    }

    protected function initFormMailValues(SmartElement $target, SmartElement $mailForm)
    {
        $mailForm->title = sprintf(___("Send \"%s\"", "mail"), $target->getTitle());


        $extraKeys = [];
        $mailTemplateId = "MAILTEMPLATE_DEFAULT";
        if (!empty($this->customClientData["mailTemplate"])) {
            if (is_string($this->customClientData["mailTemplate"])) {
                // Simple config
                $mailTemplateId = $this->customClientData["mailTemplate"];
            } else {
                // Extended config
                if (!empty($this->customClientData["mailTemplate"]["name"])) {
                    $mailTemplateId = $this->customClientData["mailTemplate"]["name"];
                }
                if (!empty($this->customClientData["mailTemplate"]["keys"])) {
                    $extraKeys = $this->customClientData["mailTemplate"]["keys"];
                    if (!is_array($extraKeys)) {
                        throw new Exception("Syntax error for keys use by mail template key");
                    }
                }
                if (!empty($this->customClientData["mailTemplate"]["selink"])) {
                    ContextParameterManager::setVolatile(
                        \Anakeen\Core\Settings::NsSde,
                        "CORE_MAILACTIONURL",
                        $this->customClientData["mailTemplate"]["selink"]
                    );
                }
            }
        }
        $mailTemplate = SmartElementManager::getDocument($mailTemplateId);
        if ($mailTemplate) {
            // Use copy to rewrite image url
            $mailTemplate->disableAccessControl();
            $copy = $mailTemplate->duplicate(true);
            $copy->store();
            $mailTemplate->restoreAccessControl();
            $keys["state"] = htmlspecialchars($target->getStepLabel());

            $mailForm->setValue(MyAttr::mail_template, $mailTemplate->id);
            foreach ($extraKeys as $k => $v) {
                $keys[$k] = $v;
            }

            /** @var MailTemplateHooks $mailTemplate */
            $mailMessage = $mailTemplate->getMailMessage($target, $keys);

            $subject = $mailMessage->subject;
            if ($subject) {
                $mailForm->setValue(MyAttr::mail_subject, $subject);
            }
            $body = $mailMessage->body;
            if ($body) {
                // need magic set to get original image url
                $copy->setValue(Mailtemplate::tmail_body, $body->getData());

                /** @noinspection PhpUndefinedFieldInspection */
                $mailForm->mail_body = $copy->getRawValue(Mailtemplate::tmail_body);
                // Need to reparse due to images in htmltext
                $mailForm->mail_body = \Anakeen\Core\Utils\HtmlClean::normalizeHTMLFragment(
                    $mailForm->mail_body,
                    $err,
                    [
                        'initid' => $copy->initid,
                        "revision" => $copy->revision,
                        "attrid" => Mailtemplate::tmail_body
                    ]
                );

                $copy->store();
            }

            $tos = $mailMessage->to;
            foreach ($tos as $to) {
                $mailForm->addArrayRow(
                    MyAttr::mail_dest,
                    [
                        MyAttr::mail_copymode => "to",
                        MyAttr::mail_recip => (string)$to
                    ]
                );
            }
            $ccs = $mailMessage->cc;
            foreach ($ccs as $cc) {
                $mailForm->addArrayRow(
                    MyAttr::mail_dest,
                    [
                        MyAttr::mail_copymode => "cc",
                        MyAttr::mail_recip => (string)$cc
                    ]
                );
            }
            $bccs = $mailMessage->bcc;
            foreach ($bccs as $bcc) {
                $mailForm->addArrayRow(
                    MyAttr::mail_dest,
                    [
                        MyAttr::mail_copymode => "bcc",
                        MyAttr::mail_recip => (string)$bcc
                    ]
                );
            }
        } else {
            throw new Exception(sprintf("Cannot access mail template \"%s\".", $mailTemplateId));
        }
    }

    public function getEtag(\Anakeen\Core\Internal\SmartElement $document)
    {
        return null;
    }
}
