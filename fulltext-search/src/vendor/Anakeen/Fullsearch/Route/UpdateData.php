<?php

namespace Anakeen\Fullsearch\Route;

use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Fullsearch\SearchDomain;
use Anakeen\Fullsearch\SearchDomainManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\TransformationEngine\Manager as TeManager;

/**
 *
 * @note used POST /api/admin/fullsearch/domains/
 */
class UpdateData
{
    protected $teConnectionChecked = false;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }

    protected function doRequest()
    {
        $data = [];

        $domains = SearchDomainManager::getConfig();

        foreach ($domains as $domainName => $config) {
            $data[$domainName] = $this->updateDomain($domainName);
        }
        return $data;
    }


    protected function checkTeConnection()
    {
        if (ContextParameterManager::getValue(TeManager::Ns, "TE_ACTIVATE") === "yes") {
            TeManager::checkConnection();
        }
    }

    protected function updateDomain($domainName)
    {
        $data = [];
        $domain = new SearchDomain($domainName);
        $domain->updateIndexSearchData(function (\Anakeen\SmartElement $se) use (&$data) {
            if ($this->teConnectionChecked === false) {
                $this->checkTeConnection();
                $this->teConnectionChecked = true;
            }
            $data[] = ["id" => $se->id, "title" => $se->getTitle()];
        });

        return $data;
    }
}
