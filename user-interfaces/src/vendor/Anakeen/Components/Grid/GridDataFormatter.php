<?php

namespace Anakeen\Components\Grid;

use Anakeen\Core\Internal\Format\DateAttributeValue;
use Anakeen\Routes\Core\Lib\CollectionDataFormatter;
use Anakeen\Search\ElementList;
use Anakeen\Search\SearchElements;
use Anakeen\Router\Exception;
use Anakeen\Router\URLUtils;

class GridDataFormatter extends CollectionDataFormatter
{
    public function __construct($source)
    {
        if (is_a($source, \Anakeen\Core\Internal\SmartElement::class)) {
            /* if the $source is a doc, we want to render only one document*/
            $this->formatCollection = new \Anakeen\Core\Internal\FormatCollection($source);
            if ($source->mid > 0) {
                // mask already set no need to set default mask
                $this->formatCollection->setVerifyAttributeAccess(false);
            }
        } elseif (is_a($source, "DocumentList")) {
            $this->formatCollection = new \Anakeen\Core\Internal\FormatCollection();
            $this->formatCollection->useCollection($source);
        } elseif (is_a($source, "\Anakeen\Search\Internal\SearchSmartData")) {
            $this->formatCollection = new \Anakeen\Core\Internal\FormatCollection();
            /* @var \Anakeen\Search\Internal\SearchSmartData $source */
            $docList = $source->getDocumentList();
            $this->formatCollection->useCollection($docList);
        } elseif (is_a($source, SearchElements::class)) {
            $this->formatCollection = new GridFormatCollection();
            $docList = $source->getResults();
            $this->formatCollection->useElementList($docList);
        } elseif (is_a($source, ElementList::class)) {
            $this->formatCollection = new GridFormatCollection();
            $this->formatCollection->useElementList($source);
        } else {
            /* the source is not a handled kind of source */
            throw new Exception("CRUD0500");
        }


        $this->formatCollection->setDecimalSeparator('.');
        $this->formatCollection->mimeTypeIconSize = 20;
        $this->formatCollection->useShowEmptyOption = false;
        $this->formatCollection->setPropDateStyle(DateAttributeValue::isoStyle);

        $this->rootPath = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_URLINDEX") . "/" . static::APIURL;
        $this->rootPath = URLUtils::stripUrlSlahes($this->rootPath);
        /* init the standard generator of url (redirect to the documents collection */
        $this->generateUrl = function ($document) {
            return \Anakeen\Routes\Core\Lib\DocumentUtils::getURI($document, static::APIURL);
        };
        $this->setDefaultHooks();
    }
}
