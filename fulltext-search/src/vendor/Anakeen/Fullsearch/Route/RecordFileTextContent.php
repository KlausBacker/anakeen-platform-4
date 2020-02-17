<?php

namespace Anakeen\Fullsearch\Route;

use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\Utils\Date;
use Anakeen\Core\Utils\FileMime;
use Anakeen\Exception;
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
            $filename = tempnam(ContextManager::getTmpDir(), 'txt-');
            if ($filename === false) {
                throw new Exception(sprintf("Error creating temporary file in '%s'.", ContextManager::getTmpDir()));
            } else {
                $err = Manager::downloadTEFile($this->taskid, $filename, $info);

                if ($this->elementid) {
                    $doc = SEManager::getDocument($this->elementid);
                    if ($doc) {
                        $doc->mdate = Date::getNow(true);
                      //  $doc->modify(true, ["mdate"], true); // To update cache
                    }
                }
                unlink($filename);
            }
        }
        return $data;
    }
}