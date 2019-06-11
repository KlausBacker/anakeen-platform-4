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

        DbManager::query("select setval('seq_id_users', (select max(id) from users))");
        DbManager::query("select setval('seq_id_acl', (select max(id) from acl))");
        DbManager::query("select setval('seq_id_docvgroup', (select max(num) from vgroup))");
        DbManager::query("select setval('seq_id_vaultdiskdirstorage', (select max(id_dir) from vaultdiskdirstorage))");
        DbManager::query("select setval('seq_id_vaultdiskfsstorage', (select max(id_fs) from vaultdiskfsstorage))");


        return $data;
    }

}
