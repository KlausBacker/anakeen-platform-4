<?php

namespace Anakeen\SmartStructures\Mailtemplate;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\SmartElementManager;

class SentMessage
{

    /**
     * record message sent from mail template
     *
     * @param                       $to
     * @param                       $from
     * @param                       $cc
     * @param                       $bcc
     * @param                       $subject
     * @param \Anakeen\Mail\Message $mimemail
     * @param SmartElement          $doc
     *
     * @return string
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public static function createSentMessage($to, $from, $cc, $bcc, $subject, \Anakeen\Mail\Message &$mimemail, &$doc = null)
    {
        $err = '';
        $msg = SmartElementManager::createDocument("SENTMESSAGE");
        /** @var \SmartStructure\Sentmessage $msg */
        if ($msg) {
            /* Drop the display name if present, and keep only the mail address */
            try {
                $mailAddrParser = new \Anakeen\Mail\MailAddrParser();
                $res = $mailAddrParser->parse($from);
                if (count($res) > 0) {
                    $from = $res[0]->address;
                }
            } catch (\Anakeen\Mail\MailAddrParserException $e) {
            }
            $msg->setValue("emsg_from", $from);
            $msg->setValue("emsg_date", \Anakeen\Core\Utils\Date::getNow());
            $msg->setValue("emsg_subject", $subject);
            /**
             * @var SmartElement $doc
             */
            if ($doc && $doc->id) {
                $msg->setValue("emsg_refid", $doc->id);
                $msg->profid = $doc->profid;
            }
            foreach (explode(',', $to) as $v) {
                if ($v) {
                    $msg->addArrayRow("emsg_t_recipient", array(
                        "emsg_sendtype" => "to",
                        "emsg_recipient" => $v
                    ));
                }
            }
            foreach (explode(',', $cc) as $v) {
                if ($v) {
                    $msg->addArrayRow("emsg_t_recipient", array(
                        "emsg_sendtype" => "cc",
                        "emsg_recipient" => $v
                    ));
                }
            }
            foreach (explode(',', $bcc) as $v) {
                if ($v) {
                    $msg->addArrayRow("emsg_t_recipient", array(
                        "emsg_sendtype" => "bcc",
                        "emsg_recipient" => $v
                    ));
                }
            }


            /**
             * @var \Anakeen\Mail\DataSource[] $partList
             */
            $partList = array();
            if (isset($mimemail->body)) {
                $partList[] = $mimemail->body;
            }
            if (count($mimemail->bodyRelated) > 0) {
                foreach ($mimemail->bodyRelated as $part) {
                    $partList[] = $part;
                }
            }
            if (isset($mimemail->altBody)) {
                $partList[] = $mimemail->altBody;
            }
            if (count($mimemail->attachments) > 0) {
                foreach ($mimemail->attachments as $part) {
                    $partList[] = $part;
                }
            }
            /**
             * @var \Anakeen\Mail\DataSource $textPart
             */
            $textPart = null;
            /**
             * @var \Anakeen\Mail\DataSource $htmlPart
             */
            $htmlPart = null;
            /**
             * @var \Anakeen\Mail\DataSource[] $otherPartList
             */
            $otherPartList = array();
            foreach ($partList as $i => $part) {
                if (!isset($textPart) && isset($part) && $part->getMimeType() == 'text/plain') {
                    $textPart = $part;
                } elseif (!isset($htmlPart) && isset($part) && $part->getMimeType() == 'text/html') {
                    $htmlPart = $part;
                } else {
                    $otherPartList[] = $part;
                }
            }
            /* Store text part */
            if ($textPart !== null) {
                $data = $textPart->getData();
                $msg->setValue(\SmartStructure\Fields\Sentmessage::emsg_textbody, $data);
            }
            /* Store html part */
            if ($htmlPart !== null) {
                $data = $htmlPart->getData();
                $msg->setValue(\SmartStructure\Fields\Sentmessage::emsg_htmlbody, $data);
            }
            /* Store remaining parts */
            foreach ($otherPartList as $i => $part) {
                $tmpfile = tempnam(ContextManager::getTmpDir(), 'Body_getFile');
                if ($tmpfile === false) {
                    break;
                }
                if (file_put_contents($tmpfile, $part->getData()) === false) {
                    unlink($tmpfile);
                    break;
                }
                $msg->setFile(\SmartStructure\Fields\Sentmessage::emsg_attach, $tmpfile, $part->getName(), $i);
                unlink($tmpfile);
            }

            $err = $msg->add();
            if ($err != '') {
                return $err;
            }

            if ($htmlPart !== null) {
                $htmlBody = $htmlPart->getData();
                // Re-link the HTML part CIDs
                foreach ($otherPartList as $i => $part) {
                    if (isset($part->cid)) {
                        $htmlBody = str_replace(sprintf("cid:%s", $part->cid), $msg->getfileLink('emsg_attach', $i), $htmlBody);
                    }
                }

                $msg->disableAccessControl(true);
                $msg->setValue('emsg_htmlbody', $htmlBody);
                $err = $msg->modify(true);
                $msg->disableAccessControl(false);
            }
        }
        return $err;
    }
}
