<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\Utils\VidExtractor;
use Anakeen\Router\Exception;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\SmartElementManager;

/**
 *
 * Download image from inline htmltext img
 *
 * @note    Used by route : GET /api/v2/images/htmltext/{docid}/{revision}/{attrid}/{vid}/{fileName}
 */
class ImageHtmltext
{
    protected $revision;

    /**
     * @var \Anakeen\Core\Internal\SmartElement
     */
    protected $_document = null;
    /**
     * @var \Anakeen\Core\SmartStructure
     */
    protected $_family = null;
    protected $inline = true;

    protected $attrid;
    protected $index;
    protected $initid;
    protected $vid;


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->attrid = $args["attrid"];
        $this->revision = $args["revision"];
        $this->initid = $args["docid"];
        $this->vid = $args["vid"];


        $this->setDocument();

        $oattr = $this->_document->getAttribute($this->attrid);
        if (!$oattr) {
            throw new Exception(sprintf(
                "cannot access image : Unknow field \"%s\" in element #%d",
                $this->attrid,
                $this->initid
            ));
        }

        if ($oattr->type !== "htmltext") {
            throw new Exception(sprintf(
                "cannot access image : Field \"%s\" is not htmltext in element #%d",
                $this->attrid,
                $this->initid
            ));
        }
        $htmlValue = $this->_document->getRawValue($oattr->id);
        if (!$htmlValue) {
            throw new Exception(sprintf(
                "cannot access image : Field \"%s\" is empty in element #%d",
                $this->attrid,
                $this->initid
            ));
        }
        if ($oattr->isMultiple()) {
            $htmlValues = $this->_document->getMultipleRawValues($oattr->id);
        } else {
            $htmlValues = [$htmlValue];
        }

        $vids = VidExtractor::getVidFromHtmltext($htmlValues);
        if (!in_array($this->vid, $vids)) {
            throw new Exception(sprintf(
                "cannot access image : Field \"%s\" image not referenced in element #%d",
                $this->attrid,
                $this->initid
            ));
        }
        $fileInfo = $this->getFileInfo($this->vid);

        // No use cache when download original file from document

        $inlineQuery = $request->getQueryParam("inline");
        if ($inlineQuery) {
            $this->inline = ($inlineQuery === "yes" || $inlineQuery === "true" || $inlineQuery === "1");
        }

        // 24 days in cache
        $response = $response->withHeader("Cache-Control", "max-age=86400");

        $response = ApiV2Response::withFile($response, $fileInfo->path, $fileInfo->name, $this->inline);
        \Anakeen\Core\VaultManager::updateAccessDate($fileInfo->id_file);

        return $response;
    }

    /**
     * @param string $vaultid
     *
     * @return \Anakeen\Vault\FileInfo
     * @throws Exception
     */
    protected function getFileInfo($vaultid)
    {
        $fileInfo = \Anakeen\Core\VaultManager::getFileInfo($vaultid);
        if (!$fileInfo) {
            throw new Exception(sprintf(
                "cannot access image : File from \"%s\" not found in element #%d",
                $this->attrid,
                $this->initid
            ));
        }

        $baseName = substr($fileInfo->name, 0, strrpos($fileInfo->name, '.'));

        if ($baseName !== "paste") {
            throw new Exception(sprintf(
                "cannot access image : Image from \"%s\" not comes from copy/paste in element #%d",
                $this->attrid,
                $this->initid
            ));
        }

        if (substr($fileInfo->mime_s, 0, 6) !== "image/") {
            throw new Exception(sprintf(
                "cannot access image : File from \"%s\" is not an image in element #%d",
                $this->attrid,
                $this->initid
            ));
        }


        return $fileInfo;
    }


    /**
     * Find the current document and set it in the internal options
     *
     *
     * @throws Exception
     */
    protected function setDocument()
    {
        $revisedId = SEManager::getRevisedDocumentId($this->initid, $this->revision);
        $this->_document = SmartElementManager::getDocument($revisedId, false);
        if (!$this->_document) {
            $exception = new Exception("CRUD0221", $this->revision, $this->initid);
            $exception->setHttpStatus("404", "Document not found");
            throw $exception;
        }
        if (!$this->_document) {
            $exception = new Exception("CRUD0200", $this->initid);
            $exception->setHttpStatus("404", "Document not found");
            throw $exception;
        }
    }
}
