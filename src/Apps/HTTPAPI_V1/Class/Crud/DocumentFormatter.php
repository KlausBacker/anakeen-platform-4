<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\HttpApi\V1\Crud;

use Anakeen\Routes\Core\CollectionDataFormatter;

/**
 * Class DocumentFormatter
 * This class is a facade of FormatCollection (had format for REST collection)
 *
 * @package Dcp\HttpApi\V1\Crud
 */
class DocumentFormatter extends CollectionDataFormatter
{
    const APIURL="api/v1/";


    public function __construct($source)
    {
        parent::__construct($source);
        $this->rootPath=self::APIURL;
    }
    public function getFormatCollection()
    {
        $fmt = parent::getFormatCollection();
        $fmt->setPropDateStyle(\DateAttributeValue::isoWTStyle);
        return $fmt;
    }
}
