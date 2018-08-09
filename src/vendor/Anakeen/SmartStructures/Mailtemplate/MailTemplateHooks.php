<?php
/*
 * Mail template document
 * @author Anakeen
 * @package FDL
*/

/**
 * Mail template document
 */

namespace Anakeen\SmartStructures\Mailtemplate;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\IMailRecipient;
use Anakeen\Core\SEManager;
use Anakeen\Core\Utils\Postgres;
use Anakeen\LogManager;

class MailTemplateHooks extends \Anakeen\SmartElement

{
    /**
     * always show a user notification
     */
    const NOTIFY_SENDMAIL_ALWAYS = 'always';
    /**
     * only show a notification if an error occured
     */
    const NOTIFY_SENDMAIL_ERRORS_ONLY = 'errors only';
    /**
     * never show a notification
     */
    const NOTIFY_SENDMAIL_NEVER = 'never';
    /**
     * show notification according to CORE_NOTIFY_SENDMAIL parameter
     */
    const NOTIFY_SENDMAIL_AUTO = 'auto';

    public $ifiles = array();
    public $sendercopy = true;
    public $keys = array();

    protected $notifySendMail = self::NOTIFY_SENDMAIL_AUTO;
    protected $stopIfNoRecip = false;

    public function preEdition()
    {
        global $action;

        if ($mailfamily = $this->getRawValue("tmail_family", getHttpVars("TMAIL_FAMILY"))) {
            $action->parent->AddJsRef(htmlspecialchars("?app=FDL&action=FCKDOCATTR&famid=" . urlencode($mailfamily), ENT_QUOTES));
        }
    }

    /**
     * Check if the relation is correct and the attribute does exists
     *
     * @param string $values Relation to check
     * @param array  $doc    Field and values of document attributes
     * @return string Error if attribute not found, else empty string
     */
    private function checkAttributeExistsInRelation($values, array $doc)
    {
        $tattrid = explode(":", $values);
        if (count($tattrid) == 1) { //no relation
            if (!array_key_exists($tattrid[0], $doc)) {
                return sprintf(_("Send mail error : Attribute %s not found."), $tattrid[0]);
            }
            return "";
        }
        $lattrid = array_pop($tattrid); // last attribute
        foreach ($tattrid as $v) {
            if (!array_key_exists($v, $doc)) {
                return sprintf(_("Send mail error : Relation to attribute %s not found. Incorrect relation key: %s"), $lattrid, $v);
            }
            $docids = getLatestDocIds($this->dbaccess, array(
                $doc[$v]
            ));
            if (!$docids) {
                return sprintf(_("Send mail error : Relation to attribute %s not found. Relation key %s does'nt link to a document"), $lattrid, $v);
            }
            $doc = SEManager::getRawDocument(array_pop($docids), false);
            if (!$doc) {
                return sprintf(_("Send mail error : Relation to attribute %s not found. Relation key %s does'nt link to a document"), $lattrid, $v);
            }
        }
        if (!array_key_exists($lattrid, $doc)) {
            return sprintf(_("Send mail error : Attribute %s not found."), $lattrid);
        }
        return "";
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $doc  Document to use for complete mail
     * @param array                               $keys extra keys to complete mail body or subject
     *
     * @return \Dcp\Mail\Message (return null if no recipients)
     * @throws \Dcp\Exception
     */
    public function getMailMessage(\Anakeen\Core\Internal\SmartElement & $doc, $keys = array())
    {
        global $action;
        $this->keys = $keys;

        $message = new \Dcp\Mail\Message();

        $tdest = $this->getArrayRawValues("tmail_dest");

        $dest = array(
            "to" => array(),
            "cc" => array(),
            "bcc" => array(),
            "from" => array()
        );
        $from = trim($this->getRawValue("tmail_from"));
        if ($from) {
            $tdest[] = array(
                "tmail_copymode" => "from",
                "tmail_desttype" => $this->getRawValue("tmail_fromtype"),
                "tmail_recip" => $from
            );
        }
        $wdoc = null;
        if ($doc->wid) {
            /**
             * @var \Anakeen\SmartStructures\Wdoc\WDocHooks $wdoc
             */
            $wdoc = SEManager::getDocument($doc->wid);
        }
        $udoc = null;
        foreach ($tdest as $k => $v) {
            $toccbcc = $v["tmail_copymode"];
            $type = $v["tmail_desttype"];
            $mail = '';
            switch ($type) {
                case 'F': // fixed address
                    $mail = $v["tmail_recip"];
                    break;

                case 'A': // text attribute
                    $aid = strtok($v["tmail_recip"], " ");
                    $err = $this->checkAttributeExistsInRelation($aid, SEManager::getRawDocument($doc->initid));
                    if ($err) {
                        LogManager::error($err);
                        $doc->addHistoryEntry($err);
                        throw new \Dcp\Exception($err);
                    }
                    $mail = $doc->getRValue($aid);
                    break;

                case 'WA': // workflow text attribute
                    if ($wdoc) {
                        $aid = strtok($v["tmail_recip"], " ");
                        $err = $this->checkAttributeExistsInRelation($aid, SEManager::getRawDocument($wdoc->initid));
                        if ($err) {
                            LogManager::error($err);
                            $wdoc->addHistoryEntry($err);
                            throw new \Dcp\Exception($err);
                        }
                        $mail = $wdoc->getRValue($aid);
                    }
                    break;

                case 'E': // text parameter
                    $aid = strtok($v["tmail_recip"], " ");
                    if (!$doc->getAttribute($aid)) {
                        LogManager::error(sprintf(_("Send mail error : Parameter %s doesn't exists"), $aid));
                        $doc->addHistoryEntry(sprintf(_("Send mail error : Parameter %s doesn't exists"), $aid));
                        throw new \Dcp\Exception(sprintf(_("Send mail error : Parameter %s doesn't exists"), $aid));
                    }
                    $mail = $doc->getFamilyParameterValue($aid);
                    break;

                case 'WE': // workflow text parameter
                    if ($wdoc) {
                        $aid = strtok($v["tmail_recip"], " ");
                        if (!$wdoc->getAttribute($aid)) {
                            LogManager::error(sprintf(_("Send mail error : Parameter %s doesn't exists"), $aid));
                            $wdoc->addHistoryEntry(sprintf(_("Send mail error : Parameter %s doesn't exists"), $aid));
                            throw new \Dcp\Exception(sprintf(_("Send mail error : Parameter %s doesn't exists"), $aid));
                        }
                        $mail = $wdoc->getFamilyParameterValue($aid);
                    }
                    break;

                case 'DE': // param user relation
                case 'D': // user relations
                case 'WD': // user relations
                    if ($type == 'D' || $type == 'DE') {
                        $udoc = $doc;
                    } elseif ($wdoc) {
                        $udoc = $wdoc;
                    }
                    if ($udoc) {
                        $aid = strtok($v["tmail_recip"], " ");
                        if (!$udoc->getAttribute($aid) && !array_key_exists(strtolower($aid), $udoc->getParamAttributes())) {
                            LogManager::error(sprintf(_("Send mail error : Attribute %s not found"), $aid));
                            $doc->addHistoryEntry(sprintf(_("Send mail error : Attribute %s not found"), $aid));
                            throw new \Dcp\Exception(sprintf(_("Send mail error : Attribute %s not found"), $aid));
                        }
                        if ($type == 'DE') {
                            $vdocid = $udoc->getFamilyParameterValue($aid);
                        } else {
                            $vdocid = $udoc->getRawValue($aid); // for array of users
                            if ($udoc->getAttribute($aid)->isMultiple()) {
                                $vdocid = Postgres::stringToFlatArray($vdocid);
                            }
                        }

                        if (is_array($vdocid)) {
                            $tvdoc = $vdocid;
                            $tmail = array();
                            $it = new \DocumentList();
                            $it->addDocumentIdentifiers($tvdoc);
                            /**
                             * @var \SmartStructure\IUSER|\SmartStructure\IGROUP|\SmartStructure\ROLE $aDoc
                             */
                            foreach ($it as $aDoc) {
                                $umail = '';
                                if (method_exists($aDoc, "getMail")) {
                                    $umail = $aDoc->getMail();
                                }
                                if (!$umail) {
                                    $umail = $aDoc->getRawValue('us_mail', '');
                                }
                                if (!$umail) {
                                    $umail = $aDoc->getRawValue('grp_mail', '');
                                }
                                if ($umail) {
                                    $tmail[] = $umail;
                                }
                            }
                            $mail = implode(",", $tmail);
                        } else {
                            if (strpos($aid, ':')) {
                                $mail = $udoc->getRValue($aid);
                            } else {
                                if ($type == "DE") {
                                    /**
                                     * @var \SmartStructure\IUSER|\SmartStructure\IGROUP|\SmartStructure\ROLE $aDoc
                                     */
                                    $aDoc = SEManager::getDocument($vdocid);
                                    $mail = '';
                                    if (method_exists($aDoc, "getMail")) {
                                        $mail = $aDoc->getMail();
                                    }
                                    if (!$mail) {
                                        $mail = $aDoc->getRawValue('us_mail', '');
                                    }
                                    if (!$mail) {
                                        $mail = $aDoc->getRawValue('grp_mail', '');
                                    }
                                } else {
                                    $mail = $udoc->getRValue($aid . ':us_mail');
                                    if (!$mail) {
                                        $mail = $udoc->getRValue($aid . ':grp_mail');
                                    }
                                }
                            }
                        }
                    }
                    break;

                case 'P':
                    $aid = strtok($v["tmail_recip"], " ");
                    if (!\Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, $aid)) {
                        LogManager::error(sprintf(_("Send mail error : Parameter %s doesn't exists"), $aid));
                        $doc->addHistoryEntry(sprintf(_("Send mail error : Parameter %s doesn't exists"), $aid));
                        throw new \Dcp\Exception(sprintf(_("Send mail error : Parameter %s doesn't exists"), $aid));
                    }
                    $mail = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, $aid);
                    break;

                case 'RD':
                    $recipDocId = $v['tmail_recip'];
                    if (preg_match('/^(?P<id>\d+)/', $v['tmail_recip'], $m)) {
                        /**
                         * Extract document's id from tmail_recip value
                         */
                        $recipDocId = $m['id'];
                    }
                    /**
                     * @var IMailRecipient|\Anakeen\Core\Internal\SmartElement $recipientDoc
                     */
                    $recipientDoc = SEManager::getDocument($recipDocId, true);
                    if (!is_object($recipientDoc) || !$recipientDoc->isAlive()) {
                        $err = sprintf(_("Send mail error: recipient document '%s' does not exists."), $recipDocId);
                        LogManager::error($err);
                        $doc->addHistoryEntry($err);
                        throw new \Dcp\Exception($err);
                    }
                    if (!is_a($recipientDoc, IMailRecipient::class)) {
                        $err = sprintf(_("Send mail error: recipient document '%s' does not implements IMailRecipient interface."), $recipDocId);
                        LogManager::error($err);
                        $doc->addHistoryEntry($err);
                        throw new \Dcp\Exception($err);
                    }
                    $mail = $recipientDoc->getMail();
                    break;
            }
            if ($mail) {
                $dest[$toccbcc][] = str_replace(array("\n", "\r"), array(",", ""), $mail);
            }
        }
        $subject = $this->generateMailInstance($doc, $this->getRawValue("tmail_subject"));
        $subject = str_replace(array(
            "\n",
            "\r",
            "<BR>"
        ), array(
            " ",
            " ",
            ", "
        ), html_entity_decode($subject, ENT_COMPAT, "UTF-8"));
        $pfout = $this->generateMailInstance($doc, $this->getRawValue("tmail_body"), $this->getAttribute("tmail_body"));
        // delete empty address
        $ftMatch = function ($v) {
            return !preg_match("/^\s*$/", $v);
        };

        $dest['to'] = array_filter($dest['to'], $ftMatch);
        $dest['cc'] = array_filter($dest['cc'], $ftMatch);
        $dest['bcc'] = array_filter($dest['bcc'], $ftMatch);
        $dest['from'] = array_filter($dest['from'], $ftMatch);

        $this->addSubstitutes($dest);

        $to = implode(',', $dest['to']);
        $cc = implode(',', $dest['cc']);
        $bcc = implode(',', $dest['bcc']);
        $from = implode(',', $dest['from']); // only one value expected for from
        if ($from == "") {
            $from = getMailAddr($action->user->id, true);
        }
        if ($from == "") {
            $from = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, 'SMTP_FROM');
        }
        if ($from == "") {
            $from = $action->user->login . '@' . (isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "");
        }

        if (trim($to . $cc . $bcc) == "") {
            LogManager::info(sprintf(_("Send mail info : can't send mail %s: no sendee found"), $subject));
            $doc->addHistoryEntry(sprintf(_("Send mail info : can't send mail %s: no sendee found"), $subject), \DocHisto::NOTICE);
            if ($this->stopIfNoRecip) {
                return null;
            }
        } //nobody to send data
        if ($this->sendercopy && \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "FDL_BCC") == "yes") {
            $umail = getMailAddr(ContextManager::getCurrentUser(true)->id);
            if ($umail != "") {
                $bcc .= (trim($bcc) == "" ? "" : ",") . $umail;
            }
        }

        $body = new \Dcp\Mail\Body($pfout, 'text/html');
        $message->setBody($body);
        // ---------------------------
        // add inserted image
        foreach ($this->ifiles as $k => $v) {
            if (file_exists($v)) {
                $message->addBodyRelatedAttachment(new \Dcp\Mail\RelatedAttachment($v, $k, sprintf("image/%s", \Anakeen\Core\Utils\FileMime::getFileExtension($v)), $k));
            }
        }
        //send attachment
        $ta = $this->getMultipleRawValues("tmail_attach");
        foreach ($ta as $k => $v) {
            $err = $this->checkAttributeExistsInRelation(strtok($v, " "), SEManager::getRawDocument($doc->initid));
            if ($err) {
                LogManager::error($err);
                $doc->addHistoryEntry($err);
                throw new \Dcp\Exception($err);
            }
            $vf = $doc->getRValue(strtok($v, " "));
            if ($vf) {
                $tvf = $this->rawValueToArray($vf);
                foreach ($tvf as $vf) {
                    if ($vf) {
                        $fileinfo = $this->getFileInfo($vf);
                        if ($fileinfo["path"]) {
                            $message->addAttachment(new \Dcp\Mail\Attachment($fileinfo['path'], $fileinfo['name'], $fileinfo['mime_s']));
                        }
                    }
                }
            }
        }
        /*
        $err = sendmail($to, $from, $cc, $bcc, $subject, $multi_mix);
        */
        $message->setFrom($from);
        $message->addTo($to);
        $message->addCc($cc);
        $message->addBcc($bcc);
        $message->setSubject($subject);
        return $message;
    }

    /**
     * send document by email using this template
     * @param \Anakeen\Core\Internal\SmartElement $doc  document to send
     * @param array                               $keys extra keys used for template
     * @return string error - empty if no error -
     */
    public function sendDocument(\Anakeen\Core\Internal\SmartElement & $doc, $keys = array())
    {
        include_once("FDL/sendmail.php");
        include_once("FDL/Lib.Vault.php");
        $err = '';
        if (!$doc->isAffected()) {
            return $err;
        }

        try {
            $this->stopIfNoRecip = true;
            $message = $this->getMailMessage($doc, $keys);
            if (!$message) {
                return "";
            }
            $this->stopIfNoRecip = false;
        } catch (\Exception $e) {
            $this->stopIfNoRecip = false;
            return $e->getMessage();
        }
        $err = $message->send();

        $to = $message->getTo();
        $cc = $message->getCc();
        $bcc = $message->getBCC();
        $subject = $message->subject;
        $from = $message->getFrom();
        $savecopy = $this->getRawValue("tmail_savecopy") == "yes";
        if (($err == "") && $savecopy) {
            createSentMessage($to, $from, $cc, $bcc, $subject, $message, $doc);
        }
        $recip = "";
        if ($to) {
            $recip .= sprintf(_("sendmailto %s"), $to);
        }
        if ($cc) {
            $recip .= ' ' . sprintf(_("sendmailcc %s"), $cc);
        }
        if ($bcc) {
            $recip .= ' ' . sprintf(_("sendmailbcc %s"), $bcc);
        }

        if (self::NOTIFY_SENDMAIL_AUTO === $this->notifySendMail) {
            $notifySendMail = ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, 'CORE_NOTIFY_SENDMAIL');
            if (is_null($notifySendMail)) {
                $notifySendMail = self::NOTIFY_SENDMAIL_ALWAYS;
            }
        } else {
            $notifySendMail = $this->notifySendMail;
        }

        if ($err == "") {
            $doc->addHistoryEntry(sprintf(_("send mail %s with template %s"), $recip, $this->title), \DocHisto::INFO, "SENDMAIL");
            LogManager::info(sprintf(_("Mail %s sent to %s"), $subject, $recip));
            if (self::NOTIFY_SENDMAIL_ALWAYS === $notifySendMail) {
                \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("send mail %s"), $recip));
            }
        } else {
            $doc->addHistoryEntry(sprintf(_("cannot send mail %s with template %s : %s"), $recip, $this->title, $err), \DocHisto::ERROR);
            LogManager::error(sprintf(_("cannot send mail %s to %s : %s"), $subject, $recip, $err));
            if (self::NOTIFY_SENDMAIL_ALWAYS === $notifySendMail || self::NOTIFY_SENDMAIL_ERRORS_ONLY === $notifySendMail) {
                \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("cannot send mail %s"), $err));
            }
        }
        return $err;
    }

    /**
     * determine if a notification should be displayed to the user
     *
     * @param string $notifySendMail one of the NOTIFY_SENDMAIL_* const
     * @return string error if the value is invalid, empty string in case of success
     */
    public function setNotification($notifySendMail)
    {
        $allowedValues = [self::NOTIFY_SENDMAIL_ALWAYS, self::NOTIFY_SENDMAIL_ERRORS_ONLY, self::NOTIFY_SENDMAIL_NEVER, self::NOTIFY_SENDMAIL_AUTO];

        if (!in_array($notifySendMail, $allowedValues)) {
            throw new \Dcp\Core\Exception("MAIL0001", $notifySendMail, implode("' , '", $allowedValues));
        } else {
            $this->notifySendMail = $notifySendMail;
        }
        return '';
    }

    /**
     * update template with document values
     * @param \Anakeen\Core\Internal\SmartElement               $doc
     * @param string                                            $tpl template content
     * @param \Anakeen\Core\SmartStructure\NormalAttribute|bool $oattr
     * @return string
     */
    private function generateMailInstance(\Anakeen\Core\Internal\SmartElement & $doc, $tpl, $oattr = false)
    {
        $tpl = str_replace("&#x5B;", "[", $tpl); // replace [ convverted in \Anakeen\Core\Internal\SmartElement::setValue()
        $doc->lay = new \Layout("", $tpl);

        $ulink = ($this->getRawValue("tmail_ulink") == "yes");
        /* Expand layout's [TAGS] */
        $doc->viewdefaultcard("mail", $ulink, false, true);
        foreach ($this->keys as $k => $v) {
            $doc->lay->set($k, $v);
        }
        $body = $doc->lay->gen();
        $body = preg_replace_callback(array(
            "/SRC=\"([^\"]+)\"/",
            "/src=\"([^\"]+)\"/"
        ), function ($matches) {
            return $this->srcfile($matches[1]);
        }, $body);
        /* Expand remaining HTML constructions */
        if ($oattr !== false && $oattr->type == 'htmltext') {
            $body = $doc->getHtmlValue($oattr, $body, "mail", $ulink);
        }
        return $body;
    }

    /**
     * add substitute account mail addresses
     * @param array $dests
     */
    private function addSubstitutes(array & $dests)
    {
        $sql = "SELECT incumbent.login as inlogin, incumbent.mail as inmail, substitut.firstname || ' ' || substitut.lastname as suname , substitut.mail as sumail from users as incumbent, users as substitut where substitut.id=incumbent.substitute and incumbent.substitute is not null and incumbent.mail is not null and substitut.mail is not null;";
        DbManager::query($sql, $substituteMails);
        foreach (array(
                     "to",
                     "cc",
                     "bcc"
                 ) as $td) {
            if (!isset($dests[$td])) {
                continue;
            }
            foreach ($dests[$td] as & $aDest) {
                foreach ($substituteMails as $aSumail) {
                    $suName = str_replace('"', '', sprintf(_("%s (as substitute)"), $aSumail["suname"]));
                    $aDest = str_replace(sprintf('<%s>', $aSumail["inmail"]), sprintf('<%s>, "%s" <%s>', $aSumail["inmail"], $suName, $aSumail["sumail"]), $aDest);

                    $aDest = preg_replace(sprintf('/(^|,|\s)(%s)/', preg_quote($aSumail["inmail"], "/")), sprintf('\1\2, "%s" <%s>', $suName, $aSumail["sumail"]), $aDest);
                }
            }
            unset($aDest);
        }
    }

    private function getUniqId()
    {
        static $unid = 0;
        if (!$unid) {
            $unid = date('Ymdhis');
        }
        return $unid;
    }

    private function srcfile($src)
    {
        $vext = array(
            "gif",
            "png",
            "jpg",
            "jpeg",
            "bmp"
        );

        if (substr($src, 0, 3) == "cid") {
            return "src=\"$src\"";
        }
        if (substr($src, 0, 4) == "http") {
            $chopped_src = '';
            // Detect HTTP URLs pointing to myself
            foreach (array(
                         'CORE_URLINDEX'
                     ) as $url) {
                $url = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, $url);
                if (strlen($url) <= 0) {
                    continue;
                }
                if (strcmp(substr($src, 0, strlen($url)), $url) == 0) {
                    // Chop the URL base part, and leave only the args/vars
                    $chopped_src = substr($src, strlen($url));
                    break;
                }
            }
            if ($chopped_src == '') {
                return sprintf('src="%s"', $src);
            }
            $src = $chopped_src;
        }
        $cid = $src;

        if (preg_match("/.*app=FDL.*action=EXPORTFILE.*vid=([0-9]*)/", $src, $reg)) {
            $info = \Dcp\VaultManager::getFileInfo($reg[1]);
            $src = $info->path;
            $cid = "cid" . $this->getUniqId() . $reg[1] . '.' . \Anakeen\Core\Utils\FileMime::getFileExtension($info->path);
        } elseif (preg_match('!file/(?P<docid>\d+)/(?P<vid>\d+)/(?P<attrid>[^/]+)/(?P<index>[^/]+)/(?P<fname>[^?]+)!', $src, $reg)) {
            $info = \Dcp\VaultManager::getFileInfo($reg['vid']);
            $src = $info->path;
            $cid = "cid" . $this->getUniqId() . $reg[1] . '.' . \Anakeen\Core\Utils\FileMime::getFileExtension($info->path);
        }

        if (!in_array(strtolower(\Anakeen\Core\Utils\FileMime::getFileExtension($src)), $vext)) {
            return "";
        }

        $this->ifiles[$cid] = $src;
        return "src=\"cid:$cid\"";
    }
}
