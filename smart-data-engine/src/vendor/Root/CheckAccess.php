<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * Checking application accesses
 *
 * @class CheckAccess
 * @brief Check application accesses when importing definition
 * @see   ErrorCodeACCS
 */
class CheckAccess extends CheckData
{
    // @var string Namespace
    protected $ns;

    /**
     * user identifier
     *
     * @var string
     */
    private $userId = '';

    /**
     * acl list
     *
     * @var array
     */
    private $acls = array();

    /**
     * @param array $data
     *
     *
     * @param null  $extra
     * @return CheckAccess
     */
    public function check(array $data, &$extra = null)
    {
        $this->userId = $data[1];
        $this->ns = $data[2];

        for ($i = 3; $i < count($data); $i++) {
            if (!empty($data[$i])) {
                if ($data[$i][0] == '-') {
                    $this->acls[] = substr($data[$i], 1);
                } else {
                    $this->acls[] = $data[$i];
                }
            }
        }

        if (!$this->hasErrors()) {
            $this->checkUserExists();
            $this->checkAclsExists();
        }

        return $this;
    }



    private function checkUserExists()
    {
        if ($this->userId) {
            $findUser = false;
            if (ctype_digit($this->userId)) {
                $findUser = \Anakeen\Core\Account::getDisplayName($this->userId);
            } else {
                // search document
                $tu = \Anakeen\Core\SEManager::getRawDocument($this->userId);
                if ($tu) {
                    $findUser = ($tu["us_whatid"] != '');
                }
            }
            if ($findUser === false) {
                $this->addError(ErrorCode::getError('ACCS0003', $this->userId));
            }
        } else {
            $this->addError(ErrorCode::getError('ACCS0007'));
        }
    }

    private function checkAclsExists()
    {
        $oAcl = new Acl();
        foreach ($this->acls as $acl) {
            if ($this->checkSyntax($acl)) {
                $aclKey=$this->ns.'::'.$acl;
                if (!$oAcl->set($aclKey)) {
                    $this->addError(ErrorCode::getError('ACCS0002', $aclKey));
                }
            } else {
                $this->addError(ErrorCode::getError('ACCS0004', $acl));
            }
        }
    }

    /**
     * @param string $acl
     *
     * @return bool
     */
    private function checkSyntax($acl)
    {
        if (preg_match("/^-?[A-Z_0-9_:-]{1,63}$/i", $acl)) {
            return true;
        }
        return false;
    }
}
