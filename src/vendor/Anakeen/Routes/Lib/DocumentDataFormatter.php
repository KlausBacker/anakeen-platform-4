<?php

namespace Anakeen\Routes\Core;

/**
 * Class DocumentDataFormatter
 * This class is a facade of FormatCollection (had format for REST collection)
 *
 */
class DocumentDataFormatter extends CollectionDataFormatter
{

    public function __construct(\Doc $document)
    {
        parent::__construct($document);
    }

    /**
     * Get Document Data for REST Api
     *
     * @return mixed
     * @throws \Dcp\Fmtc\Exception
     */
    public function getData()
    {
        return $this->format()[0];
    }
}