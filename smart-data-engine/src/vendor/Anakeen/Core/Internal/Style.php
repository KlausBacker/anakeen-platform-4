<?php

namespace Anakeen\Core\Internal;

class Style extends DbObj
{
    const RULE_FLAG_PARSE_ON_RUNTIME = 1;

    public $fields
        = array(
            "name",
            "description",
            "parsable",
            "rules"
        );

    public $id_fields
        = array(
            "name"
        );
    public $name;
    public $description;
    public $parsable;
    protected $rules;


    public $sqlcreate
        = "
    create table style (
        name text not null,
        primary key (name),
        description text,
        parsable char default 'N',
        rules text default '{}'
    );
    create sequence SEQ_ID_STYLE start 10000;
";

    public $dbtable = "style";

    protected $_expanded_rules = array();

    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        parent::__construct($dbaccess, $id, $res, $dbid);
        if (!empty($this->rules)) {
            $this->_expanded_rules = json_decode($this->rules, true);
        }
    }

    public function preupdate()
    {
        $this->encodeRules();
    }

    public function preInsert()
    {
        $this->encodeRules();
    }

    protected function encodeRules()
    {
        $this->rules = json_encode($this->_expanded_rules);
    }


    public function setRules(array $filesDefinition)
    {
        $this->_expanded_rules = $filesDefinition;
    }

    public function setRule($fileType, $file, $definition)
    {
        $this->_expanded_rules[$fileType][$file] = $definition;
    }

    public function getRules()
    {
        return $this->_expanded_rules;
    }

    public function getRule($fileType, $file)
    {
        $file = substr($file, 4); // delete "css/"
        if (!isset($this->_expanded_rules[$fileType])) {
            return null;
        }

        if (!isset($this->_expanded_rules[$fileType][$file])) {
            return null;
        }
        return $this->_expanded_rules[$fileType][$file];
    }
}
