<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Migration\Utils;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

/**
 * Class ConfigApplicationTransfert
 * @package Anakeen\Routes\Migration\Database
 * @use by route POST /api/v2/migration/database/transfert/application/{application}
 */
class FinalUpdates
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);
        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }

    protected function initParameters($args)
    {

    }

    protected function doRequest()
    {
        $data = [];

        // Transfert field access
        DbManager::query("update family.igroup set fallid=(select cfallid from docfam where name='IGROUP') where fallid is null and fromid=127;");
        DbManager::query("update family.iuser set fallid=(select cfallid from docfam where name='IUSER') where fallid is null and fromid=128;");

        // Transfert cvdoc
        DbManager::query("update family.igroup set cvid=(select ccvid from docfam where name='IGROUP') where cvid is null and fromid=127;");
        DbManager::query("update family.iuser set cvid=(select ccvid from docfam where name='IUSER') where cvid is null and fromid=128;");

        // update fieldvalues column for workflows
        DbManager::query("update family.wdoc set id=id;");


        return $data;
    }

}
