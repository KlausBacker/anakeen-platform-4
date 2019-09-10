<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class JsAssetReference extends AssetReference
{
    const moduleType = "module";
    const libraryType = "library";
    const globalType = "global";

    /**
     * @var string
     */
    protected $type = self::libraryType;

    /**
     * @var array list of function name to execute in the 'library' mode
     */
    protected $functionList = [];

    /**
     * JsAssetReference constructor.
     * @param $key
     * @param $path
     * @param string $type
     */
    public function __construct($path, $type = self::libraryType)
    {
        parent::__construct($path);
        $this->type = $type;
        $this->assetType = "js";
    }

    /**
     * Return the type of js asset
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Add a function name in the list
     * @param string $functionName
     */
    public function addFunctionName($functionName)
    {
        if (!in_array($functionName, $this->functionList)) {
            array_push($this->functionList, $functionName);
        }
    }

    /**
     * Get the function names list
     */
    public function getFunctionNames()
    {
        return $this->functionList;
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array["type"] = $this->getType();
        if (count($this->getFunctionNames()) > 0) {
            $array["function"] = $this->getFunctionNames();
        }
        return $array;
    }
}
