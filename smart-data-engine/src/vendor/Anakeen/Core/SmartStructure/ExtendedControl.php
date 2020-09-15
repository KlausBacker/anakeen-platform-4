<?php

namespace Anakeen\Core\SmartStructure;

use Anakeen\Core\AccountManager;
use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\Utils\Postgres;
use \SmartStructure\Fields\Fieldaccesslayerlist as myAttributes;

/**
 * Trait ExtendedControl
 * @package Anakeen\Core\SmartStructure
 * @mixin SmartElement
 */
trait ExtendedControl
{
    /**
     * @var SmartElement
     */
    protected $doc;
    protected $docid;

    protected $computedAcl = null;

    /**
     * Special control in case of dynamic controlled profil
     *
     * @param string $aclname
     * @param bool $strict
     *
     * @return string
     */
    public function control($aclname, $strict = false)
    {
        if (ContextManager::getCurrentUser()->id == \Anakeen\Core\Account::ADMIN_ID) {
            return ""; // no profil or admin
        }

        if (!in_array($aclname, array_keys($this->extendedAcls))) {
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

    /**
     * Affect attached smart element to the extended profil
     * @param SmartElement $doc
     */
    public function set(SmartElement $doc)
    {
        if ($this->doc && $this->docid !== $doc->id) {
            $this->computedAcl = null;
            $this->docid = $this->doc->id;
        }

        $this->doc = $doc;
    }

    /**
     * Check extAclName parameter
     *
     * @param string $extAclName
     * @return string|null
     * @throws \Anakeen\Database\Exception
     * @throws \Anakeen\Exception
     */
    protected function extendedControl(string $extAclName)
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
                    $oa = $this->doc->getAttribute($extendedAcl["attrid"]);
                    $rawValue = $this->doc->getRawValue($oa->id);

                    if ($rawValue) {
                        if ($oa->isMultiple() === false) {
                            $extuids = [$rawValue];
                        } else {
                            $extuids = Postgres::stringToFlatArray($rawValue);
                        }
                        foreach ($extuids as $extuid) {
                            if ($extuid) {
                                $this->computedAcl[$extendedAcl["acl"]][] = AccountManager::getIdFromSEId($extuid);
                            }
                        }
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
        return sprintf(___("No privilege \"%s\" for %d ( \"%s\" doesn't exist in the profil )", "sde"), $extAclName, $this->id, $extAclName);
    }

    /**
     * Call original control method
     * @param string $aclname
     * @param bool $strict
     * @return string
     */
    protected function originalControl($aclname, $strict = false)
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        return SmartElement::control($aclname, $strict);
    }
}
