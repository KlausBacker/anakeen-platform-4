<?php

namespace Anakeen\Database\Migration;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;

class DefaultParameterValues
{
    /**
     * Move default parameter values to initial if not already set
     * Delete default parameter values when are not in an array
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Database\Exception
     */
    public static function removeValues()
    {
        $sql = "select a1.id, a1.docid as famid, docfam.defaultvalues->>a1.id  as defval" .
            " from docfam, docattr a1, docattr a2" .
            " where a1.docid= docfam.id and a1.usefor = 'Q' and a1.frameid = a2.id and docfam.defaultvalues->>a1.id is not null and a2.type !~ 'array';";

        DbManager::query($sql, $result);

        foreach ($result as $defaultData) {
            $struct=SEManager::getFamily($defaultData["famid"]);
            if ($struct) {
                $pid=$defaultData["id"];
                $pval=$defaultData["defval"];
                $pValues=$struct->getOwnParams();
                if (! isset($pValues[$pid])) {
                    // move to initial parameter value if not already set
                    $struct->setParam($pid, $pval);
                }
                $struct->setDefValue($pid, null);
                $struct->modify();
            }
        }
    }
}
