<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Class.Out.php,v 1.2 2003/08/18 15:46:42 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// --------------------------------------------------------------------------
// $Id: Class.Out.php,v 1.2 2003/08/18 15:46:42 eric Exp $
// anakeen 1999 - marc.claverie@anakeen.com
// --------------------------------------------------------------------------
//
// $Log: Class.Out.php,v $
// Revision 1.2  2003/08/18 15:46:42  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.1  2001/02/10 09:45:51  yannick
// Ajout de vieilles classes
//
// Revision 1.2  1999/12/01 20:42:39  marc
// Version 1.0 qui semble marcher
//
// Revision 1.1  1999/12/01 20:22:19  marc
// Creation
//
// --------------------------------------------------------------------------
$CLASS_OUT_PHP = "";

class Out
{
    
    var $stream;
    var $cr;
    
    function Out($rt = 0)
    {
        $this->stream = "";
        if ($rt) $this->cr = "\n";
        else $this->cr = "";
    }
    
    function Cat($string)
    {
        $this->stream = $this->stream . $string . $this->cr;
    }
    function Reset()
    {
        $this->stream = "";
    }
    
    function Flush()
    {
        return ($this->stream);
    }
}
?>
