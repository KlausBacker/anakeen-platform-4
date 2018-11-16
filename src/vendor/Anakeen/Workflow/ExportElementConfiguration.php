<?php

namespace Anakeen\Workflow;

use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Core\Internal\DocumentAccess;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod;
use Anakeen\Core\SmartStructure\ExportConfiguration;
use Anakeen\Ui\ExportRenderConfiguration;
use SmartStructure\Fields\Mailtemplate as MailFields;
use SmartStructure\Fields\Timer as TimerFields;
use SmartStructure\Fields\Cvdoc as CvDocFields;
use SmartStructure\Fields\Mask as MaskFields;
use SmartStructure\Mask;

class ExportElementConfiguration
{

    /**
     * @var \DOMDocument
     */
    protected static $dom;
    protected $dataSet = [];

    /** @noinspection PhpMissingParentConstructorInspection */
    public static function initDom()
    {
        self::$dom = new \DOMDocument("1.0", "UTF-8");
        self::$dom->formatOutput = true;
        $domConfig = self::cel("config");
        $domConfig->setAttribute("xmlns:" . ExportWorkflowConfiguration::NS, ExportWorkflowConfiguration::NSURL);
        self::$dom->appendChild($domConfig);
        return $domConfig;
    }

    public static function getTimerConfig($name)
    {
        $domConfig = self::initDom();
        $domConfig->setAttribute("xmlns:" . ExportWorkflowConfiguration::NSTM, ExportWorkflowConfiguration::NSTMURL);
        $xml = self::getTimerData($name);
        $domConfig->appendChild($xml);

        $domConfig->appendChild(self::getAccessProfile($name));

        return self::$dom->saveXML();
    }

    public static function getMailTemplateConfig($name)
    {
        $domConfig = self::initDom();
        $domConfig->setAttribute("xmlns:" . ExportWorkflowConfiguration::NSMT, ExportWorkflowConfiguration::NSMTURL);
        $xml = self::getMailTemplateData($name);
        $domConfig->appendChild($xml);
        $domConfig->appendChild(self::getAccessProfile($name));
        return self::$dom->saveXML();
    }

    public static function getProfileConfig($name)
    {
        $domConfig = self::initDom();
        $domConfig->appendChild(self::getAccessProfile($name));
        return self::$dom->saveXML();
    }

    public static function getFieldAccessConfig($name)
    {
        $domConfig = self::initDom();
        $xmls = self::getFieldAccessData($name);
        foreach ($xmls as $xml) {
            $domConfig->appendChild($xml);
        }
        ExportConfiguration::setStartComment("Field Access Layer List: Configuration", $domConfig);
        $xml = self::getFieldAccess($name);
        $domConfig->appendChild($xml);
        ExportConfiguration::setEndComment();
        ExportConfiguration::setStartComment("Field Access Layer List: Accesses", $domConfig);
        $domConfig->appendChild(self::getAccessProfile($name));
        ExportConfiguration::setEndComment();

        return self::$dom->saveXML();
    }


    public static function getFieldAccessData($fallid)
    {
        $nodes = [];
        $fall = SEManager::getDocument($fallid);
        if (!$fall) {
            self::setComment(sprintf("Field Access %s not found", $fallid), self::$dom->documentElement);
            return [];
        }
        $layers = $fall->getMultipleRawValues(\SmartStructure\Fields\Fieldaccesslayerlist::fall_layer);
        $aclNames = $fall->getMultipleRawValues(\SmartStructure\Fields\Fieldaccesslayerlist::fall_aclname);

        $nodes = array_merge($nodes, self::getComment("Field Access Layers : Configurations"));
        foreach ($layers as $kl => $layer) {
            $tag = self::cel("field-access-layer");
            $eLayer = SEManager::getDocument($layer);
            if ($eLayer) {
                SEManager::cache()->addDocument($eLayer);
                $tag->setAttribute("name", ExportConfiguration::getLogicalName($eLayer->id));
                $tag->setAttribute("label", $eLayer->getTitle());
                $tag->setAttribute("access-name", $aclNames[$kl]);
                $tag->setAttribute("structure", ExportConfiguration::getLogicalName($eLayer->getRawValue(\SmartStructure\Fields\Fieldaccesslayer::fal_famid)));

                $fieldIds = $eLayer->getMultipleRawValues(\SmartStructure\Fields\Fieldaccesslayer::fal_fieldid);
                $fieldAccesses = $eLayer->getMultipleRawValues(\SmartStructure\Fields\Fieldaccesslayer::fal_fieldaccess);
                foreach ($fieldIds as $k => $accessField) {
                    $atag = self::cel("field-access");
                    $atag->setAttribute("field", $accessField);
                    $atag->setAttribute("access", $fieldAccesses[$k]);
                    $tag->appendChild($atag);
                }
            } else {
                $tag->setAttribute("name", "UNKNOW#" . $layer);
            }
            $nodes[] = $tag;
        }
        $nodes = array_merge($nodes, self::getComment("Field Access Layer : Accesses"));
        foreach ($layers as $kl => $layer) {
            $nodes[] = self::getAccessProfile($layer);
        }
        return $nodes;
    }

    public static function getCvdocConfig($name)
    {
        $domConfig = self::initDom();
        $domConfig->setAttribute("xmlns:" . ExportWorkflowConfiguration::NSUI, ExportWorkflowConfiguration::NSUIURL);
        $xml = self::getCvdocData($name);
        $domConfig->appendChild($xml);
        ExportConfiguration::setStartComment("Cvdoc configuration: Accesses", $domConfig);
        $domConfig->appendChild(self::getAccess($name, "basic"));
        ExportConfiguration::setEndComment();
        ExportConfiguration::setStartComment("Cvdoc views : Accesses", $domConfig);
        $domConfig->appendChild(self::getAccess($name, "extended"));
        ExportConfiguration::setEndComment();

        return self::$dom->saveXML();
    }

    public static function getMaskConfig($name)
    {
        $domConfig = self::initDom();
        $domConfig->setAttribute("xmlns:" . ExportWorkflowConfiguration::NSUI, ExportWorkflowConfiguration::NSUIURL);
        $xml = self::getMaskData($name);
        $domConfig->appendChild($xml);
        $domConfig->appendChild(self::getAccessProfile($name));
        return self::$dom->saveXML();
    }

    public static function getTimerData($name, \DOMDocument $dom = null)
    {
        if ($dom !== null) {
            self::$dom = $dom;
        }
        $timer = SEManager::getDocument($name);
        $timerNode = self::celtimer("timer");

        $timerNode->setAttribute("name", ExportWorkflowConfiguration::getLogicalName($name));
        $timerNode->setAttribute("label", $timer->getRawValue(TimerFields::tm_title));

        $timerNode->setAttribute("structure", ExportWorkflowConfiguration::getLogicalName($timer->getRawValue(TimerFields::tm_family)));
        $timerNode->setAttribute("workflow", ExportWorkflowConfiguration::getLogicalName($timer->getRawValue(TimerFields::tm_workflow)));

        $dateRef = $timer->getRawValue(TimerFields::tm_dyndate);
        $dateNode = self::celtimer("field-date-reference");
        if ($dateRef) {
            $dateNode->setAttribute("ref", $dateRef);
        }
        $timerNode->appendChild($dateNode);
        $deltaDay = $timer->getRawValue(TimerFields::tm_refdaydelta);
        $deltaHourDay = $timer->getRawValue(TimerFields::tm_refhourdelta);
        if ($deltaDay || $deltaHourDay) {
            $delay = sprintf("%d days %d hours", $deltaDay, $deltaHourDay);
            $dateNode->setAttribute("delta", $delay);
        }

        $tasks = $timer->getAttributeValue(TimerFields::tm_t_config);
        $tasksNode = self::celtimer("tasks");

        /*  [1] => Array
        (
            [tm_delay] => 2
            [tm_hdelay] => 9
            [tm_iteration] => 1
            [tm_tmail] => Array
                (
                )

            [tm_state] => e_ccfd_sl_validee_directeur
            [tm_method] => ::sayHello(2)
        )*/

        foreach ($tasks as $task) {
            $taskNode = self::celtimer("task");
            $delay = sprintf("%d days %d hours", $task[TimerFields::tm_delay], $task[TimerFields::tm_hdelay]);
            $taskNode->setAttribute("delta", $delay);
            if ($task[TimerFields::tm_state]) {
                $singleTask = self::celtimer("setstate");
                $singleTask->setAttribute("state", $task[TimerFields::tm_state]);
                $taskNode->appendChild($singleTask);
            }
            if ($task[TimerFields::tm_tmail]) {
                foreach ($task[TimerFields::tm_tmail] as $mail) {
                    $singleTask = self::celtimer("sendmail");
                    $singleTask->setAttribute("ref", ExportWorkflowConfiguration::getLogicalName($mail));
                    $taskNode->appendChild($singleTask);
                }
            }
            if ($task[TimerFields::tm_method]) {
                $singleTask = self::celtimer("process");
                $method = new ParseFamilyMethod();
                $method->parse($task[TimerFields::tm_method]);

                $pcNode = self::celtimer("process-callable");
                $pcNode->setAttribute("function", sprintf("%s::%s", $method->className, $method->methodName));
                $singleTask->appendChild($pcNode);

                foreach ($method->inputs as $input) {
                    $argNode = self::celtimer("process-argument");
                    $argNode->nodeValue = $input->name;
                    $argNode->setAttribute("type", $input->type === "string" ? "string" : "field");
                    $singleTask->appendChild($argNode);
                }

                $taskNode->appendChild($singleTask);
            }
            $tasksNode->appendChild($taskNode);
        }
        $timerNode->appendChild($tasksNode);

        return $timerNode;
    }


    public static function getMailTemplateData($name, \DOMDocument $dom = null)
    {
        if ($dom !== null) {
            self::$dom = $dom;
        }

        $mail = SEManager::getDocument($name);
        $mailNode = self::celmail("mailtemplate");

        $mailNode->setAttribute("name", ExportWorkflowConfiguration::getLogicalName($name));
        $mailNode->setAttribute("label", $mail->getRawValue(MailFields::tmail_title));
        $mailNode->setAttribute("structure", ExportWorkflowConfiguration::getLogicalName($mail->getRawValue(MailFields::tmail_family)));

        $fromNode = self::celmail("from");

        $fromType = $mail->getMultipleRawValues(MailFields::tmail_fromtype);
        $from = $mail->getMultipleRawValues(MailFields::tmail_from);

        if ($from) {
            $fromNode->appendChild(self::getRecipient($fromType[0], $from[0]));
        }

        $nodeRecipients = self::celmail("recipients");
        $recips = $mail->getMultipleRawValues(MailFields::tmail_recip);
        $destTypes = $mail->getMultipleRawValues(MailFields::tmail_desttype);
        $copyModes = $mail->getMultipleRawValues(MailFields::tmail_copymode);
        foreach ($recips as $k => $recip) {
            $nodeRecipient = self::celmail("recipient");
            $nodeRecipient->setAttribute("dest", $copyModes[$k]);
            $recipient = self::getRecipient($destTypes[$k], $recip);
            $nodeRecipient->appendChild($recipient);
            $nodeRecipients->appendChild($nodeRecipient);
        }
        $nodeSubject = self::celmail("subject");
        $nodeSubject->nodeValue = $mail->getRawValue(MailFields::tmail_subject);

        $nodeSave = self::celmail("savecopy");
        $nodeSave->nodeValue = ($mail->getRawValue(MailFields::tmail_savecopy) === "yes" ? "true" : "false");

        $nodeLink = self::celmail("savecopy");
        $nodeLink->nodeValue = ($mail->getRawValue(MailFields::tmail_ulink) === "yes" ? "true" : "false");


        $nodeBody = self::celmail("body");
        $nodeBody->setAttribute("content-type", "html");
        $nodeBody->appendChild(self::$dom->createCDATASection($mail->getRawValue(MailFields::tmail_body)));

        $mailNode->appendChild($fromNode);
        $mailNode->appendChild($nodeRecipients);
        $mailNode->appendChild($nodeSubject);
        $mailNode->appendChild($nodeSave);
        $mailNode->appendChild($nodeLink);
        $mailNode->appendChild($nodeBody);

        $attachements = $mail->getMultipleRawValues(MailFields::tmail_attach);

        if ($attachements) {
            $attchementsNode = self::celmail("attachments");
            foreach ($attachements as $attachement) {
                $attchNode = self::celmail("attachment");
                if (preg_match("/([^(]*)\((.*)\)/", $attachement, $reg)) {
                    $value = trim($reg[1]);
                    $label = trim($reg[2]);
                    $attchNode->setAttribute("label", $label);
                    $attchNode->nodeValue = $value;
                } else {
                    $attchNode->nodeValue = $attachement;
                }
                $attchementsNode->appendChild($attchNode);
            }
            $mailNode->appendChild($attchementsNode);
        }

        return $mailNode;
    }

    public static function getCvdocData($name, $withMask = false, \DOMDocument $dom = null)
    {
        if ($dom !== null) {
            self::$dom = $dom;
        }
        $cvdoc = SEManager::getDocument($name);
        $domConfig = self::$dom->getElementsByTagNameNS(ExportConfiguration::NSURL, "config");
        /** @var \DOMElement $domConfig */
        $domConfig = $domConfig[0];

        $cvtag = self::celui("view-control");

        $cvtag->setAttribute("name", $cvdoc->name ?: $cvdoc->id);
        $cvtag->setAttribute("label", $cvdoc->title);

        $cvtag->setAttribute("structure", ExportRenderConfiguration::getLogicalName($cvdoc->getRawvalue(CvDocFields::cv_famid)));

        $desc = $cvdoc->getRawValue(CvDocFields::ba_desc);
        if ($desc) {
            $cvdesc = self::celui("description");
            $cvdesc->appendChild(self::$dom->createCDATASection($desc));
            $cvtag->appendChild($cvdesc);
        }
        $primaryMask = $cvdoc->getRawValue(CvDocFields::cv_primarymask);
        if ($primaryMask) {
            $primaryMskNode = self::celui("primary-mask");
            $primaryMskNode->setAttribute("ref", ExportRenderConfiguration::getLogicalName($primaryMask));
            $cvtag->appendChild($primaryMskNode);
            if ($withMask) {
                /**
                 * @var Mask $mask
                 */
                $mask = SEManager::getDocument($primaryMask);
                $maskDataNode = self::getMaskData($mask->id);
                if ($maskDataNode) {
                    ExportConfiguration::setStartComment("Primary mask configuration", $domConfig);
                    $domConfig->appendChild($maskDataNode);
                    ExportConfiguration::setEndComment();
                }
            }
        }
        $idcview = $cvdoc->getRawValue(CvDocFields::cv_idcview);
        if ($idcview) {
            $idcviewtag = self::celui("creation-view");
            $idcviewtag->setAttribute("ref", $idcview);

            $cvtag->appendChild($idcviewtag);
        }
        $accessClass = $cvdoc->getRawValue(CvDocFields::cv_renderaccessclass);
        if ($accessClass) {
            $accessClassTag = self::celui("render-access");
            $accessClassTag->setAttribute("class", $accessClass);
            $cvtag->appendChild($accessClassTag);
        }
        $views = $cvdoc->getAttributeValue(CvDocFields::cv_t_views);

        $viewlist = self::celui("view-list");

        foreach ($views as $view) {
            $viewtag = self::celui("view");
            $viewtag->setAttribute("name", $view[CvDocFields::cv_idview]);
            $viewtag->setAttribute("label", $view[CvDocFields::cv_lview]);
            $viewtag->setAttribute("display-mode", $view[CvDocFields::cv_kview] === "VEDIT" ? "edition" : "consultation");
            if ($view[CvDocFields::cv_mskid]) {
                $msktag = self::celui("mask");
                $msktag->setAttribute("ref", ExportConfiguration::getLogicalName($view[CvDocFields::cv_mskid]));
                $viewtag->appendChild($msktag);
                if ($withMask) {
                    /**
                     * @var \SmartStructure\Mask $mask
                     */
                    $mask = SEManager::getDocument($view[CvDocFields::cv_mskid]);
                    if (!$mask) {
                        $maskDataNode = self::getMaskData($view[CvDocFields::cv_mskid]);
                    } else {
                        $maskDataNode = self::getMaskData($mask->id);
                    }
                    if ($maskDataNode) {
                        self::setComment("Mask configuration", $domConfig);
                        $domConfig->appendChild($maskDataNode);
                    }
                }
            }
            if ($view[CvDocFields::cv_renderconfigclass]) {
                $rcctag = self::celui("render-config");
                $rcctag->setAttribute("class", $view[CvDocFields::cv_renderconfigclass]);
                $viewtag->appendChild($rcctag);
            }
            if ($view[CvDocFields::cv_order]) {
                $viewtag->setAttribute("order", intval($view[CvDocFields::cv_order]));
            }
            $viewtag->setAttribute("menu-displayed", ($view[CvDocFields::cv_displayed] === "yes") ? "true" : "false");
            if ($view[CvDocFields::cv_menu]) {
                $viewtag->setAttribute("submenu-label", $view[CvDocFields::cv_menu]);
            }
            $viewlist->appendChild($viewtag);
        }

        ExportConfiguration::setStartComment("View control configuration", $cvtag);
        $cvtag->appendChild($viewlist);
        ExportConfiguration::setEndComment();

        return $cvtag;
    }

    public static function getMaskData($name, \DOMDocument $dom = null)
    {
        if ($dom !== null) {
            self::$dom = $dom;
        }
        $mask = SEManager::getDocument($name);

        $masktag = self::celui("mask");
        if (!$mask) {
            $masktag->setAttribute("name", "UNKNOW#" . $name);
            return $masktag;
        }

        $masktag->setAttribute("name", ExportConfiguration::getLogicalName($mask->id));
        $masktag->setAttribute("label", $mask->title);
        $masktag->setAttribute("structure", ExportConfiguration::getLogicalName($mask->getRawvalue(MaskFields::msk_famid)));
        $views = $mask->getAttributeValue(MaskFields::msk_t_contain);

        $visList = self::celui("visibility-list");
        $masktag->appendChild($visList);
        $needList = self::celui("need-list");
        $masktag->appendChild($needList);


        foreach ($views as $data) {
            $vis = $data[MaskFields::msk_visibilities];
            if ($vis && $vis !== "-") {
                $dataTag = self::celui("visibility");
                $dataTag->setAttribute("field", $data[MaskFields::msk_attrids]);
                $dataTag->setAttribute("value", $vis);
                $visList->appendChild($dataTag);
            }
            $need = $data[MaskFields::msk_needeeds];
            if ($need && $need !== "-") {
                $dataTag = self::celui("need");
                $dataTag->setAttribute("field", $data[MaskFields::msk_attrids]);
                $dataTag->setAttribute("value", ($need === "Y") ? "true" : "false");
                $needList->appendChild($dataTag);
            }
        }
        return $masktag;
    }

    public static function getAccessProfile($name, \DOMDocument $dom = null)
    {
        if ($dom !== null) {
            self::$dom = $dom;
        }
        $e = SEManager::getDocument($name);
        if (!$e) {
            self::setComment(sprintf("Profile %s not found", $name), self::$dom->documentElement);
            return self::getAccess($name);
        }

        if ($e->accessControl()->isRealProfile() || $e->id === $e->profid) {
            $accessControl = self::getAccess($e->id);
            return $accessControl;
        } else {
            $accessControl = self::getAccessRef($e);
            return $accessControl;
        }
    }

    protected static function getAccessRef(SmartElement $e)
    {
        $accessControl = self::cel("access-configuration");
        $accessControl->setAttribute("name", ExportWorkflowConfiguration::getLogicalName($e->id));

        if ($e->profid) {
            $accessControl->setAttribute("ref", ExportWorkflowConfiguration::getLogicalName($e->dprofid ?: $e->profid));
        }
        return $accessControl;
    }

    public static function getAccess(string $profid, $returns = "all", \DOMDocument $dom = null)
    {
        if ($dom !== null) {
            self::$dom = $dom;
        }
        $accessControl = self::cel("access-configuration");
        $profil = SEManager::getDocument($profid);
        if (!$profil) {
            $accessControl->setAttribute("name", "UNKNOW#" . $profid);

            return $accessControl;
        }

        $accessControl->setAttribute("name", ExportWorkflowConfiguration::getLogicalName($profil->id));
        $accessControl->setAttribute("label", $profil->title);
        if ($profil->defDoctype === 'C') {
            $accessControl->setAttribute("profil-type", "PFAM");
        } else {
            $accessControl->setAttribute("profil-type", $profil->fromname);
        }
        if ($profil->getRawValue("dpdoc_famid")) {
            $accessControl->setAttribute("access-structure", ExportWorkflowConfiguration::getLogicalName($profil->getRawValue("dpdoc_famid")));
        }
        if ($profil->getRawValue("ba_desc")) {
            if ($profil->accessControl()->isRealProfile()) {
                $desc = self::cel("description");
                $desc->appendChild(self::$dom->createCDATASection($profil->getRawValue("ba_desc")));
                $accessControl->appendChild($desc);
            }
        }
        $resultsAccount = $resultsRelation = $resultsExtAccount = $resultsExtRelation = [];
        if ($returns === "all" || $returns === "basic") {
            $sql = sprintf(
                "select users.login, docperm.upacl from docperm,users where docperm.docid=%d and users.id=docperm.userid and docperm.upacl != 0 order by users.login",
                $profil->id
            );
            DbManager::query($sql, $resultsAccount);
            $sql = sprintf(
                "select vgroup.id as attrid, docperm.upacl from docperm,vgroup where docperm.docid=%d and vgroup.num=docperm.userid and docperm.upacl != 0 order by vgroup.id",
                $profil->id
            );
            DbManager::query($sql, $resultsRelation);
        }
        if ($returns === "all" || $returns === "extended") {
            $sql = sprintf(
                "select users.login, docpermext.acl from docpermext,users where docpermext.docid=%d and users.id=docpermext.userid order by users.login",
                $profil->id
            );
            DbManager::query($sql, $resultsExtAccount);

            $sql = sprintf(
                "select vgroup.id as attrid, docpermext.acl from docpermext,vgroup where docpermext.docid=%d and vgroup.num=docpermext.userid order by vgroup.id",
                $profil->id
            );
            DbManager::query($sql, $resultsExtRelation);
        }
        $results = array_merge($resultsAccount, $resultsRelation);

        /**
         * @var \DOMElement[] $elementAccesses
         */
        $elementAccesses = [];
        $accessResults = [];
        // Add special acls - Always defined in each profil
        $profil->acls[] = "modifyacl";
        $profil->acls[] = "viewacl";

        foreach ($profil->acls as $acl) {
            if (isset(DocumentAccess::$dacls[$acl])) {
                $pos = DocumentAccess::$dacls[$acl]["pos"];
                foreach ($results as $result) {
                    if (\DocPerm::controlMask($result["upacl"], $pos)) {
                        $accessResult = [
                            "acl" => $acl
                        ];

                        $elementAccount = null;
                        if (isset($result["login"])) {
                            $accessResult["login"] = $result["login"];
                        }
                        if (isset($result["attrid"])) {
                            $accessResult["attrid"] = $result["attrid"];
                        }

                        $accessResults[] = $accessResult;
                    }
                }
            }
        }

        $extended = array_merge($resultsExtAccount, $resultsExtRelation);
        foreach ($extended as $result) {
            $accessResult = [
                "acl" => $result["acl"]
            ];
            if (isset($result["login"])) {
                $accessResult["login"] = $result["login"];
            }
            if (isset($result["attrid"])) {
                $accessResult["attrid"] = $result["attrid"];
            }
            $accessResults[] = $accessResult;
        }
        foreach ($accessResults as $result) {
            $acl = $result["acl"];
            $elementAccess = self::cel("element-access");
            $elementAccess->setAttribute("access", $acl);

            if (isset($result["login"])) {
                $elementAccess->setAttribute("account", $result["login"]);
            }
            if (isset($result["attrid"])) {
                $elementAccess->setAttribute("field", $result["attrid"]);
            }
            $elementAccesses[] = $elementAccess;
        }

        foreach ($elementAccesses as $elementAccess) {
            $accessControl->appendChild($elementAccess);
        }
        return $accessControl;
    }


    public static function getFieldAccess($fallid, \DOMDocument $dom = null)
    {
        if ($dom !== null) {
            self::$dom = $dom;
        }
        $tag = self::cel("field-access-layer-list");
        $fall = SEManager::getDocument($fallid);
        SEManager::cache()->addDocument($fall);

        $tag->setAttribute("name", ExportWorkflowConfiguration::getLogicalName($fall->id));
        $tag->setAttribute("label", $fall->title);
        $tag->setAttribute("structure", ExportWorkflowConfiguration::getLogicalName($fall->getRawValue(\SmartStructure\Fields\Fieldaccesslayerlist::fall_famid)));


        $layers = $fall->getMultipleRawValues(\SmartStructure\Fields\Fieldaccesslayerlist::fall_layer);
        $aclNames = $fall->getMultipleRawValues(\SmartStructure\Fields\Fieldaccesslayerlist::fall_aclname);
        foreach ($layers as $kl => $layer) {
            $fal = self::cel("field-access-layer");
            $fal->setAttribute("ref", ExportWorkflowConfiguration::getLogicalName($layer));
            $fal->setAttribute("access-name", $aclNames[$kl]);
            $tag->appendChild($fal);
        }

        return ($tag);
    }

    protected static function getRecipient($type, $value)
    {
        $label = "";
        if (preg_match("/([^(]*)\((.*)\)/", $value, $reg)) {
            $value = trim($reg[1]);
            $label = trim($reg[2]);
        }
        switch ($type) {
            /*
                "F" :"Adresse fixe":
                "A" :"Attribut texte"
                "D" :"Attribut relation"
                "E" :"Paramètre de famille texte"
                "DE":"Paramètre de famille relation"
                "P" :"Paramètres globaux"
                "WA":"Attribut cycle"
                "WD":"Relation cycle"
                "WE":"Paramètre cycle"
            */
            case "F":
                $node = self::celmail("address");
                $node->nodeValue = $value;
                break;
            case "A":
                $node = self::celmail("element-field-value");
                $node->nodeValue = $value;
                break;
            case "D":
                $node = self::celmail("element-account-field");
                $node->nodeValue = $value;
                break;
            case "E":
                $node = self::celmail("structure-parameter-value");
                $node->nodeValue = $value;
                break;
            case "DE":
                $node = self::celmail("structure-account-parameter");
                $node->nodeValue = $value;
                break;
            case "WA":
                $node = self::celmail("workflow-field-value");
                $node->nodeValue = $value;
                break;
            case "WE":
                $node = self::celmail("workflow-parameter-value");
                $node->nodeValue = $value;
                break;
            case "WD":
                $node = self::celmail("workflow-account-field");
                $node->nodeValue = $value;
                break;
            case "P":
                $node = self::celmail("config-parameter");

                if (strpos($value, '::') === false) {
                    $ns = ContextParameterManager::getNs($value);
                    $pvalue = $value;
                } else {
                    list($ns, $pvalue) = explode("::", $value);
                }
                $node->setAttribute("ns", $ns);
                $node->nodeValue = $pvalue;

                break;
            default:
                $node = self::celmail("unknomtype");
                $node->setAttribute("type", $type);
        }
        if ($label) {
            $node->setAttribute("label", $label);
        }
        return $node;
    }

    protected static function celtimer($name)
    {
        return self::$dom->createElementNS(ExportWorkflowConfiguration::NSTMURL, ExportWorkflowConfiguration::NSTM . ":" . $name);
    }

    protected static function celmail($name)
    {
        return self::$dom->createElementNS(ExportWorkflowConfiguration::NSMTURL, ExportWorkflowConfiguration::NSMT . ":" . $name);
    }

    protected static function celui($name)
    {
        return self::$dom->createElementNS(ExportRenderConfiguration::NSUIURL, ExportRenderConfiguration::NSUI . ":" . $name);
    }

    protected static function cel($name)
    {
        return self::$dom->createElementNS(ExportWorkflowConfiguration::NSURL, ExportWorkflowConfiguration::NS . ":" . $name);
    }

    protected static function setComment($text, \DOMElement $dom)
    {
        $nodes = self::getComment($text);
        foreach ($nodes as $node) {
            $dom->appendChild($node);
        }
    }

    protected static function getComment($text)
    {
        return ExportConfiguration::getComment($text, self::$dom);
    }
}
