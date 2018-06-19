<?php

namespace Anakeen\Core\Internal;

use Anakeen\Core\DbManager;

class ParamDef extends DbObj
{
    public $fields
        = array(
            "name",
            "isuser",
            "isstyle",
            "isglob",
            "category",
            "appid",
            "descr",
            "kind"
        );
    public $name;
    public $isuser;
    public $isstyle;
    public $isglob;
    public $appid;
    public $descr;
    public $category;
    public $kind;

    public $id_fields
        = array(
            "name",
            "appid"
        );

    public $dbtable = "paramdef";

    public $sqlcreate
        = '
      create table paramdef (
              name    text,
              isuser   varchar(1),
              isstyle   varchar(1),
              isglob   varchar(1),
              appid  int4,
              category text,
              descr    text,
              kind    text);
      create unique index paramdef_idxna on paramdef(name, appid);
                 ';

    /**
     * get Param def object from name
     *
     * @param string $name  parameter name
     * @param int    $appid application id
     *
     * @return \Anakeen\Core\Internal\ParamDef
     */
    public static function getParamDef($name, $appid = null)
    {
        $d = null;
        if ($appid == null) {
            DbManager::query(sprintf("select * from paramdef where name='%s'", pg_escape_string($name)), $paramDefValues, false, true);
        } else {
            $sql
                = <<< 'SQL'
            SELECT * from paramdef
            where name='%s'
              and (isglob='Y' or appid=%d or appid=1 or appid=(select id from application where name=(select childof from application where id=%d)));
SQL;
            $sqlp = sprintf($sql, pg_escape_string($name), $appid, $appid);
            DbManager::query($sqlp, $paramDefValues, false, true);
        }
        if (!empty($paramDefValues)) {
            $d = new \Anakeen\Core\Internal\ParamDef();
            $d->Affect($paramDefValues);
        }
        return $d;
    }
}
