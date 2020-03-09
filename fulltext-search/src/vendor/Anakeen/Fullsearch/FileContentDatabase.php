<?php

namespace Anakeen\Fullsearch;

use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\DbObj;
use Anakeen\Core\Internal\QueryDb;
use Anakeen\Core\Utils\Date;
use Anakeen\Vault\FileInfo;

class FileContentDatabase extends DbObj
{
    const DBTABLE = "files.content";
    public $dbtable = self::DBTABLE;
    public $fields = [
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
    public $fileid;
    public $mdate;
    public $taskid;
    public $status;
    public $textcontent;


    public $order_by = "";

    public $sqlcreate = <<<SQL
create schema if not exists files;
create table files.content (
  fileid bigint,
  taskid text,
  status char,
  mdate timestamp,
  textcontent text
                   );
                   
create unique index if not exists filesvault_idx on files.content(fileid);
create index if not exists files_idx on files.content(taskid);
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

    public static function deleteFileIndex($fileid)
    {
        $sql = sprintf(
            "delete from %s where fileid=%d ",
            self::DBTABLE,
            $fileid
        );
        DbManager::query($sql);
    }

    public static function isUptodate(FileInfo $info)
    {
        $sql = sprintf(
            "select fileid from %s where fileid=%d and mdate > '%s'",
            self::DBTABLE,
            $info->id_file,
            pg_escape_string($info->mdate)
        );
        DbManager::query($sql, $r, true, true);
        return $r !== false;
    }

    /**
     * @param int $fileid
     * @return FileContentDatabase|null
     * @throws \Anakeen\Database\Exception
     */
    public static function getFromFileid(int $fileid)
    {
        $q=new QueryDb("", self::class);
        $q->addQuery(sprintf("fileid = %d", $fileid));
        $r=$q->query(0, 1);
        if ($q->nb > 0) {
            return $r[0];
        }
        return null;
    }
}
