<?php

namespace Anakeen\SmartStructures\FieldAccessLayerList;

use Anakeen\Core\AccountManager;
use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\SmartElement;
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
    protected $originalAcl;

    public $usefor = 'SW';
    public $defDoctype = 'P';
    /**
     * @var SmartElement
     */
    protected $doc;

    protected $docid;
    protected $computedAcl = null;

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

    public function set(SmartElement $doc)
    {
        if ($this->doc && $this->docid !== $doc->id) {
            $this->computedAcl = null;
            $this->docid = $this->doc->id;
        }

        $this->doc = $doc;
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
        if (ContextManager::getCurrentUser()->id == \Anakeen\Core\Account::ADMIN_ID) {
            return ""; // no profil or admin
        }
        if (in_array($aclname, $this->originalAcl)) {
            // normal case
            return $this->originalControl($aclname, $strict);
        }

        if ($this->getRawValue(myAttributes::dpdoc_famid) > 0) {
            if ($this->doc) {
                // special control for dynamic users
                $err = $this->extendedControl($aclname);
                if ($err !== null) {
                    return $err;
                } else {
                    return "";
                }
            }
        }
        $err = $this->originalControl($aclname, $strict);
        return $err;
    }

    protected function extendedControl($extAclName)
    {
        if ($this->computedAcl === null) {
            $sql = sprintf(
                "select userid, acl, vgroup.id as attrid from docpermext left join vgroup on docpermext.userid = vgroup.num where docpermext.docid=%d",
                $this->id
            );
            DbManager::query($sql, $extendedAcls);

            $this->computedAcl = [];
            $this->doc->disableAccessControl();
            foreach ($extendedAcls as $extendedAcl) {
                if (!empty($extendedAcl["attrid"])) {
                    $extuid = $this->doc->getRawValue($extendedAcl["attrid"]);
                    if ($extuid) {
                        $this->computedAcl[$extendedAcl["acl"]][] = AccountManager::getIdFromSEId($extuid);
                    }
                } else {
                    $this->computedAcl[$extendedAcl["acl"]][] = $extendedAcl["userid"];
                }
            }
            $this->doc->restoreAccessControl();
        }
        if (isset($this->computedAcl[$extAclName])) {
            $memberOf = ContextManager::getCurrentUser()->getMemberOf();
            $memberOf[] = ContextManager::getCurrentUser()->id;
            if (array_intersect($memberOf, $this->computedAcl[$extAclName])) {
                return "";
            } else {
                return sprintf(___("No privilege \"%s\" for %d", "sde"), $extAclName, $this->id);
            }
        }
        return null;
    }

    protected function originalControl($aclname, $strict = false)
    {
        return SmartElement::control($aclname, $strict);
    }

    protected function setAcls()
    {
        $this->extendedAcls = array();
        $layerAcls = $this->getMultipleRawValues(myAttributes::fall_aclname);
        $tl = $this->getMultipleRawValues(myAttributes::fall_layer);

        $this->originalAcl = $this->acls;
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
