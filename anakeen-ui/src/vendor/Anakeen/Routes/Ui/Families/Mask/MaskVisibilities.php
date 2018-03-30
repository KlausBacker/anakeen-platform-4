<?php

namespace Anakeen\Routes\Ui\Families\Mask;

use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\Core\DocManager;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

/**
 * Class MaskVisibilities
 *
 * @note    Used by route : GET /api/v2/admin/mask/{mask}/visibilities/
 */
class MaskVisibilities
{
    protected $documentId;
    /**
     * @var \SmartStructure\Mask
     */
    protected $_document;
    /**
     * @var \DocFam
     */
    protected $family;
    protected $callback="callback";

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {

        $this->initParameters($request, $args);

        $data = $this->doRequest($messages);


        //print json_encode($data["visibilities"], 0);exit;
        $out = sprintf("%s(%s)", $this->callback, json_encode($data["visibilities"]));
        return $response->withJson($data["visibilities"]);
     //   return $response->write($out);
        //  return ApiV2Response::withData($response, $data, $messages);
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->documentId = $args["mask"];
        $this->callback = $request->getQueryParam("callback");
        $this->setDocument($this->documentId);
    }

    protected function doRequest(&$messages)
    {
        $data = [];
        $messages = [];
        $famId = $this->_document->getRawValue(\SmartStructure\Attributes\Mask::msk_famid);
        $this->family = DocManager::getFamily($famId);
        if (!$this->family) {
            $exception = new Exception("CRUD0200", $famId);
            $exception->setHttpStatus("400", "Family not found");
            throw $exception;
        }

        $integrityMsg = $this->_document->verifyIntegraty();
        if ($integrityMsg) {
            $msg = new \Anakeen\Routes\Core\Lib\ApiMessage();
            $msg->contentText = $integrityMsg;
            $messages[] = $msg;
        }

        $data["visibilities"] = array_values($this->getVisibilities());
        return $data;
    }

    protected function getVisibilities()
    {

        $tvisibilities = $this->_document->getVisibilities();
        $tkey_visibilities = array_keys($tvisibilities);

        $tneedeeds = $this->_document->getNeedeeds();


        $this->family->applyMask();
        $origattr = $this->family->attributes->attr;

        $tmpdoc = DocManager::createTemporaryDocument($this->family->id);
        $tmpdoc->applyMask($this->_document->id);
        // display current values
        $tmask = array();

        $labelvis = $this->getVisibilityLabels();
        $tmpdoc->attributes->orderAttributes();
        $hasMenu = false;

        foreach ($tmpdoc->attributes->attr as $k => $attr) {
            /**
             * @var $attr \NormalAttribute|\ActionAttribute
             */
            if (!$attr->visibility) {
                continue;
            }
            if ($attr->usefor == 'Q') {
                continue;
            }
            $tmask[$k]["attrid"] = $attr->id;
            $tmask[$k]["parentId"] = ($attr->fieldSet &&  $attr->fieldSet->id !== \Anakeen\Core\SmartStructures\Attributes::HIDDENFIELD )? $attr->fieldSet->id : null;

            if ($attr->type === "menu" || $attr->type === "action") {
                $hasMenu=true;
                $tmask[$k]["parentId"] = "_menu";
            }
            $tmask[$k]["label"] = $attr->getLabel();
            $tmask[$k]["type"] = $attr->type;
            $tmask[$k]["attrid"] = $attr->id;
            $tmask[$k]["visibility"] = $attr->visibility;
            $tmask[$k]["mVisibility"] = $attr->mvisibility;
            $tmask[$k]["visibilityLabel"] = $labelvis[$attr->visibility];
            $tmask[$k]["mVisibilityLabel"] = $labelvis[$attr->mvisibility];




            $tmask[$k]["needed"] = (!empty($origattr[$k]->needed));


            $tmask[$k]["mNeeded"] = $tmask[$k]["needed"];
            if (isset($tneedeeds[$attr->id])) {
                if (($tneedeeds[$attr->id] == "Y") || (($tneedeeds[$attr->id] == "-") && (!empty($attr->needed)))) {
                    $tmask[$k]["mNeeded"] = true;
                } else {
                    $tmask[$k]["mNeeded"] = false;
                }
            }

            if (in_array($attr->type, array(
                "frame",
                "tab",
                "menu",
                "action",
                "array"
            ))) {
                $tmask[$k]["needed"] = null;
                $tmask[$k]["mNeeded"] = null;
            }

            $tmask[$k]["setVisibility"]=isset($tvisibilities[$attr->id]);
        }
        if ($hasMenu) {
            $tmask["_menu"] = [
                "attrid" => "_menu",
                "type" => "menulist",
                "parentId" => null,
                "label" => "Menus"
            ];
        }

        unset($tmask[\Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD]);

        return $tmask;
    }

    public function getVisibilityLabels()
    {
        return array(
            "-" => " ",
            "R" => "Read only" ,
            "W" => "Read write" ,
            "O" => "Write only" ,
            "H" => "Hidden" ,
            "S" => "Read disabled" ,
            "U" => "Static array" ,
            "I" => "Invisible"
        );
    }
    protected function setDocument($resourceId)
    {
        $this->_document = DocManager::getDocument($resourceId);
        if (!$this->_document || $this->_document->doctype === "Z") {
            $exception = new Exception("CRUD0200", $resourceId);
            $exception->setHttpStatus("404", "Mask not found");
            throw $exception;
        }


        if ($this->_document->fromname !== "MASK") {
            throw new Exception(sprintf(sprintf("Not a mask : %d", $resourceId)));
        }
        $err = $this->_document->control("view");
        if ($err) {
            $exception = new Exception("CRUD0201", $resourceId, $err);
            $exception->setHttpStatus("403", "Forbidden");
            throw $exception;
        }
    }
}
