<?php
/**
 * Parameters values
 *
 */
namespace Anakeen\Core\Internal;

/** @deprecated use \Anakeen\Core\Internal\Param::PARAM_APP instead */
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
        "appid",
        "val"
    );
    
    public $id_fields = array(
        "name",
        "type",
        "appid"
    );
    
    public $name;
    public $type;
    public $appid;
    public $val;
    public $dbtable = "paramv";
    
    public $sqlcreate = '
      create table paramv (
              name   varchar(50) not null,
              type   varchar(21),
              appid  int4,
              val    text);
      create index paramv_idx2 on paramv(name);
      create unique index paramv_idx3 on paramv(name,type,appid);
                 ';
    
    public $buffer = array();
    
    public function PreInsert()
    {
        if (strpos($this->name, " ") != 0) {
            return _("Parameter name does not include spaces");
        }
        return '';
    }
    public function PostInit()
    {
        $opd = new \Anakeen\Core\Internal\ParamDef();
        $opd->create();
    }
    public function PreUpdate()
    {
        $this->PreInsert();
    }
    
    public function SetKey($appid, $userid, $styleid = "0")
    {
        $this->appid = $appid;
        $this->buffer = array_merge($this->buffer, $this->GetAll($appid, $userid, $styleid));
    }
    
    public function Set($name, $val, $type = self::PARAM_GLB, $appid = '')
    {
        global $action;
        if ($action) {
            $action->parent->session->unregister("sessparam" . $appid);
        }
        $this->name = $name;
        $this->val = $val;
        $this->type = $type;
        
        $pdef = \Anakeen\Core\Internal\ParamDef::getParamDef($name, $appid);
        
        if ($pdef && $pdef->isAffected()) {
            if ($pdef->isglob == 'Y') {
                $appid = $pdef->appid;
            }
        }
        $this->appid = $appid;
        
        $paramt = new \Anakeen\Core\Internal\Param($this->dbaccess, array(
            $name,
            $type,
            $appid
        ));
        if ($paramt->isAffected()) {
            $err = $this->Modify();
        } else {
            $err = $this->Add();
        }
        
        $otype = '';
        if ($type == self::PARAM_GLB) {
            $otype = self::PARAM_APP;
        } elseif ($type == self::PARAM_APP) {
            $otype = self::PARAM_GLB;
        }
        if ($otype) {
            // delete incompatible parameter
            $paramo = new \Anakeen\Core\Internal\Param($this->dbaccess, array(
                $name,
                $otype,
                $appid
            ));
            if ($paramo->isAffected()) {
                $paramo->delete();
            }
        }
        
        $this->buffer[$name] = $val;
        return $err;
    }
    
    public function SetVolatile($name, $val)
    {
        if ($val !== null) {
            $this->buffer[$name] = $val;
        } else {
            unset($this->buffer[$name]);
        }
    }
    
    public function Get($name, $def = "")
    {
        require_once('WHAT/Class.ApplicationParameterManager.php');
        
        if (($value = \Anakeen\Core\Internal\ApplicationParameterManager::_catchDeprecatedGlobalParameter($name)) !== null) {
            return $value;
        }
        if (isset($this->buffer[$name])) {
            return ($this->buffer[$name]);
        } else {
            return ($def);
        }
    }
    
    public function GetAll($appid = "", $userid, $styleid = "0")
    {
        if ($appid == "") {
            $appid = $this->appid;
        }
        $psize = new \Anakeen\Core\Internal\Param($this->dbaccess, array(
            "FONTSIZE",
            self::PARAM_USER . $userid,
            "1"
        ));
        $out = array();
        if ($psize->val != '') {
            $size = $psize->val;
        } else {
            $size = 'normal';
        }
        $size = 'SIZE_' . strtoupper($size);
        
        if ($appid) {
            if ($userid) {
                $styleIdPG = pg_escape_string($styleid);
                $sql = sprintf("select distinct on(paramv.name) paramv.* from paramv left join paramdef on (paramv.name=paramdef.name) where
(paramv.type = '%s')  OR (paramv.appid=%d and (paramv.type='%s' or paramv.type='%s%d' or paramv.type='%s%s')) OR (paramdef.isglob='Y' and (paramv.type='%s%d' or paramv.type='%s%s')) OR
(paramv.type='%s%s') order by paramv.name, paramv.type desc", self::PARAM_GLB, $appid, self::PARAM_APP, self::PARAM_USER, $userid, self::PARAM_STYLE, $styleIdPG, self::PARAM_USER, $userid, self::PARAM_STYLE, $styleIdPG, self::PARAM_STYLE, pg_escape_string($size));
            } else {
                $sql = sprintf("SELECT * from paramv where type='G' or (type='A' and appid=%d);", $appid);
            }
            simpleQuery($this->dbaccess, $sql, $list);
            
            foreach ($list as $v) {
                $out[$v["name"]] = $v["val"];
            }
        } else {
            $this->log->debug("$appid no constant define for this application");
        }
        return ($out);
    }
    
    public function GetUser($userid = \Anakeen\Core\Account::ANONYMOUS_ID, $styleid = "")
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \Anakeen\Core\Internal\Param::class);
        
        $tlist = $query->Query(0, 0, "TABLE", "select  distinct on(paramv.name, paramv.appid) paramv.*,  paramdef.descr, paramdef.kind  from paramv, paramdef where paramv.name = paramdef.name and paramdef.isuser='Y' and (" . " (type = '" . self::PARAM_GLB . "') " . " OR (type='" . self::PARAM_APP . "')" . " OR (type='" . self::PARAM_STYLE . $styleid . "' )" . " OR (type='" . self::PARAM_USER . $userid . "' ))" . " order by paramv.name, paramv.appid, paramv.type desc");
        
        return ($tlist);
    }
    /**
     * get list of parameters for a style
     * @param bool $onlystyle if false return all parameters excepts user parameters with style parameters
     * if true return only parameters redifined by the style
     * @return array of parameters values
     */
    public function GetStyle($styleid, $onlystyle = false)
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \Anakeen\Core\Internal\Param::class);
        if ($onlystyle) {
            $query->AddQuery("type='" . self::PARAM_STYLE . $styleid . "'");
            $tlist = $query->Query(0, 0, "TABLE");
        } else {
            $tlist = $query->Query(0, 0, "TABLE", "select  distinct on(paramv.name, paramv.appid) paramv.*,  paramdef.descr, paramdef.kind  from paramv, paramdef where paramv.name = paramdef.name and paramdef.isstyle='Y' and (" . " (type = '" . self::PARAM_GLB . "') " . " OR (type='" . self::PARAM_APP . "')" . " OR (type='" . self::PARAM_STYLE . $styleid . "' ))" . " order by paramv.name, paramv.appid, paramv.type desc");
        }
        return ($tlist);
    }
    
    public function GetApps()
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \Anakeen\Core\Internal\Param::class);
        
        $tlist = $query->Query(0, 0, "TABLE", "select  paramv.*, paramdef.descr, paramdef.kind  from paramv, paramdef where paramv.name = paramdef.name and  (" . " (type = '" . self::PARAM_GLB . "') " . " OR (type='" . self::PARAM_APP . "'))" . " order by paramv.appid, paramv.name, type desc");
        
        return ($tlist);
    }
    
    public function GetUParam($p, $u = \Anakeen\Core\Account::ANONYMOUS_ID, $appid = "")
    {
        if ($appid == "") {
            $appid = $this->appid;
        }
        $req = "select val from paramv where name='" . $p . "' and type='U" . $u . "' and appid=" . $appid . ";";
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \Anakeen\Core\Internal\Param::class);
        $tlist = $query->Query(0, 0, "TABLE", $req);
        if ($query->nb != 0) {
            return $tlist[0]["val"];
        }
        return "";
    }
    // delete paramters that cannot be change after initialisation
    public function DelStatic($appid)
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \Anakeen\Core\Internal\Param::class);
        $sql = sprintf("select paramv.*  from paramv, paramdef where paramdef.name=paramv.name and paramdef.kind='static' and paramdef.isuser!='Y' and paramv.appid=%d", $appid);
        $list = $query->Query(0, 0, "LIST", $sql);
        
        if ($query->nb != 0) {
            reset($list);
            /**
             * @var \Anakeen\Core\Internal\Param $v
             */
            foreach ($list as $k => $v) {
                $v->Delete();
                if (isset($this->buffer[$v->name])) {
                    unset($this->buffer[$v->name]);
                }
            }
        }
    }
    
    public function PostDelete()
    {
        if (isset($this->buffer[$this->name])) {
            unset($this->buffer[$this->name]);
        }
    }
    
    public function DelAll($appid = "")
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \Anakeen\Core\Internal\Param::class);
        // delete all parameters not used by application
        $query->Query(0, 0, "TABLE", "delete from paramv where appid not in (select id from application) ");
        return;
    }
    // FIN DE CLASSE
}
