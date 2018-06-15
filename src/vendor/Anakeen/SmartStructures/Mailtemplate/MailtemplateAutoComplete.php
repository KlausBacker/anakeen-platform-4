<?php


namespace Anakeen\SmartStructures\Mailtemplate;

use Anakeen\Core\SEManager;
use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;
use Anakeen\SmartStructures\Mail\MailAutoComplete;

class MailtemplateAutoComplete
{
    /**
     * get mail address from MAILRECIPIENT Smart Structures
     *
     *
     * @param SmartAutocompleteRequest  $request
     * @param SmartAutocompleteResponse $response
     * @param array                     $args
     * @return SmartAutocompleteResponse
     * @throws \Dcp\Db\Exception
     * @throws \Dcp\SearchDoc\Exception
     */
    public static function getMailAddresses(SmartAutocompleteRequest $request, SmartAutocompleteResponse $response, array $args): SmartAutocompleteResponse
    {
        $type = $args["fromtype"];
        $famid = $args["smartstructure"];
        $wfamid = $args["workflow"];
        // function tplmail($dbaccess, $type, $famid, $wfamid, $name){
        switch ($type) {
            case 'F': // address fix
                return MailAutoComplete::getMailAddresses($request, $response);

            case 'A': // value of attribute
                if (!$famid) {
                    return $response->setError(___("Smart Structure must be set", "autocomplete"));
                }
                return self::getFamAttribute($request, $response, $famid, 'text', false);


            case 'D': // value of attribute
                if (!$famid) {
                    return $response->setError(___("Smart Structure must be set", "autocomplete"));
                }
                $response = self::getFamAttribute($request, $response, $famid, 'docid', false);
                return self::getFamAttribute($request, $response, $famid, 'account', false);


            case 'DE':
                if (!$famid) {
                    return $response->setError(___("Smart Structure must be set", "autocomplete"));
                }
                $response = self::getFamAttribute($request, $response, $famid, 'docid', true);
                return self::getFamAttribute($request, $response, $famid, 'account', true);


            case 'G': // value of attribute
                if (!$famid) {
                    return $response->setError(___("Smart Structure must be set", "autocomplete"));
                }
                $response = self::getFamAttribute($request, $response, $famid, 'file', false);
                return self::getFamAttribute($request, $response, $famid, 'image', false);


            case 'E': // value of attribute
                if (!$famid) {
                    return $response->setError(___("Smart Structure must be set", "autocomplete"));
                }
                return self::getFamAttribute($request, $response, $famid, '', true);

            case 'WA': // value of attribute
                if (!$wfamid) {
                    return $response->setError(___("Workflow must be set", "autocomplete"));
                }
                return self::getFamAttribute($request, $response, $wfamid, 'text', false);


            case 'WE': // value of attribute
                if (!$wfamid) {
                    return $response->setError(___("Workflow must be set", "autocomplete"));
                }
                return self::getFamAttribute($request, $response, $wfamid, '', true);


            case 'WD': // value of attribute
                if (!$wfamid) {
                    return $response->setError(___("Workflow must be set", "autocomplete"));
                }
                return self::getFamAttribute($request, $response, $wfamid, 'docid', false);


            case 'PR':
                if (!$famid) {
                    return $response->setError(___("Smart Structure must be set", "autocomplete"));
                }
                return self::getFamAttribute($request, $response, $famid, 'docid', true);


            case 'WPR':
                if (!$wfamid) {
                    return $response->setError(___("Workflow must be set", "autocomplete"));
                }
                return self::getFamAttribute($request, $response, $wfamid, 'docid', true);


            case 'P':
                return self::getGlobalsParameters($request, $response);


            case 'RD':
                return self::recipientDocument($request, $response);
        }
        //return "error tplmail($dbaccess,$type,$famid, $name)";
        $response->setError("Type address must be set");
        return $response;
    }

    protected static function recipientDocument(SmartAutocompleteRequest $request, SmartAutocompleteResponse $response)
    {
        $name = $request->getFilterValue();
        $sf = new \SearchDoc("", -1);
        $sf->setObjectReturn();
        $sf->overrideViewControl();
        $sf->addFilter("atags ~* E'\\\\yMAILRECIPIENT\\\\y'");
        $dlf = $sf->search()->getDocumentList();

        if ($dlf->count() == 0) {
            return sprintf(_("none families are described to be used as recipient"));
        }
        foreach ($dlf as $fam) {
            $cfam = SEManager::createTemporaryDocument($fam->id);
            /**
             * @var \Anakeen\Core\IMailRecipient $cfam
             */
            if (!is_a($cfam, \Anakeen\Core\IMailRecipient::class)) {
                return sprintf(_("Family '%s' does not implements IMailRecipient interface."), $fam->name);
            }

            $mailAttr = $cfam->getMailAttribute();
            $s = new \SearchDoc("", $fam->id);
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

                $response->appendEntry(xml_entity_encode($mailTitle), [
                    sprintf("%d (%s)", $dest->id, $dest->getTitle())
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

    /**
     * attribut list to be use in mail template
     * @param SmartAutocompleteRequest  $request
     * @param SmartAutocompleteResponse $response
     * @param                           $famid
     * @param string                    $type
     * @param bool                      $param
     * @return SmartAutocompleteResponse
     */
    protected static function getFamAttribute(SmartAutocompleteRequest $request, SmartAutocompleteResponse $response, $famid, $type = "text", $param = false)
    {
        $name = $request->getFilterValue();
        $doc = SEManager::createTemporaryDocument($famid, false);

        if ($param) {
            $tinter = $doc->getParamAttributes();
        } else {
            $tinter = $doc->GetNormalAttributes();
        }
        $name = strtolower($name);
        // HERE HERE HERE
        $pattern_name = preg_quote($name, "/");
        $pattern_type = ($type);
        foreach ($tinter as $k => $v) {
            if (($name == "") || (preg_match("/$pattern_name/i", $v->getLabel(), $reg)) || (preg_match("/$pattern_name/", $v->id, $reg))) {
                preg_match("/$pattern_type/", $v->type, $reg);
                if (($type == "") || ($v->type == $type) || ((strpos($type, '|') > 0) && (preg_match("/$pattern_type/", $v->type, $reg)))) {
                    $r = $v->id . ' (' . $v->getLabel() . ')';

                    $response->appendEntry(xml_entity_encode($r), [$r]);
                }
            }
        }
        return $response;
    }


    protected static function getGlobalsParameters(SmartAutocompleteRequest $request, SmartAutocompleteResponse $response)
    {
        $name = $request->getFilterValue();
        $q = new \Anakeen\Core\Internal\QueryDb("", \Anakeen\Core\Internal\ParamDef::class);

        $q->AddQuery("isglob = 'Y'");
        if ($name) {
            $q->AddQuery("name ~* '" . pg_escape_string($name) . "'");
        }
        $q->order_by = "name";
        $la = $q->Query(0, 20, "TABLE");
        foreach ($la as $k => $v) {
            $p = $v["name"] . ' (' . $v["descr"] . ')';

            $response->appendEntry(xml_entity_encode($p), [$p]);
        }
        return $response;
    }
}
