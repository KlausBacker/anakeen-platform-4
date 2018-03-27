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
    /**
     * @var \Anakeen\Core\Internal\Application
     */
    public $parent;

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

    public function set(&$parent)
    {
        $this->parent = &$parent;
    }

    public function getImageUrl($img, $default)
    {
        $root = DEFAULT_PUBDIR;

        $socStyle = $this->parent->Getparam("CORE_SOCSTYLE");
        // first see if i have an society style
        if (($socStyle != "") && file_exists($root . "/STYLE/" . $socStyle . "/Images/" . $img)) {
            return ("STYLE/" . $socStyle . "/Images/" . $img);
        }

        if (file_exists($root . "/STYLE/" . $this->name . "/Images/" . $img)) {
            return ("STYLE/" . $this->name . "/Images/" . $img);
        } else {
            return ($default);
        }
    }

    public function getLayoutFile($layname, $default = "")
    {
        $root = DEFAULT_PUBDIR;

        $socStyle = $this->parent->Getparam("CORE_SOCSTYLE");
        // first see if i have an society style
        if ($socStyle != "") {
            $file = $root . "/STYLE/" . $socStyle . "/Layout/" . $layname;
            if (file_exists($file)) {
                return ($file);
            }
        }

        $file = $root . "/STYLE/" . $this->name . "/Layout/" . $layname;
        if (file_exists($file)) {
            return ($file);
        }

        return ($default);
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


