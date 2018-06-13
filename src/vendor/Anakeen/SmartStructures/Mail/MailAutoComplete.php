<?php


namespace Anakeen\SmartStructures\Mail;

use Anakeen\Core\SEManager;
use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class MailAutoComplete
{
    /**
     * get mail address from MAILRECIPENT families
     *
     *
     * @param SmartAutocompleteRequest  $request
     * @param SmartAutocompleteResponse $response
     * @return SmartAutocompleteResponse
     * @throws \Dcp\Db\Exception
     * @throws \Dcp\SearchDoc\Exception
     */
    public static function getMailAddresses(SmartAutocompleteRequest $request, SmartAutocompleteResponse $response): SmartAutocompleteResponse
    {
        $filter = $request->getFilterValue();
        $sf = new \SearchDoc("", -1);
        $sf->setObjectReturn();
        $sf->overrideViewControl();
        $sf->addFilter("atags ~* 'MAILRECIPIENT'");
        $dlf = $sf->search()->getDocumentList();

        if ($dlf->count() == 0) {
            return sprintf(___("none smart structure are described to be used as recipient", "smart mail"));
        }
        foreach ($dlf as $fam) {
            $cfam = SEManager::createTemporaryDocument($fam->id);
            /**
             * @var \Anakeen\Core\IMailRecipient $cfam
             */
            if (!method_exists($cfam, "getMail")) {
                return sprintf(___("smart structure %s does not implement IMailRecipent - missing getMail method", "smart mail"), $fam->name);
            }
            if (!method_exists($cfam, "getMailAttribute")) {
                return sprintf(___("smart structure %s does not implement IMailRecipent - missing getMailAttribute method", "smart mail"), $fam->name);
            }
            if (!method_exists($cfam, "getMailTitle")) {
                return sprintf(___("smart structure %s does not implement IMailRecipient - missing getMailTitle method", "smart mail"), $fam->name);
            }

            $mailAttr = $cfam->getMailAttribute();
            $s = new \SearchDoc("", $fam->id);
            $s->setObjectReturn();
            $s->setSlice(100);
            if ($mailAttr) {
                $s->addFilter("%s is not null", $mailAttr);
            }
            if ($filter != "") {
                if ($mailAttr) {
                    $s->addFilter("(title ~* '%s') or (%s ~* '%s')", $filter, $mailAttr, $filter);
                } else {
                    $s->addFilter("(title ~* '%s')", $filter, $filter);
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

                if ($usw > 0) {
                    $uid = $dest->id;
                    $type = "link"; //$type="link";  // cause it is a bool
                } else {
                    $type = "plain"; //$type="plain";
                    $uid = " ";
                }

                // Encode mail : Label entry is an HTML fragment
                $response->appendEntry(xml_entity_encode($mailTitle), [
                    $mail,
                    $uid,
                    $type
                ]);
            }
        }

        // Need to custom sort after because mail can be computed
        $responseData = $response->getData();
        usort($responseData, function ($a, $b) {
            return strcasecmp($a["title"], $b["title"]);
        });
        $response->setData($responseData);
        return $response;
    }
}
