<?php

namespace Anakeen\Core\Internal;

use Anakeen\Core\DbManager;

class ParamDef extends DbObj
{
    public $fields
        = array(
            "name",
            "isuser",
            "domain",
            "category",
            "descr",
            "kind"
        );
    public $name;
    public $isuser;
    public $appid;
    public $domain;
    public $descr;
    public $category;
    public $kind;

    public $id_fields
        = array(
            "name"
        );

    public $dbtable = "paramdef";

    public $sqlcreate
        = '
      create table paramdef (
              name    text,
              isuser   varchar(1),
              domain text,
              category text,
              descr    text,
              kind    text);
      create unique index paramdef_idxna on paramdef(name);
                 ';

    /**
     * get Param def object from name
     *
     * @param string $name  parameter name
     *
     * @return \Anakeen\Core\Internal\ParamDef
     */
    public static function getParamDef($name)
    {
        $d = null;

        DbManager::query(sprintf("select * from paramdef where name='%s'", pg_escape_string($name)), $paramDefValues, false, true);

        if (!empty($paramDefValues)) {
            $d = new \Anakeen\Core\Internal\ParamDef();
            $d->Affect($paramDefValues);
        }
        return $d;
    }
}
