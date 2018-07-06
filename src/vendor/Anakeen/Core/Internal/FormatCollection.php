<?php
/**
 * Document list class
 *
 */

namespace Anakeen\Core\Internal;

use \Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\FieldAccessManager;

/**
 * Format document list to be easily used in
 * @class FormatCollection
 * @code
 *      $s = new \SearchDoc(self::$dbaccess, $this->famName);
 * $s->setObjectReturn();
 * $dl = $s->search()->getDocumentList();
 * $fc = new \Anakeen\Core\Internal\FormatCollection();
 * $fc->useCollection($dl);
 * $fc->addProperty($fc::propName);
 * $fc->addAttribute(('tst_x'));
 * $fc->setNc($nc);
 * $r = $fc->render();
 * @endcode
 */
class FormatCollection
{
    const noAccessText = "N.C.";
    /**
     * @var \DocumentList $dl
     */
    protected $dl = null;
    public $debug = array();
    protected $propsKeys = array();
    protected $fmtProps = array(
        self::propId,
        self::title
    );
    protected $fmtAttrs = array();
    protected $ncAttribute = '';

    protected $noAccessText = self::noAccessText;
    /**
     * @var int family icon size in pixel
     */
    public $familyIconSize = 24;
    /**
     * @var int relation icon size in pixel
     */
    public $relationIconSize = 14;
    /**
     * @var int mime type icon size in pixel
     */
    public $mimeTypeIconSize = 14;
    /**
     * @var int thumbnail width in pixel
     */
    public $imageThumbnailSize = 48;
    /**
     * @var string text in case of no access in relation target
     */
    public $relationNoAccessText = "";
    /**
     * @var bool if true set showempty option in displayValue when value is empty
     */
    public $useShowEmptyOption = true;

    protected $attributeGrants = array();

    protected $decimalSeparator = ',';

    protected $dateStyle = Format\DateAttributeValue::defaultStyle;

    protected $propDateStyle = null;

    protected $stripHtmlTag = false;

    protected $longtextMultipleBrToCr = "\n";
    /**
     * Verify attribute visibility "I"
     * @var bool
     */
    protected $verifyAttributeAccess = true;
    /**
     * @var \Closure
     */
    protected $hookStatus = null;
    /**
     * @var bool
     */
    protected $singleDocument = false;
    /**
     * @var \Closure
     */
    protected $renderAttributeHook = null;
    /**
     * @var \Closure
     */
    protected $renderDocumentHook = null;
    /**
     * @var \Closure
     */
    protected $renderPropertyHook = null;

    const title = "title";
    /**
     * name property
     */
    const propName = "name";
    /**
     * id property
     */
    const propId = "id";
    /**
     * icon property
     */
    const propIcon = "icon";
    /**
     * locked property
     */
    const propLocked = "locked";
    /**
     * initid property
     */
    const propInitid = "initid";
    /**
     * revision property
     */
    const propRevision = "revision";
    /**
     * url access to document
     */
    const propUrl = "url";
    /**
     * family information
     */
    const propFamily = "family";
    /**
     * Last access date
     */
    const propLastAccessDate = "lastAccessDate";
    /**
     * Last modification date
     */
    const propLastModificationDate = "lastModificationDate";
    /**
     * Some informations about revision
     */
    const propRevisionData = "revisionData";
    /**
     * View Controller information
     */
    const propViewController = "viewController";
    /**
     * Workflow information
     */
    const propWorkflow = "workflow";
    /**
     * allocated information
     */
    const propAffected = "affected";
    /**
     * status information : alive, deleted, fixed
     */
    const propStatus = "status";
    /**
     * note information
     */
    const propNote = "note";
    /**
     * usefor information
     */
    const propUsage = "usage";
    /**
     * doctype information
     */
    const propType = "type";
    /**
     * Applictaion Tags list
     * @see \Anakeen\Core\Internal\SmartElement::addAtag()
     */
    const propTags = "tags";
    /**
     * Security information (lock, profil)
     */
    const propSecurity = "security";
    /**
     * Creation date (of revision 0)
     */
    const propCreationDate = "creationDate";
    /**
     * Creation user (of revision 0)
     */
    const propCreatedBy = "createdBy";
    /**
     * state property
     */
    const propState = "state";
    /**
     * state property
     */
    const propDProfid = "dprofid";
    /**
     * revision date
     */
    const revdate = "revdate";
    /**
     * access date
     */
    const adate = "adate";
    /**
     * creation date
     */
    const cdate = "cdate";

    public function __construct($doc = null)
    {
        $this->propsKeys = self::getAvailableProperties();
        if ($doc !== null) {
            $this->dl = array(
                $doc
            );
            $this->singleDocument = true;
        }
    }

    public static function getAvailableProperties()
    {
        $keys = array_keys(\Anakeen\Core\Internal\SmartElement::$infofields);
        $keys[] = self::propFamily;
        $keys[] = self::propLastAccessDate;
        $keys[] = self::propLastModificationDate;
        $keys[] = self::propCreationDate;
        $keys[] = self::propCreatedBy;
        $keys[] = self::propRevisionData;
        $keys[] = self::propViewController;
        $keys[] = self::propWorkflow;
        $keys[] = self::propTags;
        $keys[] = self::propSecurity;
        $keys[] = self::propAffected;
        $keys[] = self::propStatus;
        $keys[] = self::propNote;
        $keys[] = self::propUsage;
        $keys[] = self::propType;
        return $keys;
    }

    /**
     * @param string $propDateStyle
     * @return $this
     * @throws \Dcp\Fmtc\Exception
     */
    public function setPropDateStyle($propDateStyle)
    {
        if (!in_array($propDateStyle, array(
            Format\DateAttributeValue::defaultStyle,
            Format\DateAttributeValue::frenchStyle,
            Format\DateAttributeValue::isoWTStyle,
            Format\DateAttributeValue::isoStyle
        ))) {
            throw new \Dcp\Fmtc\Exception("FMTC0003", $propDateStyle);
        }
        $this->propDateStyle = $propDateStyle;
        return $this;
    }

    /**
     * If false, attribute with "I" visibility are  returned
     * @param boolean $verifyAttributeAccess
     */
    public function setVerifyAttributeAccess($verifyAttributeAccess)
    {
        $this->verifyAttributeAccess = $verifyAttributeAccess;
    }

    /**
     * Use when cannot access attribut value
     * Due to visibility "I"
     * @param string $noAccessText
     */
    public function setNoAccessText($noAccessText)
    {
        $this->noAccessText = $noAccessText;
    }

    /**
     * default value returned when attribute not found in document
     * @param $s
     * @return \Anakeen\Core\Internal\FormatCollection
     */
    public function setNc($s)
    {
        $this->ncAttribute = $s;
        return $this;
    }

    /**
     * document list to format
     * @param \DocumentList $l
     * @return \Anakeen\Core\Internal\FormatCollection
     */
    public function useCollection(\DocumentList & $l)
    {
        $this->dl = $l;
        return $this;
    }

    /**
     * set decimal character character to use for double and money type
     * @param string $s a character to separate decimal part from integer part
     * @return \Anakeen\Core\Internal\FormatCollection
     */
    public function setDecimalSeparator($s)
    {
        $this->decimalSeparator = $s;
        return $this;
    }

    /**
     * display Value of htmltext content value without tags
     * @param bool $strip
     * @return \Anakeen\Core\Internal\FormatCollection
     */
    public function stripHtmlTags($strip = true)
    {
        $this->stripHtmlTag = $strip;
        return $this;
    }

    /**
     * set date style
     * possible values are :DateAttributeValue::defaultStyle,DateAttributeValue::frenchStyle,DateAttributeValue::isoWTStyle,DateAttributeValue::isoStyle
     * @param string $style
     * @return $this
     * @throws \Dcp\Fmtc\Exception
     */
    public function setDateStyle($style)
    {
        if (!in_array($style, array(
            Format\DateAttributeValue::defaultStyle,
            Format\DateAttributeValue::frenchStyle,
            Format\DateAttributeValue::isoWTStyle,
            Format\DateAttributeValue::isoStyle
        ))) {
            throw new \Dcp\Fmtc\Exception("FMTC0003", $style);
        }
        $this->dateStyle = $style;
        return $this;
    }

    /**
     * add a property to render
     * by default id and title are rendered
     * @param string $props
     * @throws \Dcp\Fmtc\Exception
     * @return \Anakeen\Core\Internal\FormatCollection
     */
    public function addProperty($props)
    {
        if ((!in_array($props, $this->propsKeys) && ($props != self::propUrl))) {
            throw new \Dcp\Fmtc\Exception("FMTC0001", $props);
        }
        $this->fmtProps[$props] = $props;
        return $this;
    }

    /**
     * add an attribute to render
     * by default no attributes are rendered
     * @param string $attrid
     * @return \Anakeen\Core\Internal\FormatCollection
     */
    public function addAttribute($attrid)
    {
        $this->fmtAttrs[$attrid] = $attrid;
        return $this;
    }

    /**
     * apply a callback on each document
     * if callback return false, the document is skipped from list
     * @param \Closure $hookFunction
     * @return $this
     */
    public function setHookAdvancedStatus($hookFunction)
    {
        $this->hookStatus = $hookFunction;
        return $this;
    }

    /**
     * apply a callback on each returned value
     * to modify render
     * @param \Closure $hookFunction
     * @return $this
     */
    public function setAttributeRenderHook($hookFunction)
    {
        $this->renderAttributeHook = $hookFunction;
        return $this;
    }

    /**
     * apply a callback on each document returned
     * to modify render
     * @param \Closure $hookFunction
     * @return $this
     */
    public function setDocumentRenderHook($hookFunction)
    {
        $this->renderDocumentHook = $hookFunction;
        return $this;
    }

    /**
     * apply a callback on each returned property
     * to modify render value
     * @param \Closure $hookFunction
     * @return $this
     */
    public function setPropertyRenderHook($hookFunction)
    {
        $this->renderPropertyHook = $hookFunction;
        return $this;
    }

    protected function callHookStatus($s)
    {
        if ($this->hookStatus) {
            // call_user_func($function, $this->currentDoc);
            $h = $this->hookStatus;
            return $h($s);
        }
        return true;
    }

    /**
     * @param Format\StandardAttributeValue|null                      $info
     * @param \Anakeen\Core\SmartStructure\BasicAttribute|null $oa
     * @param \Anakeen\Core\Internal\SmartElement              $doc
     * @return Format\StandardAttributeValue
     */
    protected function callAttributeRenderHook($info, $oa, \Anakeen\Core\Internal\SmartElement $doc)
    {
        if ($this->renderAttributeHook) {
            $h = $this->renderAttributeHook;
            return $h($info, $oa, $doc);
        }
        return $info;
    }

    /**
     * @param array                               $info
     * @param \Anakeen\Core\Internal\SmartElement $doc
     * @return array
     */
    protected function callDocumentRenderHook(array $info, \Anakeen\Core\Internal\SmartElement $doc)
    {
        if ($this->renderDocumentHook) {
            $h = $this->renderDocumentHook;
            return $h($info, $doc);
        }
        return $info;
    }

    /**
     * @param Format\StandardAttributeValue|string|null  $info
     * @param string                              $propId
     * @param \Anakeen\Core\Internal\SmartElement $doc
     * @return Format\StandardAttributeValue
     */
    protected function callPropertyRenderHook($info, $propId, \Anakeen\Core\Internal\SmartElement $doc)
    {
        if ($this->renderPropertyHook) {
            $h = $this->renderPropertyHook;
            return $h($info, $propId, $doc);
        }
        return $info;
    }

    /**
     * return formatted document list to be easily exported in other format
     * @throws \Dcp\Fmtc\Exception
     * @return array
     */
    public function render()
    {
        /**
         * @var \Anakeen\Core\Internal\SmartElement $doc
         */
        $r = array();
        $kdoc = 0;
        $countDoc = count($this->dl);
        foreach ($this->dl as $docid => $doc) {
            if ($kdoc % 10 == 0) {
                $this->callHookStatus(sprintf(_("Doc Render %d/%d"), $kdoc, $countDoc));
            }
            $renderDoc = array();
            foreach ($this->fmtProps as $propName) {
                $renderDoc["properties"][$propName] = $this->callPropertyRenderHook($this->getPropInfo($propName, $doc), $propName, $doc);
            }

            foreach ($this->fmtAttrs as $attrid) {
                $oa = $doc->getAttribute($attrid);
                if ($oa) {
                    if (($oa->type == "array") || ($oa->type == "tab") || ($oa->type == "frame")) {
                        throw new \Dcp\Fmtc\Exception("FMTC0002", $attrid);
                    }

                    $value = $doc->getRawValue($oa->id);
                    if ($value === '') {
                        if ($this->verifyAttributeAccess === true && !FieldAccessManager::hasReadAccess($doc, $oa)) {
                            $attributeInfo = new Format\noAccessAttributeValue($this->noAccessText);
                        } else {
                            if ($this->useShowEmptyOption && $empty = $oa->getOption("showempty")) {
                                $attributeInfo = new Format\StandardAttributeValue($oa, null);
                                $attributeInfo->displayValue = $empty;
                            } else {
                                $attributeInfo = null;
                            }
                        }
                    } else {
                        $attributeInfo = $this->getInfo($oa, $value, $doc);
                    }
                    $renderDoc["attributes"][$oa->id] = $this->callAttributeRenderHook($attributeInfo, $oa, $doc);
                } else {
                    $renderDoc["attributes"][$attrid] = $this->callAttributeRenderHook(new Format\UnknowAttributeValue($this->ncAttribute), null, $doc);
                }
            }

            $r[$kdoc] = $this->callDocumentRenderHook($renderDoc, $doc);

            $kdoc++;
        }
        return $r;
    }

    protected function getPropInfo($propName, \Anakeen\Core\Internal\SmartElement $doc)
    {
        switch ($propName) {
            case self::title:
                return $doc->getTitle();
            case self::propIcon:
                return $doc->getIcon('', $this->familyIconSize);
            case self::propId:
                return intval($doc->id);
            case self::propInitid:
                return intval($doc->initid);
            case self::propRevision:
                return intval($doc->revision);
            case self::propLocked:
                return intval($doc->locked);
            case self::propState:
                return $this->getState($doc);
            case self::propUrl:
                return sprintf("/api/v2/documents/%s.html", $doc->id);
            case self::revdate:
                return $this->getFormatDate(date("Y-m-d H:i:s", intval($doc->$propName)), $this->propDateStyle);
            case self::cdate:
            case self::adate:
                return $this->getFormatDate($doc->$propName, $this->propDateStyle);
            case self::propFamily:
                return $this->getFamilyInfo($doc);
            case self::propLastAccessDate:
                return $this->getFormatDate($doc->adate, $this->propDateStyle);
            case self::propLastModificationDate:
                return $this->getFormatDate(date("Y-m-d H:i:s", $doc->revdate), $this->propDateStyle);
            case self::propCreationDate:
                if ($doc->revision == 0) {
                    return $this->getFormatDate($doc->cdate, $this->propDateStyle);
                } else {
                    $sql = sprintf("select cdate from docread where initid=%d and revision = 0", $doc->initid);
                    \Anakeen\Core\DbManager::query($sql, $cdate, true, true);
                    return $this->getFormatDate($cdate, $this->propDateStyle);
                }
            // no break
            case self::propCreatedBy:
                return $this->getCreatedByData($doc);
            case self::propRevisionData:
                return $this->getRevisionData($doc);
            case self::propViewController:
                return $this->getViewControllerData($doc);
            case self::propWorkflow:
                return $this->getWorkflowData($doc);
            case self::propTags:
                return $this->getApplicationTagsData($doc);
            case self::propSecurity:
                return $this->getSecurityData($doc);
            case self::propAffected:
                return $this->getAllocatedData($doc);
            case self::propStatus:
                return $this->getStatusData($doc);
            case self::propNote:
                return $this->getNoteData($doc);
            case self::propUsage:
                return $this->getUsageData($doc);
            case self::propType:
                return $this->getTypeData($doc);
            case self::propDProfid:
                return (int)$doc->dprofid;
            default:
                return $doc->$propName;
        }
    }

    protected function getCreatedByData(\Anakeen\Core\Internal\SmartElement $doc)
    {
        if ($doc->revision == 0) {
            $ownerId = $doc->owner;
        } else {
            $sql = sprintf("select owner from docread where initid=%d and revision = 0", $doc->initid);
            \Anakeen\Core\DbManager::query($sql, $ownerId, true, true);
        }
        return $this->getAccountData(abs($ownerId), $doc);
    }

    protected function getStatusData(\Anakeen\Core\Internal\SmartElement $doc)
    {
        if ($doc->doctype == "Z") {
            return "deleted";
        } elseif ($doc->locked == -1) {
            return "fixed";
        } else {
            return "alive";
        }
    }

    protected function getUsageData(\Anakeen\Core\Internal\SmartElement $doc)
    {
        if (strstr($doc->usefor, "S")) {
            return "system";
        } else {
            return "normal";
        }
    }

    protected function getTypeData(\Anakeen\Core\Internal\SmartElement $doc)
    {
        switch ($doc->defDoctype) {
            case 'F':
                return "document";
            case 'D':
                return "folder";
            case "S":
                return "search";
            case "C":
                return "family";
            case "P":
                return "profil";
            case "W":
                return "workflow";
            default:
                return $doc->defDoctype;
        }
    }

    protected function getNoteData(\Anakeen\Core\Internal\SmartElement $doc)
    {
        if ($doc->postitid > 0) {
            $note = SEManager::getDocument($doc->postitid);
            return array(
                "id" => intval($note->initid),
                "title" => $note->getTitle(),
                "icon" => $note->getIcon("", $this->familyIconSize)
            );
        } else {
            return array(
                "id" => 0,
                "title" => ""
            );
        }
    }

    protected function getWorkflowData(\Anakeen\Core\Internal\SmartElement $doc)
    {
        if ($doc->wid > 0) {
            $workflow = SEManager::getDocument($doc->wid);
            return array(
                "id" => intval($workflow->initid),
                "title" => $workflow->getTitle(),
                "icon" => $workflow->getIcon("", $this->familyIconSize)
            );
        } else {
            return array(
                "id" => 0,
                "title" => ""
            );
        }
    }

    protected function getApplicationTagsData(\Anakeen\Core\Internal\SmartElement $doc)
    {
        if ($doc->atags) {
            return explode("\n", $doc->atags);
        } else {
            return array();
        }
    }

    protected function getAllocatedData(\Anakeen\Core\Internal\SmartElement $doc)
    {
        if ($doc->allocated > 0) {
            return $this->getAccountData($doc->allocated, $doc);
        } else {
            return array(
                "id" => 0,
                "title" => ""
            );
        }
    }

    protected function getAccountData($accountId, \Anakeen\Core\Internal\SmartElement $doc)
    {
        $sql = sprintf("select initid, icon, title from doc128 where us_whatid='%d' and locked != -1", $accountId);

        \Anakeen\Core\DbManager::query($sql, $result, false, true);
        if ($result) {
            return array(
                "id" => intval($result["initid"]),
                "title" => $result["title"],
                "icon" => $doc->getIcon($result["icon"], $this->familyIconSize)
            );
        } else {
            return array(
                "id" => 0,
                "title" => "",
                "icon" => ""
            );
        }
    }

    protected function getSecurityData(\Anakeen\Core\Internal\SmartElement $doc)
    {
        $info = array();
        if ($doc->locked) {
            if ($doc->locked == -1) {
                $info["lock"] = array(
                    "id" => -1,
                    "temporary" => false
                );
            } else {
                $info["lock"] = array(
                    "lockedBy" => $this->getAccountData(abs($doc->locked), $doc),
                    "temporary" => ($doc->locked < -1)
                );
            }
        } else {
            $info["lock"] = array(
                "id" => 0
            );
        }
        $info["readOnly"] = ($doc->canEdit() != "");
        $info["fixed"] = ($doc->locked == -1);
        if ($doc->profid != 0) {
            if ($doc->profid == $doc->id) {
                $info["profil"] = array(
                    "id" => intval($doc->initid),
                    "icon" => $doc->getIcon("", $this->familyIconSize),
                    "private" => true,
                    "activated" => true,
                    "type" => "private",
                    "title" => $doc->getTitle()
                );
                if ($doc->dprofid > 0) {
                    $profil = SEManager::getDocument($doc->dprofid);
                    $info["profil"]["reference"] = array(
                        "id" => intval($profil->initid),
                        "icon" => $profil->getIcon("", $this->familyIconSize),
                        "activated" => ($profil->id == $profil->profid),
                        "title" => $profil->getTitle()
                    );
                    $info["profil"]["type"] = "dynamic";
                }
            } else {
                $profil = SEManager::getDocument(abs($doc->profid));
                $info["profil"] = array(
                    "id" => intval($profil->initid),
                    "icon" => $profil->getIcon("", $this->familyIconSize),
                    "type" => "linked",
                    "activated" => ($profil->id == $profil->profid),
                    "title" => $profil->getTitle()
                );
            }
        } else {
            $info["profil"] = array(
                "id" => 0,
                "title" => ""
            );
        }

        $info["confidentiality"] = ($doc->confidential > 0) ? "private" : "public";
        return $info;
    }

    protected function getViewControllerData(\Anakeen\Core\Internal\SmartElement $doc)
    {
        if ($doc->cvid > 0) {
            $cv = SEManager::getDocument($doc->cvid);
            return array(
                "id" => intval($cv->initid),

                "title" => $cv->getTitle(),
                "icon" => $cv->getIcon("", $this->familyIconSize)
            );
        } else {
            return array(
                "id" => 0,
                "title" => ""
            );
        }
    }

    protected function getRevisionData(\Anakeen\Core\Internal\SmartElement $doc)
    {
        return array(
            "isModified" => ($doc->lmodify == "Y"),
            "id" => intval($doc->id),
            "number" => intval($doc->revision),
            "createdBy" => $this->getAccountData(abs($doc->owner), $doc)
        );
    }

    protected function getFamilyInfo(\Anakeen\Core\Internal\SmartElement $doc)
    {
        $family = $doc->getFamilyDocument();
        return array(
            "title" => $family->getTitle(),
            "name" => $family->name,
            "id" => intval($family->id),
            "icon" => $family->getIcon("", $this->familyIconSize)
        );
    }

    protected function getFormatDate($v, $dateStyle = '')
    {
        if (!$dateStyle) {
            $dateStyle = $this->dateStyle;
        }
        if ($dateStyle === Format\DateAttributeValue::defaultStyle) {
            return stringDateToLocaleDate($v);
        } elseif ($dateStyle === Format\DateAttributeValue::isoStyle) {
            return stringDateToIso($v, false, true);
        } elseif ($dateStyle === Format\DateAttributeValue::isoWTStyle) {
            return stringDateToIso($v, false, false);
        } elseif ($dateStyle === Format\DateAttributeValue::frenchStyle) {
            $ldate = stringDateToLocaleDate($v, '%d/%m/%Y %H:%M');
            if (strlen($v) < 11) {
                return substr($ldate, 0, strlen($v));
            } else {
                return $ldate;
            }
        }
        return stringDateToLocaleDate($v);
    }

    protected function getState(\Anakeen\Core\Internal\SmartElement $doc)
    {
        $s = new Format\StatePropertyValue();
        if ($doc->state) {
            $s->reference = $doc->state;
            $s->stateLabel = _($doc->state);

            if ($doc->locked != -1) {
                $s->activity = $doc->getStateActivity();
                if ($s->activity) {
                    $s->displayValue = $s->activity;
                } else {
                    $s->displayValue = $s->stateLabel;
                }
            } else {
                $s->displayValue = $s->stateLabel;
            }

            $s->color = $doc->getStateColor();
        }
        return $s;
    }

    /**
     * delete last null values
     * @param array $t
     * @return array
     */
    protected static function rtrimNull(array $t)
    {
        $i = count($t) - 1;
        for ($k = $i; $k >= 0; $k--) {
            if ($t[$k] === null) {
                unset($t[$k]);
            } else {
                break;
            }
        }
        return $t;
    }

    public function getInfo(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $value, $doc = null)
    {
        $info = null;
        if ($oa->isMultiple()) {
            if ($oa->isMultipleInArray()) {
                // double level multiple
                $tv = \Anakeen\Core\Internal\SmartElement::rawValueToArray($value);
                if (count($tv) == 1 && $tv[0] == "\t") {
                    $tv[0] = '';
                }
                foreach ($tv as $k => $av) {
                    if ($av !== '') {
                        if (is_array($av)) {
                            $tvv = $this->rtrimNull($av);
                        } else {
                            $tvv = explode('<BR>', $av); // second level multiple
                        }
                        if (count($tvv) == 0) {
                            $info[$k] = array();
                        } else {
                            foreach ($tvv as $avv) {
                                $info[$k][] = $this->getSingleInfo($oa, $avv, $doc);
                            }
                        }
                    } else {
                        $info[$k] = array();
                    }
                }
            } else {
                // single level multiple
                $tv = \Anakeen\Core\Internal\SmartElement::rawValueToArray($value);
                if ($oa->inArray() && count($tv) == 1 && $tv[0] == "\t") {
                    $tv[0] = '';
                }

                foreach ($tv as $k => $av) {
                    $info[] = $this->getSingleInfo($oa, $av, $doc, $k);
                }
            }

            return $info;
        } else {
            return $this->getSingleInfo($oa, $value, $doc);
        }
    }

    protected function getSingleInfo(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $value, $doc = null, $index = -1)
    {
        $info = null;

        if ($this->verifyAttributeAccess === true && !FieldAccessManager::hasReadAccess($doc, $oa)) {
            $info = new Format\noAccessAttributeValue($this->noAccessText);
        } else {
            switch ($oa->type) {
                case 'text':
                    $info = new Format\TextAttributeValue($oa, $value);
                    break;

                case 'longtext':
                    $info = new Format\LongtextAttributeValue($oa, $value, $this->longtextMultipleBrToCr);
                    break;

                case 'int':
                    $info = new Format\IntAttributeValue($oa, $value);
                    break;

                case 'money':
                    $info = new Format\MoneyAttributeValue($oa, $value);
                    break;

                case 'double':
                    $info = new Format\DoubleAttributeValue($oa, $value, $this->decimalSeparator);
                    break;

                case 'enum':
                    $info = new Format\EnumAttributeValue($oa, $value);
                    break;

                case 'thesaurus':
                    $info = new Format\ThesaurusAttributeValue($oa, $value, $doc, $this->relationIconSize, $this->relationNoAccessText);
                    break;

                case 'docid':
                case 'account':
                    $info = new Format\DocidAttributeValue($oa, $value, $doc, $this->relationIconSize, $this->relationNoAccessText);
                    break;

                case 'file':
                    $info = new Format\FileAttributeValue($oa, $value, $doc, $index, $this->mimeTypeIconSize);
                    break;

                case 'image':
                    $info = new Format\ImageAttributeValue($oa, $value, $doc, $index, $this->imageThumbnailSize);
                    break;

                case 'timestamp':
                case 'date':
                    $info = new Format\DateAttributeValue($oa, $value, $this->dateStyle);
                    break;

                case 'htmltext':
                    $info = new Format\HtmltextAttributeValue($oa, $value, $this->stripHtmlTag);
                    break;

                default:
                    $info = new Format\StandardAttributeValue($oa, $value);
                    break;
            }
        }
        return $info;
    }

    /**
     * @param string $longtextMultipleBrToCr
     */
    public function setLongtextMultipleBrToCr($longtextMultipleBrToCr)
    {
        $this->longtextMultipleBrToCr = $longtextMultipleBrToCr;
    }

    /**
     * get some stat to estimate time cost
     * @return array
     */
    public function getDebug()
    {
        $average = $cost = $sum = array();
        foreach ($this->debug as $type => $time) {
            $average[$type] = sprintf("%0.3fus", array_sum($time) / count($time) * 1000000);
            $cost[$type] = sprintf("%0.3fms", array_sum($time) * 1000);
            $sum[$type] = sprintf("%d", count($time));
        }

        return array(
            "average" => $average,
            "cost" => $cost,
            "count" => $sum
        );
    }

    /**
     * @param array|\stdClass                              $info
     * @param \Anakeen\Core\SmartStructure\NormalAttribute $oAttr
     * @param int                                          $index
     * @param array                                        $configuration
     * @return string
     */
    public static function getDisplayValue($info, $oAttr, $index = -1, $configuration = array())
    {
        $attrInArray = ($oAttr->inArray());
        $attrIsMultiple = ($oAttr->getOption('multiple') == 'yes');
        $sepRow = isset($configuration['multipleSeparator'][0]) ? $configuration['multipleSeparator'][0] : "\n";
        $sepMulti = isset($configuration['multipleSeparator'][1]) ? $configuration['multipleSeparator'][1] : ", ";
        $displayDocId = (isset($configuration['displayDocId']) && $configuration['displayDocId'] === true) && (!isset($info->visible));

        if (is_array($info) && $index >= 0) {
            $info = array(
                $info[$index]
            );
        }
        if ($displayDocId && is_array($info) && count($info) > 0) {
            $displayDocId = (!isset($info[0]->visible));
        }

        if (!$attrInArray) {
            if ($attrIsMultiple) {
                $multiList = array();
                if (empty($info)) {
                    $info = array();
                }
                foreach ($info as $data) {
                    $multiList[] = $displayDocId ? $data->value : $data->displayValue;
                }
                $result = join($sepMulti, $multiList);
            } else {
                $result = $displayDocId ? $info->value : $info->displayValue;
            }
        } else {
            $rowList = array();
            if ($attrIsMultiple) {
                if (empty($info)) {
                    $info = array();
                }
                foreach ($info as $multiData) {
                    $multiList = array();
                    foreach ($multiData as $data) {
                        $multiList[] = $displayDocId ? $data->value : $data->displayValue;
                    }
                    $rowList[] = join($sepMulti, $multiList);
                }
            } else {
                if (!is_array($info)) {
                    $info = array(
                        $info
                    );
                }
                foreach ($info as $data) {
                    $rowList[] = $displayDocId ? $data->value : $data->displayValue;
                }
            }
            $result = join($sepRow, $rowList);
        }
        return $result;
    }
}
