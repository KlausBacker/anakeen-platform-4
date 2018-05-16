<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\Settings;
use Anakeen\Router\Exception;
use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;

/**
 * Class FileAttribute
 *
 * Download file from document file attribute
 *
 * @note    Used by route : GET /api/v2/documents/{docid}/files/{attrid}/{index}/{fileName}
 * @note    Used by route : GET /api/v2/documents/{docid}/revisions/{revision}/files/{attrid}/{index}/{fileName}
 * @package Anakeen\Routes\Core
 */
class FileAttribute
{
    const CACHEIMGDIR = Settings::CacheDir."file/";
    protected $revision;

    private $tmpFlag = "_tmp_";
    /**
     * @var \Anakeen\Core\Internal\SmartElement 
     */
    protected $_document = null;
    /**
     * @var \Anakeen\Core\SmartStructure 
     */
    protected $_family = null;
    protected $inline = false;

    protected $attrid;
    protected $index;
    protected $docid;


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->attrid = $args["attrid"];
        $this->index = $args["index"];
        $this->docid = $args["docid"];
        if (isset($args["revision"])) {
            $this->revision = $args["revision"];
        }

        $fileInfo = $this->getFileInfo($this->docid);

        // No use cache when download original file from document

        $inlineQuery = $request->getQueryParam("inline");
        if ($inlineQuery) {
            $this->inline = ($inlineQuery === "yes" || $inlineQuery === "true" || $inlineQuery === "1");
        }

        $response = ApiV2Response::withFile($response, $fileInfo->path, $fileInfo->name, $this->inline);
        \Dcp\VaultManager::updateAccessDate($fileInfo->id_file);
        if ($fileInfo->id_file === $this->tmpFlag) {
            unlink($fileInfo->path);
        }
        return $response;
    }


    /**
     * @param string $resourceId
     *
     * @return \vaultFileInfo
     * @throws Exception
     */
    protected function getFileInfo($resourceId)
    {
        $this->setDocument($resourceId);

        $attrid = $this->attrid;

        if ($attrid === "icon") {
            $fileValue = $this->_document->icon;
            $index = -1;
        } else {
            $attribut = $this->_document->getAttribute($attrid);
            if (!$attribut) {
                throw new Exception("ROUTES0116", $attrid, $this->_document->getTitle());
            }

            if (isset($this->index)) {
                $index = intval($this->index);
            } else {
                if (!$attribut->isMultiple()) {
                    $index = -1;
                } else {
                    $index = null;
                }
            }

            if ($attribut->mvisibility === "I") {
                $exception = new Exception("ROUTES0117", $attrid, $this->_document->getTitle());
                $exception->setHttpStatus("403", "Forbidden");
                throw $exception;
            }

            if ($attribut->type !== "file" && $attribut->type !== "image") {
                throw new Exception("ROUTES0124", $attrid, $resourceId);
            }

            $fileValue = $this->_document->getAttributeValue($attribut->id);

            if (is_array($fileValue) && $index === null) {
                return $this->zipFiles($attribut, $fileValue);
            }
        }

        if ($index === -1 && is_array($fileValue)) {
            throw new Exception("ROUTES0121", $index, $attrid, $resourceId);
        } elseif ($index >= 0 and !is_array($fileValue)) {
            throw new Exception("ROUTES0122", $index, $attrid, $resourceId);
        } elseif ($index < -1) {
            throw new Exception("ROUTES0123", $index, $attrid, $resourceId);
        }

        if ($index >= 0) {
            $fileValue = $fileValue[$index];
        }
        if (empty($fileValue)) {
            $exception = new Exception("ROUTES0118", $attrid, $index, $resourceId);
            $exception->setHttpStatus("404", "File not found");
            throw $exception;
        }

        preg_match(PREGEXPFILE, $fileValue, $reg);

        if (empty($reg["vid"])) {
            if ($attrid !== "icon") {
                throw new Exception("ROUTES0120", $attrid, $index, $resourceId);
            } else {
                // Redirect to public icon
                // @TODO Special case when attrid is "icon"
                throw new Exception("Not yet icon implemented");
            }
        }
        $vaultid = $reg["vid"];

        $fileInfo = \Dcp\VaultManager::getFileInfo($vaultid);
        if (!$fileInfo) {
            $exception = new Exception("ROUTES0119", $attrid, $index, $resourceId);
            $exception->setHttpStatus("404", "File not found");
            throw $exception;
        }


        return $fileInfo;
    }

    protected function zipFiles(\Anakeen\Core\SmartStructure\NormalAttribute $attribute, array $files)
    {
        $tmpZip = tempnam(ContextManager::getTmpDir(), "file" . $this->_document->id . "-") . ".zip";

        $zip = new \ZipArchive();
        if ($zip->open($tmpZip, \ZipArchive::CREATE) === true) {
            $fileNamePattern = sprintf("%%0%dd-%%s", floor(log(count($files), 10)) + 1);
            foreach ($files as $k => $file) {
                preg_match(PREGEXPFILE, $file, $reg);
                if (empty($reg["vid"])) {
                    throw new Exception("CRUD0609", $attribute->id, $k, $this->_document->id);
                }
                $fileInfo = \Dcp\VaultManager::getFileInfo($reg["vid"]);
                $zip->addFile($fileInfo->path, sprintf($fileNamePattern, $k + 1, $fileInfo->name));
            }
            $zip->close();
            $fileInfo = new \vaultFileInfo();
            $fileInfo->id_file = $this->tmpFlag;
            $fileInfo->name = $attribute->getLabel() . ".zip";
            $fileInfo->path = $tmpZip;
            $fileInfo->mime_s = "application/x-zip";
            return $fileInfo;
        } else {
            throw new Exception("CRUD0615", $attribute->id, "", $this->_document->id);
        }
    }

    /**
     * Find the current document and set it in the internal options
     *
     * @param $resourceId
     *
     * @throws Exception
     */
    protected function setDocument($resourceId)
    {
        if (isset($this->revision)) {
            $revisedId = SEManager::getRevisedDocumentId($resourceId, $this->revision);
            $this->_document = SEManager::getDocument($revisedId, false);
            if (!$this->_document) {
                $exception = new Exception("CRUD0221", $this->revision, $resourceId);
                $exception->setHttpStatus("404", "Document not found");
                throw $exception;
            }
        } else {
            $this->_document = SEManager::getDocument($resourceId);
        }
        if (!$this->_document) {
            $exception = new Exception("CRUD0200", $resourceId);
            $exception->setHttpStatus("404", "Document not found");
            throw $exception;
        }

        $err = $this->_document->control("view");
        if ($err) {
            $exception = new Exception("CRUD0201", $resourceId, $err);
            $exception->setHttpStatus("403", "Forbidden");
            throw $exception;
        }

        if ($this->_family && !is_a($this->_document, \Anakeen\Core\SEManager::getFamilyClassName($this->_family->name))) {
            $exception = new Exception("CRUD0220", $resourceId, $this->_family->name);
            $exception->setHttpStatus("404", "Document is not a document of the family " . $this->_family->name);
            throw $exception;
        }
    }
}
