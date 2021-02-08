<?php

namespace Anakeen\Routes\Admin\Account;

use Anakeen\Core\DbManager;

/**
 * @note use by route GET /api/v2/admin/account/grouptree/nocategory/
 * @note use by route GET /api/v2/admin/account/grouptree/nocategory/{groupid:[0-9]+}
 * @note use by route GET /api/v2/admin/account/grouptree/nocategory/all
 */
class GroupTreeWithoutCategory extends GroupTree
{
    protected function addGroupSqlView()
    {
        $view = "
    create temporary view tv_groups as select users.* 
    from users left join doc127 on (users.fid = doc127.id)
       where doc127.grp_category is null and users.accounttype = 'G' 
            ";

        DbManager::query($view);
    }
}
