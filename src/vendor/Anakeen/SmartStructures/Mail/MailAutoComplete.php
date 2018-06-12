<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * INterface to send mail
 *
 */
namespace Anakeen\SmartStructures\Mail;

use Anakeen\Core\SEManager;

class MailAutoComplete
{

    /**
     * get mail address from MAILRECIPENT families
     *
     * @param $dbaccess
     * @param $name
     *
     * @return array|string
     */
    function lmail($dbaccess, $name)
    {

        $tr = array();
        $sf = new \SearchDoc("", -1);
        $sf->setObjectReturn();
        $sf->overrideViewControl();
        $sf->addFilter("atags ~* 'MAILRECIPIENT'");
        $dlf = $sf->search()->getDocumentList();

        if ($dlf->count() == 0) {
            return sprintf(_("none families are described to be used as recipient"));
        }
        foreach ($dlf as $fam) {
            $cfam = SEManager::createTemporaryDocument($fam->id);
            /**
             * @var \Anakeen\Core\IMailRecipient $cfam
             */
            if (!method_exists($cfam, "getMail")) {
                return sprintf(_("family %s does not implement IMailRecipent - missing getMail method"), $fam->name);
            }
            if (!method_exists($cfam, "getMailAttribute")) {
                return sprintf(_("family %s does not implement IMailRecipent - missing getMailAttribute method"), $fam->name);
            }
            if (!method_exists($cfam, "getMailTitle")) {
                return sprintf(_("family %s does not implement IMailRecipient - missing getMailTitle method"), $fam->name);
            }

            $mailAttr = $cfam->getMailAttribute();
            $s = new \SearchDoc($dbaccess, $fam->id);
            $s->setObjectReturn();
            $s->setSlice(100);
            if ($mailAttr) {
                $s->addFilter("%s is not null", $mailAttr);
            }
            if ($name != "") {
                if ($mailAttr) {
                    $s->addFilter("(title ~* '%s') or (%s ~* '%s')", $name, $mailAttr, $name);
                } else {
                    $s->addFilter("(title ~* '%s')", $name, $name);
                }
            }
            $dl = $s->search()->getDocumentList();
            foreach ($dl as $dest) {
                /**
                 * @var \SmartStructure\IUSER $dest
                 */
                $mailTitle = $dest->getMailTitle();
                $mail = $dest->getMail();
                if ($mailTitle == '') {
                    $mailTitle = $mail;
                }
                $usw = $dest->getRawValue("us_whatid");
                $uid = "";
                if ($usw > 0) {
                    $uid = $dest->id;
                    $type = "link"; //$type="link";  // cause it is a bool
                } else {
                    $type = "plain"; //$type="plain";
                    $uid = " ";
                }
                $tr[] = array(
                    xml_entity_encode($mailTitle),
                    $mail,
                    $uid,
                    $type
                );
            }
        }
        usort($tr, function ($a, $b) {
            return strcasecmp($a[0], $b[0]);
        });
        return $tr;
    }
}
