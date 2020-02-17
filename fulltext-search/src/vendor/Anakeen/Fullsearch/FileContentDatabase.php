<?php

namespace Anakeen\Fullsearch;

use Anakeen\Core\Internal\DbObj;

class FileContentDatabase extends DbObj
{
    public $dbtable = "files.content";
    public $fields = [
        "docid",
        "field",
        "index",
        "taskid",
        "status",
        "textcontent"
    ];

    public $id_fields
        = [
            "taskid"
        ];
    public $docid;
    public $field;
    public $index = -1;
    public $taskid;
    public $status;
    public $textcontent;


    public $order_by = "";

    public $sqlcreate = <<<SQL
create schema if not exists files;
create table files.content (
  docid int references docread(id),
  field text,
  taskid text not null,
  status char,
  index int default -1,
  textcontent text
                   );
                   
create index if not exists files_idx on files.content(docid);
create unique index if not exists files_idx on files.content(taskid);
SQL;


}
