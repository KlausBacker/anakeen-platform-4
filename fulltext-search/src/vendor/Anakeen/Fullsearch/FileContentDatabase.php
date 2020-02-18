<?php

namespace Anakeen\Fullsearch;

use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\DbObj;
use Anakeen\Core\Utils\Date;

class FileContentDatabase extends DbObj
{
    const DBTABLE = "files.content";
    public $dbtable = self::DBTABLE;
    public $fields = [
        "docid",
        "field",
        "index",
        "fileid",
        "taskid",
        "status",
        "mdate",
        "textcontent"
    ];

    public $id_fields
        = [
            "taskid"
        ];
    public $docid;
    public $field;
    public $fileid;
    public $mdate;
    public $index = -1;
    public $taskid;
    public $status;
    public $textcontent;


    public $order_by = "";

    public $sqlcreate = <<<SQL
create schema if not exists files;
create table files.content (
  docid int references docread(id),
  fileid bigint,
  field text,
  taskid text not null,
  status char,
  index int default -1,
  mdate timestamp,
  textcontent text
                   );
                   
create index if not exists files_idx on files.content(docid);
create unique index if not exists files_idx on files.content(taskid);
SQL;

    public function preInsert()
    {
        $this->mdate = Date::getNow(true);
        return parent::preInsert();
    }

    public function preUpdate()
    {
        $this->mdate = Date::getNow(true);
        return parent::preUpdate();
    }

    public static function deleteFieldIndex($seId, $fieldName, $index)
    {
        $sql = sprintf(
            "delete from %s where docid=%d and field='%s' and index = %d",
            self::DBTABLE,
            $seId,
            pg_escape_string($fieldName),
            $index
        );
        DbManager::query($sql);
    }
}
