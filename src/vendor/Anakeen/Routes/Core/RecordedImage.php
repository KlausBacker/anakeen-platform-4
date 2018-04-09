<?php
namespace Anakeen\Routes\Core;

use Anakeen\Router\Exception;

/**
 * Class RecordedImage
 *
 * @note    Used by route : GET /api/v2/images/recorded/original/{image}[.{extension}]
 * @note    Used by route : GET /api/v2/images/recorded/sizes/{size:[0-9x]+[cfs]?}/{image}[.{extension}]
 * @package Anakeen\Routes\Core
 */
class RecordedImage extends ImageAsset
{
    protected $extension;

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        parent::initParameters($request, $args);
        if (!empty($args["extension"])) {
            $this->extension = $args["extension"];
        }
    }

    protected function getSourceImage()
    {
        $vaultId = $this->imageFileName;
        $location = \Anakeen\Routes\Core\Lib\Files::getVaultPath($vaultId, true);
        
        if (!$location || !file_exists($location)) {
            throw new Exception("CRUD0600", $vaultId);
        }
        
        if (!$this->size && !empty($this->extension) && basename($location) !== sprintf("%s%s", $vaultId, $this->extension)) {
            throw new Exception("CRUD0604", $vaultId, $this->extension);
        }
        
        return $location;
    }
    
    protected function getDestinationCacheImage($localimage, $size)
    {
        $fileExtension = $this->extension;
        $basedest = sprintf("%s/%s/%s-vid%s%s", DEFAULT_PUBDIR, self::CACHEIMGDIR, $size, str_replace("/", "_", $localimage), $fileExtension);
        
        return $basedest;
    }
}
