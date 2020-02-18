<?php

namespace Anakeen\Fullsearch\Route;

use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\Utils\Date;
use Anakeen\Core\Utils\FileMime;
use Anakeen\Exception;
use Anakeen\Fullsearch\IndexFile;
use Anakeen\Fullsearch\SearchDomainDatabase;
use Anakeen\Router\ApiV2Response;
use Anakeen\TransformationEngine\Client;
use Anakeen\TransformationEngine\Manager;
use Anakeen\Vault\VaultFile;

/**
 * Class RecordFileTextContent
 * @package Anakeen\Fullsearch\Route
 * @note used by route GET /api/v2/fullsearch/domains/{domain}/smart-elements/{elementid}
 */
class RecordFileTextContent
{

    protected $elementid;
    protected $taskid;
    protected $name;
    protected $domainName;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }
    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->domainName = $args["domain"];
        $this->elementid = $args["elementid"];
        $this->taskid = $request->getQueryParam("tid");
        $this->name = $request->getQueryParam("name");
    }


    /**
     *
     * @return array
     * @throws Exception
     * @throws \Anakeen\Core\DocManager\Exception
     */
    protected function doRequest()
    {
        $data = [];
        if (!$this->taskid) {
            throw new Exception("No task identifier found");
        } else {
            $ot = new \Anakeen\TransformationEngine\Client();


            IndexFile::recordTeFileresult($this->taskid);

            $se = SEManager::getDocument($this->elementid, false);
            $d = new SearchDomainDatabase($this->domainName);
            $d->updateSmartWithFiles($se);

            $data["title"]=$se->getTitle();
        }
        return $data;
    }
}