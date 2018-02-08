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
    public function getFormatCollection()
    {
        $fmt = parent::getFormatCollection();
        $fmt->setPropDateStyle(\DateAttributeValue::isoWTStyle);
        return $fmt;
    }
}
