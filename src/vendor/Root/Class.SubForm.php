<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Class.SubForm.php,v 1.3 2006/06/20 16:18:07 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------------------
// $Id: Class.SubForm.php,v 1.3 2006/06/20 16:18:07 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Form/Class.SubForm.php,v $
// ---------------------------------------------------------------
// $Log: Class.SubForm.php,v $
//
//
class SubForm
{
    // This class is used to produce HTML/JS code when you want to
    // create a separate window which exchange values with its parent
    // window (for instance an edit/update window or a query window)
    public $mainjs = '
function submit_withpar(height,width,name,[id],url) {
  subwindow(height,width,name,url+\'&[id]=\'+[id]);
}
';
    
    public $jsmaincall = 'submit_withpar([height],[width],\'[name]\',\'[id]\',\'[url]\')';
    
    public $mainform = '
<form name="[name]" method="POST" action="[url]">
[BLOCK PAR]
  <input type="hidden" name="[name]" value="[val]"> [ENDBLOCK PAR]
</form>
';
    
    public $subjs = '
function sendform() {
  var p = self.opener.document.forms.[name];
  var lf = self.document.[name];
[BLOCK PAR]
  if( lf.[name] ) { p.[name].value = lf.[name].value; } [ENDBLOCK PAR]

[BLOCK SEL]
  if( lf.[name] ) { p.[name].value = lf.[name].options[lf.[name].selectedIndex].value; } [ENDBLOCK SEL]
  p.submit();
}';
    
    public $param = array(); // contains all exchanged vars in the form
    // "key" => "val" , val is the initial value
    // of the key.
    public function __construct($name, $width = 100, $height = 100, $mainurl = "", $suburl = "")
    {
        $this->name = $name;
        $this->width = $width;
        $this->height = $height;
        $this->mainurl = $mainurl;
        $this->suburl = $suburl;
    }
    
    public function SetParams($array)
    {
        $this->param = array_merge($array, $this->param);
    }
    
    public function SetParam($key, $val = "", $type = "")
    {
        $this->param[$key]["val"] = $val;
        $this->param[$key]["typ"] = $type;
    }
    
    public function SetKey($key)
    {
        $this->key = $key;
    }
    
    public function GetMainForm()
    {
        $lay = new Layout("", null, $this->mainform);
        $tab = array();
        reset($this->param);
        $c = - 1;
        foreach ($this->param as $k => $v) {
            $tab[$c]["name"] = $k;
            $tab[$c]["val"] = $v["val"];
            $c++;
        }
        $lay->SetBlockData("PAR", $tab);
        $lay->Set("url", $this->mainurl);
        $lay->Set("name", $this->name);
        return ($lay->gen());
    }
    
    public function GetMainJs()
    {
        $lay = new Layout("", null, $this->mainjs);
        $lay->Set("formname", $this->name);
        $lay->Set("id", $this->key);
        return ($lay->gen());
    }
    
    public function GetSubJs()
    {
        $lay = new Layout("", null, $this->subjs);
        $tab = array();
        reset($this->param);
        $isel = $c = - 1;
        foreach ($this->param as $k => $v) {
            if ($v["typ"] == "sel") {
                $isel++;
                $tabsel[$isel]["name"] = $k;
            } else {
                $c++;
                $tab[$c]["name"] = $k;
            }
        }
        if ($isel > - 1) {
            $lay->SetBlockData("SEL", $tabsel);
        } else {
            $lay->SetBlockData("SEL", null);
        }
        if ($c > - 1) {
            $lay->SetBlockData("PAR", $tab);
        } else {
            $lay->SetBlockData("PAR", null);
        }
        $lay->Set("name", $this->name);
        return ($lay->gen());
    }
    
    public function GetLinkJsMainCall()
    {
        $lay = new Layout("", "", $this->jsmaincall);
        $lay->Set("url", $this->suburl);
        $lay->Set("width", $this->width);
        $lay->Set("height", $this->height);
        $lay->Set("name", $this->name);
        return ($lay->gen());
    }
    
    public function GetEmptyJsMainCall()
    {
        $lay = new Layout("", "", $this->jsmaincall);
        $lay->Set("id", "");
        $lay->Set("url", $this->suburl);
        $lay->Set("width", $this->width);
        $lay->Set("height", $this->height);
        $lay->Set("name", $this->name);
        return ($lay->gen());
    }
    // CLASS END
}
