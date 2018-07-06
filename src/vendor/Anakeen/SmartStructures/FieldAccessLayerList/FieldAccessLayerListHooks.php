<?php

namespace Anakeen\SmartStructures\FieldAccessLayerList;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Dcp\Exception;
use \SmartStructure\Fields\Fieldaccesslayerlist as myAttributes;

class FieldAccessLayerListHooks extends \Anakeen\SmartElement
{
    /**
     * Field Access Layer List has its own special access depend on special views
     * by default the three access are always set
     *
     * @var array
     */
    public $acls = array(
        "view",
        "edit",
        "delete"
    );

    public $usefor = 'SW';
    public $defDoctype = 'P';
    protected $doc;
    /**
     * @var FieldAccessLayerListHooks
     */
    protected $pdoc;

    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        parent::__construct($dbaccess, $id, $res, $dbid);
        // First construct acl array
        if (isset($this->fromid)) {
            // It's a profil itself
            $this->defProfFamId = $this->fromid;
        }

        $this->setAcls();
    }

    public function set(SmartElement &$doc)
    {
        if ($this->doc && $this->doc->id !== $doc->id) {
            $this->pdoc = null;
        }

        $this->doc = &$doc;
    }

    /**
     * Special control in case of dynamic controlled profil
     *
     * @param string $aclname
     * @param bool   $strict
     *
     * @return string
     */
    public function control($aclname, $strict = false)
    {
        $err = $this->docControl($aclname, $strict);
        if ($err == "") {
            return $err;
        } // normal case
        if ($this->getRawValue(myAttributes::dpdoc_famid) > 0) {
            if ($this->doc) {
                // special control for dynamic users
                if ($this->pdoc === null) {
                    $pdoc = SEManager::createTemporaryDocument($this->fromid);
                    $err = $pdoc->add();
                    if ($err != "") {
                        // can't create profil
                        throw new Exception("FieldAccessList::Control:" . $err);
                    }
                    $pdoc->acls = $this->acls;
                    $pdoc->extendedAcls = $this->extendedAcls;
                    $pdoc->accessControl()->setProfil($this->profid, $this->doc);

                    $this->pdoc = &$pdoc;
                }

                $err = $this->pdoc->docControl($aclname, $strict);
            }
        }
        return $err;
    }

    public function docControl($aclname, $strict = false)
    {
        return SmartElement::control($aclname, $strict);
    }

    protected function setAcls()
    {
        $this->extendedAcls = array();
        $layerAcls = $this->getMultipleRawValues(myAttributes::fall_aclname);
        $tl = $this->getMultipleRawValues(myAttributes::fall_layer);

        foreach ($layerAcls as $k => $acl) {
            if (!$acl) {
                continue;
            }
            $layerId = $tl[$k];
            $this->extendedAcls[$acl] = array(
                "name" => $acl,
                "description" => $layerId ? $this->getTitle($layerId) : "No$k"
            );

            $this->acls[$acl] = $acl;
        }
    }
}
