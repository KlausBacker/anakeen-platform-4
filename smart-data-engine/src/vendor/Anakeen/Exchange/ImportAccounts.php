<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Exchange;

use Anakeen\Core\Account;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\Utils\Xml;
use Anakeen\Exception;

class ImportAccounts
{
    protected $file;
    /**
     * @var \DOMDocument
     */
    protected $xml;
    /**
     * @var \DOMXPath
     */
    protected $xpath;
    /**
     * @var bool
     */
    protected $analyzeOnly = false;
    /**
     * @var bool commit only if no one error detected
     */
    protected $transactionMode = true;
    /**
     * @var array list all actions done
     */
    protected $report = array();

    protected $xsd = "vendor/Anakeen/Xml/accounts.xsd";

    protected $familiesXsd = array();

    protected $stopOnError = false;

    const ABORTORDER = "::ABORT::";

    protected $sessionKey = '';
    protected $nsPrefix;

    private $needSyncAccounts = false;
    /**
     * @var \Anakeen\Core\Account
     */
    private $workAccount = null;

    public function import()
    {
        $this->setSessionMessage(___("Load XML file", "fuserimport"));
        $this->xml = new \DOMDocument();
        $this->xml->load($this->file);
        $this->xml->preserveWhiteSpace = false;
        $this->xml->formatOutput = true;
        $this->xpath = new \DOMXPath($this->xml);

        try {
            if (!$this->hasXsdNamespace()) {
                $this->validateShema();
            }
            $this->workAccount = new \Anakeen\Core\Account();

            if ($this->transactionMode || $this->analyzeOnly) {
                DbManager::savePoint("AccountsExport");
                // Use a master lock because can be numerous accounts to import
                DbManager::setMasterLock(true);
            }
            $this->importRoles();
            $this->importGroups();
            $this->importUsers();

            if ($this->transactionMode && !$this->hasErrors() && !$this->analyzeOnly) {
                DbManager::commitPoint("AccountsExport");
                if ($this->needSyncAccounts) {
                    $g = new \Group();
                    // send order to recompute memberOf
                    $g->resetAccountMemberOf();
                }
            }
            $this->setSessionMessage(___("Import end", "fuserimport"));
        } catch (Exception $e) {
            if ($e->getDcpCode() === "ACCT0204") {
                $this->setSessionMessage(___("Import Aborted", "fuserimport"));
                $this->addToReport("", "userAbort", ___("Import Aborted", "fuserimport"), "", null);
            } elseif ($e->getDcpCode() === "ACCT0206") {
                $this->setSessionMessage(___("Import Aborted", "fuserimport"));
                $this->addToReport("", "stopOnError", "", "", null);
            } else {
                $this->setSessionMessage("::END::");
                throw $e;
            }
        }
        $this->setSessionMessage("::END::");
    }

    /**
     * @param string $file XML file path to import
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @param boolean $transactionMode
     */
    public function setTransactionMode($transactionMode)
    {
        $this->transactionMode = $transactionMode;
    }

    /**
     * @param string $sessionKey
     */
    public function setSessionKey($sessionKey)
    {
        $this->sessionKey = $sessionKey;
    }

    /**
     * @param boolean $stopOnError
     */
    public function setStopOnError($stopOnError)
    {
        $this->stopOnError = $stopOnError;
    }

    /**
     * Abort current import session
     */
    public function abortSession()
    {
        if ($this->sessionKey) {
            global $action;
            $action->session->register($this->sessionKey . "::ABORT", self::ABORTORDER);
        }
    }

    protected function setSessionMessage($text)
    {
        if ($this->sessionKey) {
            global $action;

            $action->session->register($this->sessionKey, $text);
            $msg = $action->session->read($this->sessionKey . "::ABORT");
            if ($msg === self::ABORTORDER) {
                $this->stopOnError = false;
                $action->session->register($this->sessionKey . "::ABORT", "CATCHED");

                throw new Exception("ACCT0204");
            }
        }
    }

    public function getSessionMessage()
    {
        if ($this->sessionKey) {
            global $action;
            return $action->session->read($this->sessionKey);
        }
        return null;
    }

    protected function libxmlDisplayError($error)
    {
        $return = "";
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "Warning $error->code: ";
                break;

            case LIBXML_ERR_ERROR:
                $return .= "Error $error->code: ";
                break;

            case LIBXML_ERR_FATAL:
                $return .= "Fatal Error $error->code: ";
                break;
        }
        $return .= trim($error->message);
        if ($error->file) {
            $return .= " in $error->file";
        }
        $return .= " on line $error->line\n";

        return $return;
    }

    protected function getXmlError()
    {
        $errors = libxml_get_errors();
        $humanError = "";
        foreach ($errors as $error) {
            $humanError .= $this->libxmlDisplayError($error);
        }
        libxml_clear_errors();
        return $humanError;
    }

    protected function hasXsdNamespace()
    {
        $xmlWithoutDocument = new \DOMDocument();

        $xmlWithoutDocument->load($this->file);
        $accounts = $xmlWithoutDocument->getElementsByTagNameNS(ExportAccounts::NSURI, "accounts");
        if ($accounts->length > 0) {
            $this->xpath->registerNamespace("ac", ExportAccounts::NSURI);
            $this->nsPrefix = Xml::getPrefix($xmlWithoutDocument, ExportAccounts::NSURI);
        }
        return $accounts->length > 0;
    }

    /**
     * Validate XML file with accounts schema
     * Document parts are validated by families schemas
     *
     * @throws Exception
     */
    protected function validateShema()
    {
        libxml_use_internal_errors(true);
        $xmlWithoutDocument = new \DOMDocument();

        $xmlWithoutDocument->load($this->file);
        $xmlWithoutDocument->preserveWhiteSpace = false;


        $xpath = new \DOMXPath($xmlWithoutDocument);
        // Delete document tag childs because if not a direct part of accounts xsd
        $documents = $xpath->query("//document");
        /**
         * @var \DOMElement $nodeDocument
         */
        foreach ($documents as $nodeDocument) {
            while ($nodeDocument->hasChildNodes()) {
                $nodeDocument->removeChild($nodeDocument->firstChild);
            }
        }

        if (!$xmlWithoutDocument->schemaValidate(DEFAULT_PUBDIR . "/" . $this->xsd)) {
            throw new Exception("ACCT0201", $this->getXmlError());
        }
        // Now validate document part
        $documents = $this->xpath->query("//document/*");
        $docDOM = new \DOMDocument();
        foreach ($documents as $nodeDocument) {
            $rootTag = $nodeDocument->tagName;
            $docDOM->loadXML($this->xml->saveXML($nodeDocument));
            if (!$docDOM->schemaValidateSource($this->getFamilyXsd($rootTag))) {
                throw new Exception("ACCT0201", $this->getXmlError());
            }
        }
        $this->xml->normalize();
    }

    /**
     * @param boolean $analyzeOnly
     */
    public function setAnalyzeOnly($analyzeOnly)
    {
        $this->analyzeOnly = $analyzeOnly;
    }

    /**
     * Return family xsd
     *
     * @param string $familyName family name
     *
     * @return string
     * @throws Exception
     */
    protected function getFamilyXsd($familyName)
    {
        if (!isset($this->familiesXsd[$familyName])) {
            /**
             * @var \Anakeen\Core\SmartStructure $family
             */

            $family = SEManager::getFamily($familyName);
            if (!$family || !$family->isAlive()) {
                throw new Exception("ACCT0202", $familyName);
            }
            $this->familiesXsd[$familyName] = $family->getXmlSchema();
        }

        return $this->familiesXsd[$familyName];
    }

    protected function importRoles()
    {
        $roles = $this->xpathQuery("/accounts/roles/role");
        $count = $roles->length;
        foreach ($roles as $k => $role) {
            $this->setSessionMessage(sprintf(___("Import role (%d/%d)", "fuserimport"), $k, $count));
            $this->importRole($role);
        }
    }

    protected function importGroups()
    {
        $groups = $this->xpathQuery("/accounts/groups/group");
        $count = $groups->length;
        foreach ($groups as $k => $group) {
            $this->setSessionMessage(sprintf(___("Import group (%d/%d)", "fuserimport"), $k, $count));
            $this->importGroup($group);
        }
    }

    protected function importUsers()
    {
        $users = $this->xpathQuery("/accounts/users/user");

        $count = $users->length;
        foreach ($users as $k => $user) {
            $this->setSessionMessage(sprintf(___("Import user (%d/%d)", "fuserimport"), $k, $count));
            $this->importUser($user);
        }
    }

    /**
     * @param \DOMElement $node
     *
     * @throws Exception
     */
    protected function importUser($node)
    {
        $values = array();
        $matchings = array(
            "string(@login)" => "login",
            "string(login)" => "login",
            "string(firstname)" => "firstname",
            "string(lastname)" => "lastname",
            "string(mail)" => "mail",
            "string(substitute/@ref)" => "substitute",
            "string(substitute/@reference)" => "substitute",
            "(password)[1]" => "password",
            "string(status/@activated)" => "status"
        );
        foreach ($matchings as $xmlTag => $varId) {
            /**
             * @var string|\DOMNodeList $nodeInfo
             */
            $nodeInfo = $this->xpathQuery($xmlTag, $node);
            if (is_a($nodeInfo, \DOMNodeList::class) && $nodeInfo->length === 0) {
                continue;
            }
            if ($nodeInfo) {
                switch ($varId) {
                    case "substitute":
                        $substituteLogin = $nodeInfo;

                        $subs = $this->getWorkingAccount();
                        $subs->setLoginName($substituteLogin);
                        if (!$subs->id) {
                            throw new Exception("ACCT0200", $substituteLogin, $values["login"]);
                        }

                        $values[$varId] = $subs->id;
                        break;

                    case "status":
                        $status = $nodeInfo;
                        if ($status === "true") {
                            $values[$varId] = "A";
                        } elseif ($status === "false") {
                            $values[$varId] = "D";
                        }

                        break;

                    case "password":
                        /** @var \DOMNodeList $nodeInfo */
                        $nodeElt = $nodeInfo->item(0);
                        /** @var \DOMElement $nodeElt */
                        $crypted = $nodeElt->getAttribute("crypted") === "true";
                        if ($crypted) {
                            if (substr($nodeElt->nodeValue, 0, 3) !== '$5$') {
                                $this->addToReport(
                                    $values["login"],
                                    "changePassword",
                                    "Not a SHA256 crypt",
                                    "",
                                    $nodeInfo
                                );
                            } else {
                                $values["password"] = $nodeElt->nodeValue;
                            }
                        } else {
                            $values["password_new"] = $nodeElt->nodeValue;
                            $this->addToReport($values["login"], "changePassword", "", "", $nodeElt);
                        }
                        break;

                    case "login":
                        if (mb_strtolower($nodeInfo) !== $nodeInfo) {
                            $this->addToReport(
                                $nodeInfo,
                                "users update",
                                "Login must not contains uppercase characters",
                                "",
                                $nodeInfo
                            );
                        }
                        $values[$varId] = $nodeInfo;
                        break;

                    default:
                        $values[$varId] = $nodeInfo;
                }
            }
        }
        $structureName = $this->xpathQuery("string(structure/@ref)", $node);
        if (!$structureName) {
            $structureName = "IUSER";
        }
        $account = $this->importAccount($node, "user", $structureName, $values);
        $logicalName = $this->xpathQuery("string(structure/@name)", $node);
        if ($logicalName) {
            $this->setLogicalName($logicalName, $account);
        }
        if (isset($subs) && $subs->id) {
            $account->setSubstitute($subs->id);
        }
        $this->importParent($node, "associatedRole", $account);
        $this->importParent($node, "parentGroup", $account);
    }

    /**
     * @param \DOMElement $node
     */
    protected function importGroup($node)
    {
        $values = array();
        $matchings = array(
            "string(@name)" => "login",
            "string(reference)" => "login",
            "string(displayName)" => "lastname"
        );
        foreach ($matchings as $xmlTag => $varId) {
            /**
             * @var \DOMNodeList $value
             */
            $value = $this->xpathQuery($xmlTag, $node);
            if ($value !== "") {
                $values[$varId] = $value;

                if ($varId === "login" && mb_strtolower($values[$varId]) !== $values[$varId]) {
                    $this->addToReport(
                        $values[$varId],
                        "group update",
                        "Reference must not contains uppercase characters",
                        "",
                        $value
                    );
                }
            }
        }
        $structureName = $this->xpathQuery("string(structure/@ref)", $node);
        if (!$structureName) {
            $structureName = "IGROUP";
        }
        $account = $this->importAccount($node, "group", $structureName, $values);
        $logicalName = $this->xpathQuery("string(structure/@name)", $node);
        if ($logicalName) {
            $this->setLogicalName($logicalName, $account);
        }
        $this->importParent($node, "associatedRole", $account);
        $this->importParent($node, "parentGroup", $account);
    }

    /**
     * @param \DOMElement $node
     * @param string $tagName "group" or "role"
     * @param \Anakeen\Core\Account $account
     */
    protected function importParent($node, $tagName, \Anakeen\Core\Account $account)
    {
        $listNode = $this->xpathQuery(sprintf("%ss", $tagName), $node);
        if ($listNode->length > 0) {
            $parents = $this->xpathQuery(sprintf("%ss/%s", $tagName, $tagName), $node);
            /**
             * @var \DOMElement $listNodeItem
             */
            $listNodeItem = $listNode->item(0);
            $reset = $listNodeItem->getAttribute("reset") === "true";

            if ($reset) {
                $type = "";
                if ($tagName === "parentGroup") {
                    $type = \Anakeen\Core\Account::GROUP_TYPE;
                } elseif ($tagName === "associatedRole") {
                    $type = \Anakeen\Core\Account::ROLE_TYPE;
                }
                $sql = sprintf(
                    "delete from groups using users where iduser=%d and users.id=groups.idgroup and users.accounttype= %s",
                    $account->id,
                    pg_escape_literal($type)
                );

                DbManager::query($sql);
                $this->addToReport($account->login, "reset$tagName", "", "", $listNodeItem);
                $this->needSyncAccounts = true;
                $account->updateMemberOf();
                $account->synchroAccountDocument();
            }
            $needUpdate = array();
            /**
             * @var \DOMElement $parentNode
             */
            foreach ($parents as $parentNode) {
                $parentLogin = $parentNode->getAttribute("reference");
                if (!$parentLogin) {
                    $parentLogin = $parentNode->getAttribute("ref");
                }
                $groupAccount = $this->getWorkingAccount();

                if ($groupAccount->setLoginName($parentLogin)) {
                    $group = new \Group();
                    $group->setSyncAccount(false); // No sync for each grou, sync done at the end
                    $group->idgroup = $groupAccount->id;
                    $group->iduser = $account->id;
                    $alreadyExists = ($group->preInsert() === "OK");

                    if (!$alreadyExists) {
                        $err = $group->add();
                        $this->needSyncAccounts = true;
                        $this->addToReport($account->login, "add$tagName", $err, $groupAccount->login, $parentNode);
                        if (!$err) {
                            $needUpdate[] = $groupAccount->fid;
                        }
                    } else {
                        $this->addToReport($account->login, "already$tagName", "", $groupAccount->login, $parentNode);
                    }
                } else {
                    $this->addToReport(
                        $account->login,
                        "add $tagName",
                        sprintf("$tagName reference %s not exists", $parentLogin),
                        "",
                        $parentNode
                    );
                }
            }
            if ($needUpdate) {
                $account->synchroAccountDocument();
                if ($tagName === "parentGroup") {
                    $dl = new \DocumentList();
                    if ($account->accounttype === \Anakeen\Core\Account::GROUP_TYPE) {
                        $needUpdate[] = $account->fid;
                    }
                    $dl->addDocumentIdentifiers($needUpdate);
                    /**
                     * @var \SmartStructure\Igroup $iGroup
                     */
                    foreach ($dl as $iGroup) {
                        $iGroup->refreshGroup();
                    }
                }
            }
        }
    }

    /**
     * @param \DOMElement $node
     */
    protected function importRole($node)
    {
        $values = array();
        $matchings = array(
            "string(@name)" => "login",
            "string(reference)" => "login",
            "string(displayName)" => "lastname"
        );
        foreach ($matchings as $xmlTag => $varId) {
            /**
             * @var \DOMNodeList $value
             */
            $value = $this->xpathQuery($xmlTag, $node);

            if ($value !== "") {
                $values[$varId] = $value;
                if ($varId === "login" && mb_strtolower($values[$varId]) !== $values[$varId]) {
                    $this->addToReport(
                        $values[$varId],
                        "role update",
                        "Reference must not contains uppercase characters",
                        "",
                        $value
                    );
                }
            }
        }
        $structureName = $this->xpathQuery("string(structure/@ref)", $node);
        if (!$structureName) {
            $structureName = "ROLE";
        }
        $role = $this->importAccount($node, "role", $structureName, $values);
        $logicalName = $this->xpathQuery("string(structure/@name)", $node);
        if ($logicalName) {
            $this->setLogicalName($logicalName, $role);
        }
    }


    protected function setLogicalName(string $logicalName, Account $account)
    {
        if ($logicalName) {
            $roleElt = SEManager::getDocument($account->fid);

            if ($roleElt->name !== $logicalName) {
                $err = $roleElt->setLogicalName($logicalName);
                if (!$err) {
                    $err = $roleElt->modify();
                }
                if ($err) {
                    throw new Exception($err);
                }
            }
        }
    }

    /**
     * @param \DOMElement $node node to import
     * @param string $tag node tag
     * @param string $defaultFamily default family for account in case of document tag not exists
     * @param array $values system values to update account
     *
     * @return \Anakeen\Core\Account
     * @throws Exception
     */
    protected function importAccount($node, $tag, $defaultFamily, array $values)
    {
        $newDocAccount = null;
        $err = '';
        /**
         * @var \DOMElement $documentNode
         */
        $documentNode = $this->xpath->query("document", $node)->item(0);
        $family = $defaultFamily;
        if ($documentNode) {
            $family = $documentNode->getAttribute("family");
        }
        $account = new \Anakeen\Core\Account();

        $msg = "";
        if ($values) {
            $msg = ___("Updated values", "dcp:import") . " :\n" . substr(print_r($values, true), 7, -2);
        }

        if (!$account->setLoginName($values["login"])) {
            // Already exists : update role

            if ($tag === "role") {
                $account->accounttype = \Anakeen\Core\Account::ROLE_TYPE;
            } elseif ($tag === "group") {
                $account->accounttype = \Anakeen\Core\Account::GROUP_TYPE;
            }
            // New account
            $famId = \Anakeen\Core\SEManager::getFamilyIdFromName($family);
            if (!$famId) {
                $err = "Not found family $family";
                $this->addToReport($values["login"], "documentCreation", $err, "", $documentNode);
                $famId = $defaultFamily;
            }
            $newDocAccount = SEManager::createDocument($famId);
            if ($newDocAccount) {
                $err = $newDocAccount->add();
                if (!$err) {
                    $account->fid = $newDocAccount->id;
                    $this->addToReport(
                        $values["login"],
                        "documentCreation",
                        "",
                        sprintf(___("Family %s", "fusersimport"), $newDocAccount->getFamilyDocument()->getTitle()),
                        $documentNode ? ($documentNode->cloneNode(false)) : null
                    );
                } else {
                    $this->addToReport($values["login"], "documentCreation", $err, "", $documentNode);
                }
            } else {
                $this->addToReport($values["login"], "documentCreation", "Cannot create $family", "", $documentNode);
            }
        }
        /**
         * @var \DOMElement $roleDocumentNode
         */
        $roleDocumentNode = $this->xpath->query("document/" . strtolower($family), $node)->item(0);
        if (!$err) {
            $account->affect($values);
            /**
             * @var \DOMElement $uNode
             */
            $uNode = $node->cloneNode(true);
            foreach (["document", "groups", "roles"] as $delTag) {
                $delNode = $uNode->getElementsByTagName($delTag);
                if ($delNode->length > 0) {
                    $uNode->removeChild($delNode->item(0));
                }
            }
            $msg = sprintf(_("Account Type \"%s\""), $tag) . "\n" . $msg;
            if ($account->id > 0) {
                $err = $account->modify();

                $this->addToReport($account->login, "updateAccount", $err, $msg, $uNode);
                if ($roleDocumentNode) {
                    $docName = $roleDocumentNode->getAttribute("name");
                    if ($docName) {
                        $docAccount = SEManager::getDocument($account->fid);
                        if ($docAccount && !$docAccount->name) {
                            $err = $docAccount->setLogicalName($docName);
                            if ($err) {
                                throw new Exception($err);
                            }
                        } else {
                            if ($docAccount->name != $docName) {
                                throw new Exception("ACCT0209", $docName, $docAccount);
                            }
                        }
                    }
                }
            } else {
                if (!$account->password) {
                    $account->password = "-";
                }
                $err = $account->add();
                $this->addToReport($account->login, "addAccount", $err, $msg, $uNode);
                // Connect document and account
                $newDocAccount->setValue(\SmartStructure\Fields\Role::us_whatid, $account->id);
                $newDocAccount->modify();
                if ($roleDocumentNode) {
                    $docName = $roleDocumentNode->getAttribute("name");
                    if ($docName) {
                        $err = $newDocAccount->setLogicalName($docName);
                        if ($err) {
                            throw new Exception($err);
                        }
                    }
                }
                $account->synchroAccountDocument();
            }
        }
        if ($roleDocumentNode) {
            $this->importXMLDocument($roleDocumentNode, $account);
        }

        return $account;
    }

    protected function importXMLDocument(\DOMElement $node, \Anakeen\Core\Account $account)
    {
        $node->setAttribute("id", $account->fid);
        $importXml = new ImportXml();
        $tmpFile = $this->getTmpFile();

        file_put_contents($tmpFile, $this->xml->saveXML($node));

        $importXml->importXmlFileDocument($tmpFile, $log);
        $msg = "";
        if ($log["values"]) {
            $msg = ___("Updated values", "dcp:import") . " :\n" . substr(print_r($log["values"], true), 7, -2);
        }

        $this->addToReport($account->login, "documentUpdate", $log["err"], $msg, $node);
    }

    protected function getTmpFile()
    {
        return \LibSystem::tempnam(null, "importXml");
    }

    protected function addToReport($login, $actionType, $error, $msg = "", $node = null)
    {
        switch ($actionType) {
            case "documentCreation":
                $msgType = ___("Document creation", "fusersimport");
                break;

            case "documentUpdate":
                $msgType = ___("Document update", "fusersimport");
                break;

            case "updateAccount":
                $msgType = ___("Update account", "fusersimport");
                break;

            case "addAccount":
                $msgType = ___("Create account", "fusersimport");
                break;

            case "addparentGroup":
                $msgType = ___("Add group reference", "fusersimport");
                break;

            case "addassociatedRole":
                $msgType = ___("Add role reference", "fusersimport");
                break;

            case "alreadyparentGroup":
                $msgType = ___("Group reference already added", "fusersimport");
                break;

            case "alreadyassociatedRole":
                $msgType = ___("Role reference already added", "fusersimport");
                break;

            case "resetgroup":
                $msgType = ___("Reset all group attachment", "fusersimport");
                break;

            case "resetrole":
                $msgType = ___("Reset all direct associated roles", "fusersimport");
                break;

            case "changePassword":
                $msgType = ___("New password", "fusersimport");
                break;

            case "stopOnError":
                $msgType = ___("Stopped on first error", "fusersimport");
                break;

            default:
                $msgType = $actionType;
        }

        if ($msg) {
            $msgType .= " : \n" . $msg;
        }

        $this->report[] = array(
            "login" => $login,
            "action" => $actionType,
            "error" => $error,
            "message" => $msgType,
            "node" => (is_a($node, \DOMNode::class)) ? $this->xml->saveXML($node) : $node
        );

        if ($error && $this->stopOnError) {
            throw new Exception("ACCT0206");
        }
    }

    public function getReport()
    {
        return $this->report;
    }

    public function hasErrors()
    {
        foreach ($this->report as $report) {
            if ($report["error"]) {
                return true;
            }
        }
        return false;
    }

    public function getErrors()
    {
        $errors = [];
        foreach ($this->report as $report) {
            if ($report["error"]) {
                $errors[] = $report["error"];
            }
        }
        return $errors;
    }

    /**
     * @return \Anakeen\Core\Account
     */
    private function getWorkingAccount()
    {
        foreach ($this->workAccount->fields as $field) {
            $this->workAccount->$field = "";
        }
        $this->workAccount->isset = false;
        return $this->workAccount;
    }

    private function xpathQuery($rule, $node = null)
    {
        if ($this->nsPrefix) {
            if (substr($rule, 0, 7) !== "string(" && $rule[0] !== '/' && $rule[0] !== '(') {
                $rule = "ac:" . $rule;
            }
            $rule = str_replace("/", "/ac:", $rule);
            $rule = str_replace("(", "(ac:", $rule);
            $rule = str_replace("ac:@", "@", $rule);
        }

        return $this->xpath->evaluate($rule, $node);
    }
}
