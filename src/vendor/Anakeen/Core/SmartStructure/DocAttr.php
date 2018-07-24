<?php

namespace Anakeen\Core\SmartStructure;

use Anakeen\Core\Internal\DbObj;

/**
 * Database Attribute document
 */
class DocAttr extends DbObj
{
    public $fields = array(
        "id",
        "docid",
        "frameid",
        "labeltext",
        "title",
        "abstract",
        "type",
        "ordered",
        "accessibility", // None, Read, Write, ReadWrite
        "needed",
        "link",
        "phpfile",
        "phpfunc",
        "elink",
        "phpconstraint",
        "usefor",
        "options",
        "properties"
    );

    public $id_fields = array(
        "docid",
        "id"
    );

    public $dbtable = "docattr";

    public $order_by = "ordered";

    public $fulltextfields = array(
        "labeltext"
    );

    public $id;
    public $docid;
    public $frameid;
    public $labeltext;
    public $title;
    public $abstract;
    public $type;
    public $ordered;
    public $accessibility; // Write Read None
    public $needed;
    public $link;
    public $phpfile;
    public $phpfunc;
    public $elink;
    public $phpconstraint;
    public $usefor;
    public $options;
    public $properties;

    public $sqlcreate = "
create table docattr ( id  name not null,
                     docid int not null,
                     frameid  name,
                     labeltext text,
                     title  char,
                     abstract  char,
                     type  text,
                     ordered int,
                     accessibility text,
                     needed char,
                     link text,
                     phpfile text,
                     phpfunc text,
                     elink text,
                     phpconstraint text,
                     usefor char DEFAULT 'N',
                     options text,
                     properties jsonb
                   );
create sequence seq_id_docattr start 1000;
create unique index idx_iddocid on docattr(id, docid);";
    // possible type of attributes
    public $deftype = array(
        "text",
        "longtext",
        "image",
        "file",
        "frame",
        "enum",
        "date",
        "integer",
        "double",
        "money",
        "password"
    );

    public function preInsert()
    {
        // compute new id
        if ($this->id == "") {
            $res = pg_query($this->dbid, "select nextval ('seq_id_docattr')");
            $arr = pg_fetch_array($res, 0);
            $this->id = "auto_" . $arr[0]; // not a number must be alphanumeric begin with letter
        }
        $this->id = strtolower($this->id);
        if ($this->id[0] != ':') {
            if ($this->type == "") {
                $this->type = "text";
            }
            if ($this->abstract == "") {
                $this->abstract = 'N';
            }
            if ($this->title == "") {
                $this->title = 'N';
            }
            if ($this->usefor == "") {
                $this->usefor = 'N';
            }
        }
    }

    public function getRawType($type = '')
    {
        if (!$type) {
            $type = $this->type;
        }
        return strtok($type, '(');
    }

    public function isStructure()
    {
        $rtype = $this->getRawType();
        return ($rtype == "frame" || $rtype == "tab");
    }

    public function isAbstract()
    {
        return (strtolower($this->abstract) == "y");
    }

    public function isTitle()
    {
        return (strtolower($this->title) == "y");
    }

    public function isNeeded()
    {
        return (strtolower($this->needed) == "y");
    }
}
