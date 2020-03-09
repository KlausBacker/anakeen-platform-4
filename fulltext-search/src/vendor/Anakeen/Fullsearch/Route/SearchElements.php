<?php

namespace Anakeen\Fullsearch\Route;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\Settings;
use Anakeen\Fullsearch\FilterMatch;
use Anakeen\Fullsearch\SearchDomain;
use Anakeen\Router\URLUtils;
use Anakeen\Routes\Core\DocumentList;

/**
 * @note used by route GET /api/v2/fullsearch/domains/{domain}/smart-elements/
 */
class SearchElements extends DocumentList
{

    protected $domainName;
    /**
     * @var SearchDomain
     */
    protected $domain;
    /**
     * @var string
     */
    protected $pattern;

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        parent::initParameters($request, $args);
        $this->domainName = $args["domain"];
        $this->pattern = $request->getQueryParam("q");

        $this->domain = new SearchDomain($this->domainName);
    }

    protected function getData()
    {
        $data = parent::getData();

        $data["requestParameters"]["q"] = $this->pattern;
        $data["requestParameters"]["searchDomain"] = $this->domainName;
        $data["uri"] = URLUtils::generateURL(Settings::ApiV2 . "fullsearch/domains/{$this->domainName}/smart-elements/");

        return $data;
    }


    protected function prepareDocumentList()
    {
        parent::prepareDocumentList();
        if ($this->pattern) {
            $filter = new FilterMatch($this->domainName, $this->pattern);

            $this->_searchDoc->addFilter($filter);
            $this->_searchDoc->setOrder($filter->getRankOrder());
        }
    }

    protected function prepareDocumentFormatter($documentList)
    {
        $documentFormatter = parent::prepareDocumentFormatter($documentList);

        if ($this->pattern) {
            $h = new \Anakeen\Fullsearch\FilterHighlight($this->domainName);
            $h->setStartSel("[[[");
            $h->setStopSel("]]]");
            $fmt = $documentFormatter->getFormatCollection();
            $previous = $fmt->getDocumentRenderHook();
            $fmt->setDocumentRenderHook(function ($values, SmartElement $smartElement) use ($h, $previous) {
                if ($previous) {
                    $values = $previous($values, $smartElement);
                }
                $values["highlights"] = $h->highlight($smartElement->id, $this->pattern);
                return $values;
            });
        }
        return $documentFormatter;
    }


}
