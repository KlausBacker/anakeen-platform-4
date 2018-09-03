<?php
/**
 * Flat table of all smart element. Used to perform quick searches
 *
 * @author Anakeen
 */

class DocRead extends DbObj
{
    /**
     * identifier of document
     * @public int
     */
    public $id;
    
    public $id_fields = array(
        "id"
    );
    
    public $dbtable = "docread";
    public $sqlcreate = "
create table docread as (select * from only doc) with no data;
ALTER TABLE docread ADD CONSTRAINT docread_pkey PRIMARY KEY (id);
create index fromid_docread on docread(fromid);
create index initid_docread on docread(initid);
create index title_docread on docread(title);
create index docty_docread on docread(doctype);
create index full_docread on docread using gist(fulltext);";
    
    public $fields = array(
        "id",
        "owner",
        "title",
        "revision",
        "version",
        "initid",
        "fromid",
        "doctype",
        "locked",
        "allocated",
        "icon",
        "lmodify",
        "profid",
        "usefor",
        "cdate",
        "mdate",
        "comment",
        "classname",
        "state",
        "wid",
        "postitid",
        "domainid",
        "lockdomainid",
        "cvid",
        "name",
        "dprofid",
        "views",
        "atags",
        "prelid",
        "confidential",
        "ldapdn"
    );
    
    public $sup_fields = array(
        "fieldvalues"
    ); // not be in fields else trigger error
}
