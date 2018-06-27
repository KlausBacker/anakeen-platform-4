<?php
/**
 * Parameters values
 *
 */
namespace Anakeen\Core\Internal;

/** @deprecated use \Anakeen\Core\Internal\Param::PARAM_APP instead */
use Anakeen\Core\DbManager;

define("PARAM_APP", "A");
/** @deprecated use \Anakeen\Core\Internal\Param::PARAM_GLB instead */
define("PARAM_GLB", "G");
/** @deprecated use \Anakeen\Core\Internal\Param::PARAM_USER instead */
define("PARAM_USER", "U");
/** @deprecated use \Anakeen\Core\Internal\Param::PARAM_STYLE instead */
define("PARAM_STYLE", "S");

class Param extends DbObj
{
    const PARAM_APP = "A";
    const PARAM_GLB = "G";
    const PARAM_USER = "U";
    const PARAM_STYLE = "S";
    
    public $fields = array(
        "name",
        "type",
        "val"
    );
    
    public $id_fields = array(
        "name",
        "type",
    );
    
    public $name;
    public $type;
    public $val;
    public $dbtable = "paramv";
    
    public $sqlcreate = '
      create table paramv (
              name   varchar(50) not null,
              type   varchar(21),
              val    text);
      create index paramv_idx2 on paramv(name);
      create unique index paramv_idx3 on paramv(name,type);
                 ';

    
    public function preInsert()
    {
        if (strpos($this->name, " ") != 0) {
            return _("Parameter name does not include spaces");
        }
        return '';
    }
    public function postInit()
    {
        $opd = new \Anakeen\Core\Internal\ParamDef();
        $opd->create();
    }
    public function preUpdate()
    {
        $this->preInsert();
    }

    public function set($name, $val, $type = self::PARAM_GLB)
    {
        $this->name = $name;
        $this->val = $val;
        $this->type = $type;

        $paramt = new \Anakeen\Core\Internal\Param($this->dbaccess, array(
            $name,
            $type
        ));
        if ($paramt->isAffected()) {
            $err = $this->Modify();
        } else {
            $err = $this->add();
        }

        return $err;
    }

}
