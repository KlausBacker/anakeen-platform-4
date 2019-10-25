<?php

namespace Anakeen\TestTools\Routes\Middleware;

use Anakeen\Core\DbManager;
use Anakeen\Router\Exception;
use \Slim\Http\request;
use \Slim\Http\response;

class TestToolsDryRun
{
    protected $dryRun;
    protected $transactionName = "seUpdate";

    public function __invoke(request $request, response $response, callable $next, array $args): response
    {
        $this->initParameters($request, $args);

        if ($this->dryRun) {
            $this->initTransaction();
        }
  
        return $next($request, $response);


        if ($this->dryRun) {
            $this->rollbackTransaction();
        }
    }

    protected function initParameters(\Slim\Http\request $request, array $args)
    {
        $request->getParsedBody();
        $this->dryRun = $request->getQueryParams()["dry-run"] ?? null;
    }

    protected function initTransaction()
    {
        $savepoint = DbManager::savePoint($this->transactionName);
        if (!empty($savepoint)) {
            $exception = new Exception("ANKTEST001", $savepoint);
            $exception->setHttpStatus("500", "Cannot put the save point");
            $exception->setUserMessage(err);
            throw $exception;
        }
    }

    protected function rollbackTransaction()
    {
        $rollback = DbManager::rollbackPoint($this->transactionName);
        if (!empty($rollback)) {
            $exception = new Exception("ANKTEST001", $rollback);
            $exception->setHttpStatus("500", "Error rollback : save point is not define");
            $exception->setUserMessage(err);
            throw $exception;
        }
    }
}
