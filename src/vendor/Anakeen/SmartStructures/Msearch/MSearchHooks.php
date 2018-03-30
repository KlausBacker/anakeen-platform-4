<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Grouped searches
 */
namespace Anakeen\SmartStructures\MSearch;

class MSearchHooks extends \SmartStructure\Search
{
    public $defaultedit = "FDL:EDITBODYCARD";
    public $defaultview = "FDL:VIEWBODYCARD";
    
    public function ComputeQuery($keyword = "", $famid = - 1, $latest = "yes", $sensitive = false, $dirid = - 1, $subfolder = true, $full = false)
    {
        $tidsearch = $this->getMultipleRawValues("SEG_IDCOND");
        
        $query = array();
        foreach ($tidsearch as $k => $v) {
            /**
             * @var \SmartStructure\Search $doc
             */
            $doc = new_Doc($this->dbaccess, $v);
            $err = $doc->control("execute");
            
            if ($err == "" && method_exists($doc, "getQuery")) {
                $doc->setValue("SE_IDCFLD", $this->getRawValue("SE_IDCFLD"));
                $q = $doc->getQuery();
                
                $query[] = $q[0];
            }
        }
        
        return $query;
    }
    /**
     * return false : is never staticSql
     * @return bool
     */
    public function isStaticSql()
    {
        return false;
    }
}
