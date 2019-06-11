<?php

namespace Anakeen\Routes\Core\Lib;

/**
 * Class DocumentDataFormatter
 * This class is a facade of \Anakeen\Core\Internal\FormatCollection (had format for REST collection)
 *
 */
class DocumentDataFormatter extends CollectionDataFormatter
{

    public function __construct(\Anakeen\Core\Internal\SmartElement $document)
    {
        parent::__construct($document);
    }

    /**
     * Get Document Data for REST Api
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->format()[0];
    }
}
