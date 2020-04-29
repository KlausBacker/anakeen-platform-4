<?php
/*
 * @author Anakeen
 * @package FDL
*/

require_once __DIR__."/Class.PgObj.php";

class Engine extends PgObj
{
    public $fields = array(
        "name",
        "mime",
        "command",
        "comment"
        // comment text
        
    );
    /**
     * transformation name
     * @var string
     */
    public $name;
    /**
     * compatible system mime type with the command (like text/html)
     * @var string
     */
    public $mime;
    /**
     * complete path of the program to use for transformation
     * @var string
     */
    public $command;
    /**
     * description of the command
     * @var string
     */
    public $comment;
    
    public $id_fields = array(
        "name",
        "mime"
    );
    
    public $dbtable = "engine";
    
    public $sqlcreate = <<< 'SQL'
CREATE TABLE engine (
    name TEXT NOT NULL,
    mime TEXT NOT NULL,
    command TEXT NOT NULL,
    comment TEXT ,
    CONSTRAINT engine_pkey PRIMARY KEY(name,mime)
);
SQL;
    
    public function getNearEngine($engine, $mime)
    {
        if ($this->isAffected()) {
            return false;
        }
        
        $eng = new Engine($this->dbaccess, array(
            $engine,
            $mime
        ));
        if ($eng->isAffected()) {
            return $eng;
        }
        
        $mime = strtok($mime, ";");
        $eng = new Engine($this->dbaccess, array(
            $engine,
            $mime
        ));
        if ($eng->isAffected()) {
            return $eng;
        }
        
        $mime = strtok($mime, "/");
        $eng = new Engine($this->dbaccess, array(
            $engine,
            $mime
        ));
        if ($eng->isAffected()) {
            return $eng;
        }
        
        $eng = new Engine($this->dbaccess, array(
            $engine,
            $mime . '/*'
        ));
        if ($eng->isAffected()) {
            return $eng;
        }
        
        $eng = new Engine($this->dbaccess, array(
            $engine,
            '*'
        ));
        if ($eng->isAffected()) {
            return $eng;
        }
        
        return false;
    }
    
    public function existsEngine($engine)
    {
        include_once(__DIR__."/Class.QueryPg.php");
        $q = new QueryPg($this->dbaccess, "Engine");
        $q->AddQuery("name='" . pg_escape_string($engine) . "'");
        return ($q->Count() > 0);
    }
    
    public function getAllEngines()
    {
        include_once(__DIR__."/Class.QueryPg.php");
        $q = new QueryPg($this->dbaccess, "Engine");
        $q->AddQuery("true");
        return $q->Query(0, 0, "TABLE");
    }
}
