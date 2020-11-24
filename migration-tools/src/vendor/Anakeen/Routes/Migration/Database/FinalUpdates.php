<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;

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
        \Anakeen\Database\Migration\DefaultParameterValues::removeValues();
        $data = [];

        // Transfert field access
        DbManager::query("update docfam set cfallid=(select cfallid from docfam where name='IGROUP') where fromid=(select id from docfam where name = 'IGROUP');");
        DbManager::query("update docfam set cfallid=(select cfallid from docfam where name='IUSER') where fromid=(select id from docfam where name = 'IUSER');");
        DbManager::query("update family.igroup set fallid=(select cfallid from docfam where name='IGROUP') where fallid is null;");
        DbManager::query("update family.iuser set fallid=(select cfallid from docfam where name='IUSER') where fallid is null;");

        // Transfert cvdoc
        DbManager::query("update family.igroup set cvid=(select ccvid from docfam where name='IGROUP') where cvid is null and fromid=127;");
        DbManager::query("update family.iuser set cvid=(select ccvid from docfam where name='IUSER') where cvid is null and fromid=128;");


        // Update revdate to mdate in searches
        DbManager::query("update family.dsearch set se_attrids=array_replace(se_attrids, 'revdate', 'mdate') where 'revdate' = any(se_attrids);");
        DbManager::query("update family.report set rep_idcols=array_replace(rep_idcols, 'revdate', 'mdate') where 'revdate' = any(rep_idcols);");
        // Update activity to state in searches
        DbManager::query("update family.dsearch set se_attrids=array_replace(se_attrids, 'activity', 'state') where 'activity' = any(se_attrids);");
        DbManager::query("update family.report set rep_idcols=array_replace(rep_idcols, 'activity', 'state') where 'activity' = any(rep_idcols);");
        // update fieldvalues column for workflows
        DbManager::query("update family.wdoc set id=id;");

        InitTransfert::restoreRoles();

        DbManager::query("select setval('seq_id_users', (select max(id) from users))");
        DbManager::query("select setval('seq_id_acl', (select max(id) from acl))");
        DbManager::query("select setval('seq_id_docvgroup', (select max(num) from vgroup))");
        DbManager::query("select setval('seq_id_vaultdiskdirstorage', (select max(id_dir) from vaultdiskdirstorage))");
        DbManager::query("select setval('seq_id_vaultdiskfsstorage', (select max(id_fs) from vaultdiskfsstorage))");


        DbManager::query("update docattr set accessibility = 'Read'  where options ~ 'autotitle=yes'");

        $this->nameElements("CVDOC", "cv_famid");
        $this->nameElements("MASK", "msk_famid");
        $this->nameElements("MAILTEMPLATE", "tmail_family");
        $this->nameElements("WDOC", "wf_famid");
        $this->nameElements("PDOC", "dpdoc_famid");

        $this->updateMailTimers();
        return $data;
    }

    protected function nameElements($structName, $relName)
    {
        $struct = SEManager::getFamily($structName);

        $sql = sprintf(
            "update doc%d as dd set name=('%s_' || docfam.name || '_' || dd.id) from docfam where dd.name is null and dd.doctype != 'Z' and dd.%s::int = docfam.id",
            $struct->id,
            $struct->name,
            $relName
        );
        DbManager::query($sql);
    }

    /**
     * Convert tmail index to array in doctimer table
     */
    protected function updateMailTimers()
    {
        $q = new \Anakeen\Core\Internal\QueryDb("", \DocTimer::class);
        $doctimers = $q->query(0, 0, "ITER");

        foreach ($doctimers as $doctimer) {
            /** @var \DocTimer $doctimer */
            $timerAction = unserialize($doctimer->actions);
            if (!is_array($timerAction["tmail"])) {
                $timerAction["tmail"] = explode("\n", $timerAction["tmail"]);
                $doctimer->actions = serialize($timerAction);
                $doctimer->modify(true, ["actions"], true);
            }
        }
    }
}
