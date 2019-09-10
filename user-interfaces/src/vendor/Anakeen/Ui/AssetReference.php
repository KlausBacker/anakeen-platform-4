<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

abstract class AssetReference
{
    protected $assetType;

    /**
     * @var string path of the asset
     */
    protected $path;

    /**
     * AssetReference constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }


    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    public static function getManifestAssetPath($name)
    {
        return UIGetAssetPath::getElementAssets($name, UIGetAssetPath::isInDebug() ? "dev" : "prod");
    }

    public function toArray()
    {
        return [
            "path" => $this->getPath()
        ];
    }
}
