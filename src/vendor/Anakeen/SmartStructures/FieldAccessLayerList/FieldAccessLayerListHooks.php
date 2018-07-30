<?php

namespace Anakeen\SmartStructures\FieldAccessLayerList;

use Anakeen\Core\SmartStructure\ExtendedControl;
use \SmartStructure\Fields\Fieldaccesslayerlist as myAttributes;

class FieldAccessLayerListHooks extends \Anakeen\SmartElement
{
    use ExtendedControl;

    public $usefor = 'S';
    public $defDoctype = 'P';

    /**
     * Extend access
     * by default the three access are always set
     *
     * @var array
     */
    public $acls = array(
        "view",
        "edit",
        "delete"
    );

    protected $docid;

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
                "description" => $layerId ? "Layer $layerId" : "No$k"
            );

            $this->acls[$acl] = $acl;
        }
    }
}
