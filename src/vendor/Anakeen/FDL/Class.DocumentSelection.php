<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Selction Document Object Definition
 *
 * @author Anakeen
 * @version $Id:  $
 * @package FDL
 */
/**
 */
include_once ("FDL/Class.Document.php");
/**
 * Document selection Class
 *
 */
class Fdl_DocumentSelection
{
    private $mainSelector;
    private $selectionItems;
    function __construct($config)
    {
        foreach ($config as $k => $v) $this->$k = $v;
        $this->dbaccess = getDbAccess();
    }
    /**
     * return document identificators from selection
     * @return array
     */
    function getIdentificators()
    {
        if (strtolower($this->mainSelector) != "all") {
            if (is_array($this->selectionItems)) {
                return $this->selectionItems;
            } else return array();
        } else {
            $cc = $this->getRawDocuments();
            if (is_array($cc)) return array_keys($cc);
            else return array();
        }
    }
    /**
     * return document data from selection
     * @return array
     */
    function getRawDocuments()
    {
        
        if (strtolower($this->mainSelector) != "all") {
            if (is_array($this->selectionItems)) {
                return getDocsFromIds($this->dbaccess, $this->selectionItems);
            }
        } else {
            $idc = $this->collectionId;
            $c = new Fdl_Collection($idc);
            if ($c->isAlive()) {
                /**
                 * @var DocCollection $idoc
                 */
                $idoc = $c->getInternalDocument();
                $filter = array();
                $famid = "";
                if ($this->filter) {
                    $err = $idoc->object2SqlFilter($this->filter, $famid, $sql);
                    if ($err == "") {
                        if ($sql) $filter[] = $sql;
                    } else return null;
                }
                if (is_array($this->selectionItems)) {
                    $cc = $idoc->getContent(true, $filter, $famid);
                    foreach ($this->selectionItems as $eid) {
                        if (isset($cc[$eid])) unset($cc[$eid]);
                        else {
                            $err = simpleQuery($this->dbaccess, sprintf("select initid from docread where id=%d", $eid) , $ids, true, true);
                            if (isset($cc[$ids])) unset($cc[$ids]);
                        }
                    }
                    return $cc;
                } else {
                    return $idoc->getContent(true, $filter, $famid);
                }
            }
        }
        return array();
    }
}
