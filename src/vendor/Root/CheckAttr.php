<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * Checking application accesses
 * @class CheckAttr
 * @brief Check application accesses when importing definition
 * @see   ErrorCodeATTR
 */
class CheckAttr extends CheckData
{
    /**
     * Type of import line : 'ATTR', 'PARAM', 'MODATTR', etc.
     * @var string
     */
    private $lineType = '';
    /**
     * @var StructAttribute
     */
    private $structAttr = null;
    private $types = array(
        "text",
        "longtext",
        "image",
        "file",
        "frame",
        "enum",
        "date",
        "integer",
        "int",
        "double",
        "money",
        "password",
        "ifile",
        "xml",
        "thesaurus",
        "tab",
        "time",
        "timestamp",
        "array",
        "color",
        "menu",
        "docid",
        "htmltext",
        "account"
    );

    private $noValueTypes = array(
        "frame",
        "tab",
        "menu",
        "action",
        "array"
    );
    private $accessibilities = array(
        'Read',
        'Write',
        'ReadWrite',
        'None'
    );

    private $yesno = array(
        'y',
        'n'
    );
    private $postgreSqlWords = array(
        'all',
        'analyse',
        'analyze',
        'and',
        'any',
        'array',
        'as',
        'asc',
        'asymmetric',
        'both',
        'case',
        'cast',
        'check',
        'collate',
        'column',
        'constraint',
        'create',
        'current_date',
        'current_role',
        'current_time',
        'current_timestamp',
        'current_user',
        'default',
        'deferrable',
        'desc',
        'distinct',
        'do',
        'else',
        'end',
        'except',
        'false',
        'for',
        'foreign',
        'from',
        'grant',
        'group',
        'having',
        'in',
        'initially',
        'intersect',
        'into',
        'leading',
        'limit',
        'localtime',
        'localtimestamp',
        'new',
        'not',
        'null',
        'off',
        'offset',
        'old',
        'on',
        'only',
        'or',
        'order',
        'placing',
        'primary',
        'references',
        'returning',
        'select',
        'session_user',
        'some',
        'symmetric',
        'table',
        'then',
        'to',
        'trailing',
        'true',
        'union',
        'unique',
        'user',
        'using',
        'when',
        'where',
        'with'
    );
    /**
     * the attribute identifier
     * @var string
     */
    private $attrid;
    /**
     * analyze an attribute structure
     * @param array $data
     * @param mixed $extra
     * @return CheckAttr
     */
    /**
     * @var \Anakeen\Core\Internal\SmartElement
     */
    private $doc = null;
    /**
     * true if check MODATTR
     * @var bool
     */
    private $isModAttr = false;

    public function check(array $data, &$extra = null)
    {
        $this->lineType = $data[0];
        $this->structAttr = new StructAttribute($data);
        $this->doc = $extra;
        $this->attrid = strtolower($this->structAttr->id);
        $this->isModAttr = (strtolower($data[0]) == "modattr");
        $this->checkId();
        $this->checkSet();
        $this->checkType();
        $this->checkOrder();
        $this->checkFieldAccess();
        $this->checkIsAbstract();
        $this->checkIsTitle();
        $this->checkIsNeeded();
        if ($this->checkPhpFile()) {
            $this->checkPhpFunctionOrMethod();
            $this->checkEnum();
        }
        $this->checkPhpConstraint();
        $this->checkOptions();
        return $this;
    }

    /**
     * test syntax for document's identifier
     * @return void
     */
    private function checkId()
    {
        if (empty($this->attrid)) {
            $this->addError(ErrorCode::getError('ATTR0102'));
        } elseif (!$this->checkAttrSyntax($this->attrid)) {
            $this->addError(ErrorCode::getError('ATTR0100', $this->attrid));
        } else {
            if (in_array($this->attrid, $this->postgreSqlWords)) {
                $this->addError(ErrorCode::getError('ATTR0101', $this->attrid));
            } else {
                $doc = new \Anakeen\Core\Internal\SmartElement();
                if (in_array($this->attrid, $doc->fields)) {
                    $this->addError(ErrorCode::getError('ATTR0103', $this->attrid));
                }
            }
        }
    }

    /**
     * test syntax for document's identifier
     * @return void
     */
    private function checkSet()
    {
        $setId = strtolower($this->structAttr->setid);
        if ($setId && ($this->attrid == $setId)) {
            $this->addError(ErrorCode::getError('ATTR0202', $setId, $this->attrid));
        }
        if ($this->isNodeNeedSet()) {
            if (empty($setId)) {
                $this->addError(ErrorCode::getError('ATTR0201', $this->attrid));
            } elseif (!$this->checkAttrSyntax($setId)) {
                $this->addError(ErrorCode::getError('ATTR0200', $setId, $this->attrid));
            }
        } elseif ($setId) {
            if (!$this->checkAttrSyntax($setId)) {
                $this->addError(ErrorCode::getError('ATTR0200', $setId, $this->attrid));
            } else {
                if ($this->getType() == 'tab') {
                    $this->addError(ErrorCode::getError('ATTR0206', $setId, $this->attrid));
                }
            }
        }
    }

    /**
     * test attribute type is a recognized type
     * @return void
     */
    private function checkType()
    {
        $type = $this->structAttr->type;
        if (!$type) {
            if (!$this->isModAttr) {
                $this->addError(ErrorCode::getError('ATTR0600', $this->attrid));
            } else {
                $this->checkModAttrType();
            }
        } elseif (!in_array($type, $this->types)) {
            $basicType = $this->getType();
            if (!$basicType) {
                $this->addError(ErrorCode::getError('ATTR0602', $type, $this->attrid));
            } elseif (!in_array($basicType, $this->types)) {
                $this->addError(ErrorCode::getError('ATTR0601', $basicType, $this->attrid, implode(', ', $this->types)));
            } else {
                if ($this->isModAttr) {
                    $this->checkModAttrType();
                }
            }
        } else {
            $format = $this->getFormat();
            if ($format) {
                if (in_array($type, array(
                    'text',
                    'int',
                    'double',
                    'money',
                    'longtext'
                ))) {
                    $a = @sprintf($format, 123);
                    if ($a === false) {
                        $this->addError(ErrorCode::getError('ATTR0603', $format, $this->attrid));
                    }
                }
            }

            if ($this->isModAttr) {
                $this->checkModAttrType();
            }
        }
    }

    /**
     * Verify id modattr of enum if compatible whith origin
     */
    private function checkModAttrType()
    {
        $type = $this->structAttr->type;

        $basicType = $this->getType();
        $originAttr = $this->getOriginAttr($this->attrid, $this->doc->fromid, $this->doc->id);
        if ($originAttr) {
            $originType = $originAttr["type"];
            $basicOriginType = $this->getType($originType);
            if ($basicOriginType == "enum") {
                if (trim($this->structAttr->phpfunc) != '') {
                    $this->addError(ErrorCode::getError('ATTR0606', $this->attrid, $this->doc->name));
                }
            }
            if (!$type) {
                return;
            }
            if (!$this->isCompatibleModType($basicOriginType, $basicType)) {
                $this->addError(ErrorCode::getError('ATTR0604', $this->attrid, $this->doc->name, $type, $originType));
            }
        } else {
            $this->addError(ErrorCode::getError('ATTR0605', $this->attrid, $this->doc->name));
        }
    }

    private function getOriginAttr($attrid, $fromid, $docid)
    {
        $sqlPattern = <<< 'SQL'
    select * from docattr where docid in (
    with recursive adocfam(id, fromid, famname) as (
     select  docfam.id, docfam.fromid, docfam.name as famname from docfam where docfam.id=%d or docfam.id=%d
       union
     select  docfam.id, docfam.fromid, docfam.name as famname  from docfam, adocfam where  docfam.id = adocfam.fromid
    ) select id from adocfam
    ) and id='%s' order by docid desc;
SQL;

        $attrid = pg_escape_string($attrid);
        $sql = sprintf($sqlPattern, $docid, $fromid, $attrid);

        \Anakeen\Core\DbManager::query($sql, $r);
        if (count($r) > 0) {
            return $r[0];
        }
        return null;
    }

    private function isCompatibleModType($typeA, $typeB)
    {
        if ($typeA == $typeB) {
            return true;
        }
        $compatibleText = array(
            'text',
            'htmltext',
            'longtext'
        );
        if (in_array($typeA, $compatibleText) && in_array($typeB, $compatibleText)) {
            return true;
        }
        $compatibleNumbers = array(
            'double',
            'money'
        );
        if (in_array($typeA, $compatibleNumbers) && in_array($typeB, $compatibleNumbers)) {
            return true;
        }
        $compatibleRelation = array(
            'docid',
            'account',
            'thesaurus'
        );
        if (in_array($typeA, $compatibleRelation) && in_array($typeB, $compatibleRelation)) {
            return true;
        }
        return false;
    }

    /**
     * test syntax order
     * must be an integer
     * @return void
     */
    private function checkOrder()
    {
        $order = $this->structAttr->order;

        if ($this->isNodeNeedOrder() && empty($order)) {
            $this->addError(ErrorCode::getError('ATTR0702', $this->attrid));
        } elseif ($order) {
            if (!is_numeric($order)) {
                if ($order === \Dcp\FamilyAbsoluteOrder::firstOrder || $order === \Dcp\FamilyAbsoluteOrder::autoOrder) {
                    return;
                }
                if (!$this->checkAttrSyntax($order)) {
                    $this->addError(ErrorCode::getError('ATTR0700', $order, $this->attrid));
                }
            }
        }
    }


    private function checkFieldAccess()
    {
        $acs = $this->structAttr->access;
        if (empty($acs)) {
            if (!$this->isModAttr) {
                $this->addError(ErrorCode::getError('ATTR0800', $this->attrid));
            }
        } elseif (!in_array($acs, $this->accessibilities)) {
            $this->addError(ErrorCode::getError('ATTR0801', $acs, $this->attrid, implode(',', $this->accessibilities)));
        }
    }

    private function checkIsAbstract()
    {
        $isAbstract = strtolower($this->structAttr->isabstract);
        if ($isAbstract) {
            if (!in_array($isAbstract, $this->yesno)) {
                $this->addError(ErrorCode::getError('ATTR0500', $isAbstract, $this->attrid));
            } elseif ($isAbstract == 'y' && (!$this->isNodeHasValue())) {
                $this->addError(ErrorCode::getError('ATTR0501', $this->attrid));
            }
        }
    }

    private function checkIsTitle()
    {
        $isTitle = strtolower($this->structAttr->istitle);
        if ($isTitle) {
            if (!in_array($isTitle, $this->yesno)) {
                $this->addError(ErrorCode::getError('ATTR0400', $isTitle, $this->attrid));
            } elseif ($isTitle == 'y' && (!$this->isNodeHasValue())) {
                $this->addError(ErrorCode::getError('ATTR0401', $this->attrid));
            }
        }
    }

    private function checkIsNeeded()
    {
        $isNeeded = strtolower($this->structAttr->isneeded);
        if ($isNeeded) {
            if (!in_array($isNeeded, $this->yesno)) {
                $this->addError(ErrorCode::getError('ATTR0900', $isNeeded, $this->attrid));
            } elseif ($isNeeded == 'y' && (!$this->isNodeHasValue())) {
                $this->addError(ErrorCode::getError('ATTR0901', $this->attrid));
            }
        }
    }

    /**
     * @return bool return false if some error detected
     */
    private function checkPhpFile()
    {
        $goodFile = true;
        $phpFile = trim($this->structAttr->phpfile);
        if ($phpFile && $phpFile != '-' && ($this->getType() != "action")) {
            $phpFile = sprintf(DEFAULT_PUBDIR . "/EXTERNALS/%s", $phpFile);
            if (!file_exists($phpFile)) {
                $this->addError(ErrorCode::getError('ATTR1100', $phpFile, $this->attrid));
                $goodFile = false;
            } else {
                $realPhpFile = realpath($phpFile);
                if (CheckClass::phpLintFile($realPhpFile, $output) === false) {
                    $this->addError(ErrorCode::getError('ATTR1101', $phpFile, $this->attrid, implode("\n", $output)));
                    $goodFile = false;
                } else {
                    require_once $phpFile;
                }
            }
        }
        return $goodFile;
    }

    private function checkPhpFunctionOrMethod()
    {
        $phpFunc = trim($this->structAttr->phpfunc);
        $phpFile = trim($this->structAttr->phpfile);
        $type = $this->getType();
        if ($phpFunc && $phpFunc != '-' && ($type != "action") && ($type != "enum")) {
            if ($phpFile && $phpFile != '-') {
                // parse function for input help
                $this->checkPhpFunction();
            } else {
                // parse method for computed attribute
                $this->checkPhpMethod();
            }
        }
    }

    private function checkEnum()
    {
        $phpFunc = $this->structAttr->phpfunc;
        $phpFile = trim($this->structAttr->phpfile);
        $type = $this->getType();
        if ((!$phpFile || $phpFile == '-') && $phpFunc && ($type == "enum")) {
            // parse static enum
            $enums = str_replace(array(
                "\\.",
                "\\,"
            ), '-', $phpFunc); // to replace dot & comma separators
            $topt = explode(",", $enums);
            foreach ($topt as $opt) {
                if (strpos($opt, '|') === false) {
                    $enumKey = $opt;
                    $enumLabel = null;
                } else {
                    list($enumKey, $enumLabel) = explode("|", $opt, 2);
                }
                if ($enumKey === '') {
                    $this->addError(ErrorCode::getError('ATTR1272', $opt, $this->attrid));
                } elseif (!preg_match('/^[\x20-\x7E]+$/', $enumKey)) {
                    $this->addError(ErrorCode::getError('ATTR1271', $opt, $this->attrid));
                } elseif ($enumLabel === null) {
                    $this->addError(ErrorCode::getError('ATTR1270', $opt, $this->attrid));
                }
            }
        }
    }

    private function checkPhpFunction()
    {
        $phpFunc = trim($this->structAttr->phpfunc);
        $phpFile = trim($this->structAttr->phpfile);
        $type = $this->getType();
        if ($phpFunc && $phpFunc != '-' && ($type != "action")) {
            if ($phpFile && $phpFile != '-') {
                // parse function for input help
                $oParse = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyFunction();
                $strucFunc = $oParse->parse($phpFunc, ($type == 'enum'));
                if ($strucFunc->getError()) {
                    $this->addError(ErrorCode::getError('ATTR1200', $this->attrid, $strucFunc->getError()));
                } else {
                    $phpFuncName = $strucFunc->functionName;
                    try {
                        $refFunc = new ReflectionFunction($phpFuncName);
                        if ($refFunc->isInternal()) {
                            $this->addError(ErrorCode::getError('ATTR1209', $phpFuncName));
                        } else {
                            $targetFile = $refFunc->getFileName();
                            $realPhpFile = realpath(sprintf(DEFAULT_PUBDIR . "/EXTERNALS/%s", $phpFile));
                            if ($targetFile != $realPhpFile) {
                                if (!$oParse->appName) {
                                    $this->addError(ErrorCode::getError('ATTR1210', $phpFuncName, $realPhpFile));
                                }
                            } else {
                                $numArgs = $refFunc->getNumberOfRequiredParameters();
                                if ($numArgs > count($strucFunc->inputs)) {
                                    $this->addError(ErrorCode::getError('ATTR1211', $phpFuncName, $numArgs));
                                }
                            }
                        }
                    } catch (Exception $e) {
                        \Anakeen\Core\LogException::writeLog($e);
                        $this->addError(ErrorCode::getError('ATTR1203', $phpFuncName));
                    }
                }
            }
        }
    }

    private function checkPhpMethod()
    {
        $phpFunc = trim($this->structAttr->phpfunc);
        $type = $this->getType();

        if ($this->isModAttr && (!$type)) {
            return;
        } // cannot really test if has not type
        $oParse = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod();
        $strucFunc = $oParse->parse($phpFunc, ($type == 'enum'));
        if ($strucFunc->getError()) {
            $this->addError(ErrorCode::getError('ATTR1250', $this->attrid, $strucFunc->getError()));
        } else {
            // validity of method call cannot be tested here
            // it is tested in checkEnd
            if ($this->lineType == 'PARAM' && isset($strucFunc->outputs) && count($strucFunc->outputs) > 0) {
                $this->addError(ErrorCode::getError('ATTR0211', $this->attrid));
            }
        }
    }

    private function checkPhpConstraint()
    {
        $constraint = trim($this->structAttr->constraint);
        if ($constraint) {
            if ($this->isModAttr && $constraint == '-') {
                return;
            }
            $oParse = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod();
            $strucFunc = $oParse->parse($constraint, true);
            if ($strucFunc->getError()) {
                $this->addError(ErrorCode::getError('ATTR1400', $this->attrid, $strucFunc->getError()));
            }
        }
    }

    private function checkOptions()
    {
        $options = trim($this->structAttr->options);
        if ($options) {
            $topt = explode("|", $options);
            foreach ($topt as $opt) {
                if (strpos($opt, '=') === false) {
                    $optName = $opt;
                    $optValue = null;
                } else {
                    list($optName, $optValue) = explode("=", $opt, 2);
                }
                if (!preg_match('/^[a-z_-]{1,63}$/', $optName)) {
                    $this->addError(ErrorCode::getError('ATTR1500', $optName, $this->attrid));
                } elseif ($optValue === null) {
                    $this->addError(ErrorCode::getError('ATTR1501', $optName, $this->attrid));
                }
            }
        }
    }

    /**
     * @param string $attrid
     * @return bool
     */
    public static function checkAttrSyntax($attrid)
    {
        if (preg_match("/^[A-Z_0-9]{1,63}$/i", $attrid)) {
            return true;
        }
        return false;
    }

    private function getType($type = null)
    {
        if ($type === null) {
            $type = trim($this->structAttr->rawType);
        }
        $rtype = '';
        if (preg_match('/^([a-z]+)\(["\'].+["\']\)$/i', $type, $reg)) {
            $rtype = $reg[1];
        } elseif (preg_match('/^([a-z]+)$/i', $type, $reg)) {
            $rtype = $reg[1];
        }
        return $rtype;
    }

    private function getFormat()
    {
        $type = trim($this->structAttr->rawType);
        $rformat = '';

        if (preg_match('/^([a-z]+)\(["\'](.+)["\']\)$/i', $type, $reg)) {
            $rformat = $reg[2];
        }
        return $rformat;
    }

    private function isNodeNeedSet()
    {
        if ($this->isModAttr) {
            return false;
        }
        $type = $this->getType();
        return (($type != "tab") && ($type != "frame") && ($type != "menu") && ($type != "action"));
    }

    private function isNodeNeedOrder()
    {
        if ($this->isModAttr) {
            return false;
        }
        $type = $this->getType();
        return (($type != "tab") && ($type != "frame"));
    }

    private function isNodeHasValue()
    {
        $type = $this->getType();
        return (!in_array($type, $this->noValueTypes));
    }
}
