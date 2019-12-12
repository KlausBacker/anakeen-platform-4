<?php

namespace Anakeen\TestTools\Routes\Middleware;

use Anakeen\Core\DbManager;
use Slim\Http\request;
use Slim\Http\response;

class TestToolsDryRun
{
    protected $dryRun;
    protected $transactionName = "seUpdate";

    public function __invoke(request $request, response $response, callable $next, array $args): response
    {
        $this->initParameters($request);

        if ($this->dryRun) {
            $this->initTransaction();
        }

        $response = $next($request, $response);


        if ($this->dryRun) {
            $this->rollbackTransaction();
        }

        return $response;
    }

    protected function initParameters(\Slim\Http\request $request)
    {
        $request->getParsedBody();
        $this->dryRun = $request->getQueryParams()["dry-run"] ?? null;
    }

    protected function initTransaction()
    {
        DbManager::savePoint($this->transactionName);
    }

    protected function rollbackTransaction()
    {
        DbManager::rollbackPoint($this->transactionName);
    }
}
