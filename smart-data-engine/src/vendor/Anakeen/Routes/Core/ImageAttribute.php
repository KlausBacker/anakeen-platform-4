<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Settings;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

/**
 * Class ImageAsset
 *
 * Download image from public assets
 *
 * @note    Used by route : GET /api/v2/smart-elements/{docid}/images/{attrid}/{index}/sizes/{size:[0-9x]+[cfs]?}[.{extension}]
 * @note    Used by route : GET /api/v2/smart-elements/{docid}/revisions/{revision}/images/{attrid}/{index}/sizes/{size:[0-9x]+[cfs]?}[.{extension}]
 * @package Anakeen\Routes\Core
 */
class ImageAttribute extends FileAttribute
{
    const CACHEIMGDIR = Settings::CacheDir . "image/";
    protected $size;
    protected $imageFileName;
    /**
     * @var \Anakeen\Vault\FileInfo
     */
    protected $fileInfo;
    protected $extension;
    protected $inline = true;
    protected $revision;

    /**
     * Download resized image
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param array               $args
     *
     * @return mixed
     * @throws Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->size = $args["size"];
        $this->attrid = $args["attrid"];
        $this->index = $args["index"];
        $this->docid = $args["docid"];
        $this->extension = $args["extension"];

        $inlineQuery = $request->getQueryParam("inline");
        if ($inlineQuery) {
            $this->inline = ($inlineQuery === "yes" || $inlineQuery === "true" || $inlineQuery === "1");
        }
        if (isset($args["revision"])) {
            $this->revision = $args["revision"];
        }

        $this->fileInfo = $this->getFileInfo($this->docid);

        if (!file_exists($this->fileInfo->path)) {
            $this->fileInfo->path=sprintf("%s/public/Images/%s", ContextManager::getRootDirectory(), ImageAsset::DEFAULTIMG);
        }
        $destination = $this->getDestinationCacheImage($this->fileInfo->id_file, $this->size);

        if (file_exists($destination)) {
            $outFile = $destination;
        } else {
            $outFile = Lib\Files::resizeLocalImage($this->fileInfo->path, $destination, $this->size);
        }

        $fileName = sprintf("%s-%s", $this->size, $this->fileInfo->name);

        $mime = "";
        if ($this->extension) {
            $fileName = substr($fileName, 0, strrpos($fileName, '.'));
            $fileName .= "." . $this->extension;
            switch ($this->extension) {
                case "jpg":
                    $mime = "image/jpeg";
                    break;

                default:
                    $mime = "image/" . $this->extension;
            }
        }
        // \Dcp\HttpApi\V1\Etag\Manager::setEtagHeaders();
        //  Files::downloadFile($outFile, $fileName, $mime, $this->inline, false);
        $etag = $this->getEtagInfo();
        if ($etag) {
            $response = ApiV2Response::withEtag($request, $response, $etag);
        }
        $response = ApiV2Response::withFile($response, $outFile, $fileName, $this->inline, $mime);

        return $response;
    }

    protected function getDestinationCacheImage($localimage, $size)
    {
        if (empty($this->extension)) {
            $this->extension = "png";
        }
        $imgDir = sprintf("%s/%s", DEFAULT_PUBDIR, self::CACHEIMGDIR);
        if (!is_dir($imgDir)) {
            mkdir($imgDir, 0755, true);
        }
        $basedest = sprintf(
            "%s/%s-vid-%s.%s",
            $imgDir,
            $size,
            str_replace("/", "_", $localimage),
            $this->extension
        );

        return $basedest;
    }

    public function getEtagInfo()
    {
        if ($this->fileInfo) {
            return $this->fileInfo->mdate;
        }
        return null;
    }
}
