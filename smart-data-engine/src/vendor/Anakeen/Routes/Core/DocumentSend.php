<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\SEManager;
use Anakeen\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\SmartElementManager;
use Anakeen\SmartStructures\Mailtemplate\MailTemplateHooks;
use SmartStructure\Fields\Mail as MailAttr;
use SmartStructure\Fields\Mailtemplate;

/**
 *
 * @description Send document by email
 * @use         by route POST /api/v2/smart-elements/{docid}/send/
 */
class DocumentSend
{
    protected $targetDocumentId;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);
        $data = $this->doRequest($request, $msg);
        return ApiV2Response::withData($response, $data, [$msg]);
    }

    protected function initParameters($args)
    {
        $this->targetDocumentId = $args["docid"];
    }

    /**
     * @param \Slim\Http\request $request
     * @param ApiMessage $msg
     * @return array
     * @throws Exception
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Database\Exception
     */
    protected function doRequest(\Slim\Http\request $request, &$msg)
    {
        $targetDocument = SmartElementManager::getDocument($this->targetDocumentId);
        if (!$targetDocument) {
            throw new Exception(sprintf("Unknow document #%s", $this->targetDocumentId));
        }

        if ($err = $targetDocument->control("send")) {
            throw new Exception($err);
        }


        $subject = $request->getParam(MailAttr::mail_subject);
        /* @TODO use body */
        $body = $request->getParam(MailAttr::mail_body);
        $from = $request->getParam(MailAttr::mail_from);

        $userinfo = null;

        $recipients = $request->getParam(MailAttr::mail_recip);
        $copymode = $request->getParam(MailAttr::mail_copymode);

        $address = ["to" => [], "cc" => [], "bcc" => []];
        foreach ($recipients as $k => $mailAddress) {
            $sendFormat = $copymode[$k];
            $mailAddress = trim($mailAddress);
            if ($mailAddress) {
                $address[$sendFormat][] = $mailAddress;
            }
        }

        if (empty($address)) {
            throw new Exception(___("No mail address set", "mail"));
        }

        $mailStructure = SEManager::getFamily("MAIL");
        $mtDefaultId = $mailStructure->getFamilyParameterValue(MailAttr::mail_tpl_default, "MAILTEMPLATE_DEFAULT");
        $mt = SEManager::getDocument($mtDefaultId);
        if ($mt) {
            $keys["state"] = htmlspecialchars($targetDocument->getStepLabel());

            // Send new body
            $mt->setValue(Mailtemplate::tmail_body, $body);
            /** @var MailTemplateHooks $mt */
            $mailMessage = $mt->getMailMessage($targetDocument, $keys);

            if ($from) {
                $mailMessage->setSender($from);
            }

            if ($subject) {
                $mailMessage->setSubject($subject);
            } else {
                $mailMessage->setSubject($targetDocument->getTitle());
            }
            $mailMessage->to = [];
            foreach ($address["to"] as $to) {
                if ($to) {
                    $mailMessage->addTo($to);
                }
            }
            $mailMessage->cc = [];
            foreach ($address["cc"] as $cc) {
                if ($cc) {
                    $mailMessage->addCc($cc);
                }
            }
            $mailMessage->bcc = [];
            foreach ($address["bcc"] as $bcc) {
                if ($bcc) {
                    $mailMessage->addBcc($bcc);
                }
            }

            $err = $mailMessage->send();
            if ($err) {
                $exc = new Exception($err);
                $exc->setUserMessage($err);
                throw $exc;
            }

            $msg = new ApiMessage(sprintf(___("\"%s\" has been sended.", "mail"), $targetDocument->getTitle()));

            return ["adresses" => $address, "subject" => $mailMessage->subject,
                "closingText" => ___("Closing in %ds", "mail"),
                "statusText"=>___("Sended", "mail")];
        }


        throw new Exception(sprintf("Mail template \"%s\" not found", $mtDefaultId));
    }
}
