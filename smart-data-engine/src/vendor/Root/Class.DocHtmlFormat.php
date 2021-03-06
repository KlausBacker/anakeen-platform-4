<?php
/** @noinspection PhpUnusedParameterInspection */

/*
 * @author Anakeen
 * @package FDL
*/

use Anakeen\Core\Settings;

/**
 * Get Html Value for document
 *
 * @class DocHtmlFormat
 *
 */
class DocHtmlFormat
{
    /**
     * @var \Anakeen\Core\Internal\SmartElement
     */
    public $doc = null;
    private $index = -1;
    private $target = '_self';
    /**
     * @var \Anakeen\Core\SmartStructure\NormalAttribute
     */
    private $oattr = null;
    private $attrid = '';
    /**
     * format set in type
     *
     * @var string
     */
    private $cFormat = '';
    private $cancelFormat = false;
    private $htmlLink = true;
    private $useEntities = true;
    private $abstractMode = false;
    /**
     * @var bool to send once vault error
     */
    private $vaultErrorSent = false;

    public function __construct(\Anakeen\Core\Internal\SmartElement &$doc)
    {
        $this->setDoc($doc);
    }

    public function setDoc(\Anakeen\Core\Internal\SmartElement &$doc)
    {
        $this->doc = $doc;
    }

    /**
     * get html fragment for a value of an attribute
     * for multiple values if index >= 0 the value must be the ith value of array values
     *
     * @param \Anakeen\Core\SmartStructure\NormalAttribute $oattr
     * @param string $value raw value
     * @param string $target
     * @param bool $htmlLink
     * @param int $index
     * @param bool $useEntities
     * @param bool $abstractMode
     *
     * @return string the HTML formated value
     */
    public function getHtmlValue(
        $oattr,
        $value,
        $target = "_self",
        $htmlLink = true,
        $index = -1,
        $useEntities = true,
        $abstractMode = false
    ) {
        $this->oattr = $oattr;
        $this->target = $target;
        $this->index = $index;
        $this->cFormat = $this->oattr->format;
        $this->cancelFormat = false;
        $atype = $this->oattr->type;
        $this->htmlLink = $htmlLink;
        $this->useEntities = $useEntities;
        $this->abstractMode = $abstractMode;

        $showEmpty = $this->oattr->getOption('showempty');

        if (($this->oattr->repeat) && ($this->index < 0)) {
            $tvalues = \Anakeen\Core\Internal\SmartElement::rawValueToArray($value, true);
            if (count($tvalues) === 0) {
                return $showEmpty;
            }
        } else {
            $tvalues[$this->index] = $value;
        }
        $this->attrid = $this->oattr->id;
        $thtmlval = array();
        foreach ($tvalues as $kvalue => $avalue) {
            if ($abstractMode && empty($avalue) && !$showEmpty) {
                $thtmlval[$kvalue] = '';
            } else {
                switch ($atype) {
                    case "image":
                        $htmlval = $this->formatImage($kvalue, $avalue);
                        break;

                    case "file":
                        $htmlval = $this->formatFile($kvalue, $avalue);
                        break;

                    case "longtext":
                    case "xml":
                        $htmlval = $this->formatLongtext($kvalue, $avalue);
                        break;

                    case "password":
                        $htmlval = $this->formatPassword($kvalue, $avalue);
                        break;

                    case "enum":
                        $htmlval = $this->formatEnum($kvalue, $avalue);
                        break;

                    case "array":
                        $htmlval = $this->formatArray($kvalue, $avalue);
                        break;

                    case "account":
                        $htmlval = $this->formatAccount($kvalue, $avalue);
                        break;

                    case "docid":
                        $htmlval = $this->formatDocid($kvalue, $avalue);
                        break;

                    case "thesaurus":
                        $htmlval = $this->formatThesaurus($kvalue, $avalue);
                        break;

                    case "option":
                        $htmlval = $this->formatOption($kvalue, $avalue);
                        break;

                    case 'money':
                        $htmlval = $this->formatMoney($kvalue, $avalue);
                        break;

                    case 'htmltext':
                        $htmlval = $this->formatHtmltext($kvalue, $avalue);
                        break;

                    case 'date':
                        $htmlval = $this->formatDate($kvalue, $avalue);
                        break;

                    case 'time':
                        $htmlval = $this->formatTime($kvalue, $avalue);
                        break;

                    case 'timestamp':
                        $htmlval = $this->formatTimeStamp($kvalue, $avalue);

                        break;

                    case 'ifile':
                        $htmlval = $this->formatIfile($kvalue, $avalue);
                        break;

                    case 'color':
                        $htmlval = $this->formatColor($kvalue, $avalue);
                        break;

                    default:
                        $htmlval = $this->formatDefault($kvalue, $avalue);

                        break;
                }

                $abegin = $aend = '';
                if (($htmlval === '' || $htmlval === null) && $showEmpty) {
                    if ($abstractMode) {
                        // if we are not in abstract mode, the same heuristic is at array level,
                        // but arrays does not exists in abstract mode
                        if (!$oattr->inArray()) {
                            $htmlval = $showEmpty;
                        } elseif ((count($tvalues) > 1)) {
                            // we are in an array, ensure the array is not totally empty
                            $htmlval = $showEmpty;
                        }
                    } else {
                        $htmlval = $showEmpty;
                    }
                } elseif ($htmlval === "\t" && $oattr->inArray() && $showEmpty) {
                    // array with single empty line
                    $htmlval = $showEmpty;
                } elseif (($this->cFormat != "" && $this->cancelFormat === false)
                    && ($htmlval !== '')
                    && ($atype != "enum")
                    && ($atype != "doc")
                    && ($atype != "array")
                    && ($atype != "option")) {
                    //printf($htmlval);
                    $htmlval = sprintf($this->cFormat, $htmlval);
                }
                // add link if needed
                if ($this->htmlLink && ($this->oattr->link != "")) {
                    $ititle = "";
                    $hlink = $this->oattr->link;
                    if ($hlink[0] == "[") {
                        if (preg_match('/\[(.*)\](.*)/', $hlink, $reg)) {
                            $hlink = $reg[2];
                            $ititle = str_replace("\"", "'", $reg[1]);
                        }
                    }
                    if ($ulink = $this->doc->urlWhatEncode($hlink, $kvalue)) {
                        if ($this->target == "ext") {
                            if (preg_match("/FDL_CARD.*id=([0-9]+)/", $ulink, $reg)) {
                                $abegin = $this->doc->getDocAnchor(
                                    $reg[1],
                                    $this->target,
                                    true,
                                    html_entity_decode($htmlval, ENT_QUOTES, 'UTF-8')
                                );
                                $htmlval = '';
                                $aend = "";
                            } elseif (true || preg_match("/^http:/", $ulink, $reg)) {
                                $ltarget = $this->oattr->getOption("ltarget");
                                $abegin = "<a target=\"$ltarget\"  href=\"$ulink\">";

                                $aend = "</a>";
                            }
                        } elseif ($this->target == "mail") {
                            $scheme = "";
                            if (preg_match("/^([[:alpha:]]*):(.*)/", $ulink, $reg)) {
                                $scheme = $reg[1];
                            }
                            $abegin = "<a target=\"$this->target\"  href=\"";
                            if ($scheme == "") {
                                $url = \Anakeen\Core\ContextManager::getParameterValue(
                                    Settings::NsSde,
                                    "CORE_URLINDEX"
                                );
                                if (!$url) {
                                    $url = \Anakeen\Core\ContextManager::getParameterValue(
                                        Settings::NsSde,
                                        "CORE_EXTERNURL"
                                    ) . "/";
                                }
                                $abegin .= $url . $ulink;
                            } else {
                                $abegin .= $ulink;
                            }
                            $abegin .= "\">";
                            $aend = "</a>";
                        } else {
                            $ltarget = $this->oattr->getOption("ltarget");
                            if ($ltarget != "") {
                                $this->target = $ltarget;
                            }
                            $ltitle = $this->oattr->getOption("ltitle");
                            if ($ltitle != "") {
                                $ititle = str_replace("\"", "'", $ltitle);
                            }
                            $abegin = "<a target=\"$this->target\" title=\"$ititle\" onmousedown=\"document.noselect=true;\" href=\"";
                            $abegin .= $ulink . "\" ";
                            if ($this->htmlLink > 1) {
                                $scheme = "";
                                if (preg_match("/^([[:alpha:]]*):(.*)/", $ulink, $reg)) {
                                    $scheme = $reg[1];
                                }
                                if (($scheme == "") || ($scheme == "http")) {
                                    if ($scheme == "") {
                                        $ulink .= "&ulink=1";
                                    }
                                    $abegin .= " oncontextmenu=\"popdoc(event,'$ulink');return false;\" ";
                                }
                            }
                            $abegin .= ">";
                            $aend = "</a>";
                        }
                    } else {
                        $abegin = "";
                        $aend = "";
                    }
                } else {
                    $abegin = "";
                    $aend = "";
                }
                if (is_array($htmlval)) {
                    $htmlval = implode(', ', $htmlval);
                }
                $thtmlval[$kvalue] = $abegin . $htmlval . $aend;
            }
        }

        return implode("<BR>", $thtmlval);
    }

    /**
     * format Default attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string HTML value
     */
    public function formatDefault($kvalue, $avalue)
    {
        if ($this->useEntities) {
            $avalue = htmlentities(($avalue), ENT_COMPAT, "UTF-8");
        } else {
            $avalue = ($avalue);
        }
        $htmlval = str_replace(array(
            "[",
            "$"
        ), array(
            "&#091;",
            "&#036;"
        ), $avalue);
        return $htmlval;
    }

    /**
     * format Image attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string HTML value
     */
    public function formatImage($kvalue, $avalue)
    {
        if (!$avalue) {
            return "";
        }
        if ($this->target == "te") {
            $htmlval = "file://" . $this->doc->vaultFilename($this->oattr->id, true, $kvalue);
        } else {
            if (preg_match(PREGEXPFILE, $avalue, $reg)) {
                $fileInfo = new Anakeen\Vault\FileInfo();
                $vf = new \Anakeen\Vault\VaultFile();
                if ($vf->Show($reg[2], $fileInfo) == "") {
                    if (!file_exists($fileInfo->path)) {
                        if (!$vf->storage->fs->isAvailable()) {
                            if (!$this->vaultErrorSent) {
                                \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("cannot access to vault file system")));
                            }
                            $this->vaultErrorSent = true;
                        } else {
                            \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("file %s not found"), $fileInfo->name));
                        }
                    }
                }
                if (($this->oattr->repeat) && ($this->index <= 0)) {
                    $idx = $kvalue;
                } else {
                    $idx = $this->index;
                }
                $inline = $this->oattr->getOption("inline");
                $htmlval = $this->doc->getFileLink(
                    $this->oattr->id,
                    $idx,
                    false,
                    ($inline == "yes"),
                    $avalue,
                    $fileInfo
                );
            }
        }
        $htmlval=sprintf('<img src="%s" alt="%s" />', $htmlval, htmlentities($this->oattr->getLabel()));
        return $htmlval;
    }

    /**
     * format File attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string HTML value
     */
    public function formatFile($kvalue, $avalue)
    {
        static $vf = null;

        if (!$vf) {
            $vf = new \Anakeen\Vault\VaultFile();
        }
        $vid = "";
        $fileInfo = false;
        $mime = '';
        $fname = _("no file");
        $htmlval = '';
        if (preg_match(PREGEXPFILE, $avalue, $reg)) {
            // reg[1] is mime type
            $vid = $reg[2];
            $mime = $reg[1];

            $fileInfo = new Anakeen\Vault\FileInfo();
            if ($vf->Show($reg[2], $fileInfo) == "") {
                $fname = $fileInfo->name;
                if (!file_exists($fileInfo->path)) {
                    if (!$vf->storage->fs->isAvailable()) {
                        if (!$this->vaultErrorSent) {
                            \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("Cannot access to vault file system")));
                        }
                        $this->vaultErrorSent = true;
                    } else {
                        \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("file %s not found"), $fileInfo->name));
                    }

                    $fname .= ' ' . _("(file not found)");
                }
            } else {
                $htmlval = _("vault file error");
            }
        } else {
            if ($this->oattr->getOption('showempty')) {
                $htmlval = $this->oattr->getOption('showempty');
                $this->cancelFormat = true;
            } else {
                $htmlval = _("no filename");
            }
        }

        if ($this->target == "mail") {
            $htmlval = "<a target=\"_blank\" href=\"";
            $htmlval .= "cid:" . $this->oattr->id;
            if ($this->index >= 0) {
                $htmlval .= "+$this->index";
            }
            $htmlval .= "\">" . htmlspecialchars($fname, ENT_QUOTES) . "</a>";
        } else {
            if ($fileInfo) {
                if ($fileInfo->teng_state < 0 || $fileInfo->teng_state > 1) {
                    $htmlval = "";
                    if (\Anakeen\Core\Internal\Autoloader::classExists('Anakeen\TransformationEngine\Client')) {
                        switch (intval($fileInfo->teng_state)) {
                            case \Anakeen\TransformationEngine\Client::error_convert: // convert fail
                                $textval = _("file conversion failed");
                                break;

                            case \Anakeen\TransformationEngine\Client::error_noengine: // no compatible engine
                                $textval = _("file conversion not supported");
                                break;

                            case \Anakeen\TransformationEngine\Client::error_connect: // no compatible engine
                                $textval = _("cannot contact server");
                                break;

                            case \Anakeen\TransformationEngine\Client::status_waiting: // waiting
                                $textval = _("waiting conversion file");
                                break;

                            case \Anakeen\TransformationEngine\Client::status_inprogress: // in progress
                                $textval = _("generating file");
                                break;

                            default:
                                $textval = sprintf(_("unknown file state %s"), $fileInfo->teng_state);
                        }
                    } else {
                        $textval = sprintf(_("unknown file state %s"), $fileInfo->teng_state);
                    }
                    if ($this->htmlLink) {
                        //$errconvert=trim(file_get_contents($info->path));
                        //$errconvert=sprintf('<p>%s</p>',str_replace(array("'","\r","\n"),array("&rsquo;",""),nl2br(htmlspecialchars($errconvert,ENT_COMPAT,"UTF-8"))));
                        if ($fileInfo->teng_state > 1) {
                            $waiting = "<img class=\"mime\" src=\"Images/loading.gif\">";
                        } else {
                            $waiting = "<img class=\"mime\" needresize=1 src=\"Images/bullet_error.png\">";
                        }
                        $htmlval = sprintf(
                            '<a _href_="%s" vid="%d" onclick="popdoc(event,this.getAttribute(\'_href_\')+\'&inline=yes\',\'%s\')">%s %s</a>',
                            $this->doc->getFileLink($this->oattr->id, $this->index),
                            $fileInfo->id_file,
                            str_replace("'", "&rsquo;", _("file status")),
                            $waiting,
                            $textval
                        );
                        if ($fileInfo->teng_state < 0) {
                            $htmlval .= sprintf(
                                '<a href="?app=FDL&action=FDL_METHOD&id=%d&method=resetConvertVaultFile(\'%s,%s)"><img class="mime" title="%s" src="
%s"></a>',
                                $this->doc->id,
                                $this->oattr->id,
                                $this->index,
                                _("retry file conversion"),
                                "Images/arrow_refresh.png"
                            );
                        }
                    } else {
                        $htmlval = $textval;
                    }
                } elseif ($this->htmlLink) {
                    $mimeicon = \Anakeen\Core\Utils\FileMime::getIconMimeFile($fileInfo->mime_s == "" ? $mime : $fileInfo->mime_s);
                    if (($this->oattr->repeat) && ($this->index <= 0)) {
                        $idx = $kvalue;
                    } else {
                        $idx = $this->index;
                    }
                    $standardview = true;
                    if ($standardview) {
                        $size = self::humanSize($fileInfo->size);
                        $utarget = "_blank";
                        $inline = $this->oattr->getOption("inline");
                        $htmlval = "<a onmousedown=\"document.noselect=true;\" title=\"$size\" target=\"$utarget\" type=\"$mime\" href=\""
                            . $this->doc->getFileLink(
                                $this->oattr->id,
                                $idx,
                                false,
                                ($inline == "yes"),
                                $avalue,
                                $fileInfo
                            ) . "\">";
                        if ($mimeicon) {
                            $htmlval .= "<img class=\"mime\" needresize=1  src=\"Images/$mimeicon\">&nbsp;";
                        }
                        $htmlval .= htmlspecialchars($fname, ENT_QUOTES) . "</a>";
                    }
                } else {
                    $htmlval = $fileInfo->name;
                }
            }
        }
        return $htmlval;
    }

    /**
     * format Longtext attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string HTML value
     */
    public function formatLongtext($kvalue, $avalue)
    {
        if ($this->useEntities) {
            $bvalue = nl2br(htmlentities((str_replace("<BR>", "\n", $avalue)), ENT_COMPAT, "UTF-8"));
        } else {
            $bvalue = (str_replace("<BR>", "\n", $avalue));
        }
        $shtmllink = $this->htmlLink ? "true" : "false";
        $bvalue = preg_replace_callback('/(\[|&#x5B;)ADOC ([^\]]*)\]/', function ($matches) use ($shtmllink) {
            return $this->doc->getDocAnchor($matches[2], $this->target, $shtmllink);
        }, $bvalue);
        $htmlval = str_replace(array(
            "[",
            "$"
        ), array(
            "&#091;",
            "&#036;"
        ), $bvalue);
        return $htmlval;
    }

    /**
     * format Password attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string HTML value
     */
    public function formatPassword($kvalue, $avalue)
    {
        if (strlen($avalue) > 0) {
            $htmlval = '*****';
        } else {
            $htmlval = '';
        }
        return $htmlval;
    }

    /**
     * format Enum attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string HTML value
     */
    public function formatEnum($kvalue, $avalue)
    {
        $enumlabel = $this->oattr->getEnumlabel();
        $colors = $this->oattr->getOption("boolcolor");
        if ($colors != "") {
            if (isset($enumlabel[$avalue])) {
                reset($enumlabel);
                $tcolor = explode(",", $colors);
                if (current($enumlabel) == $enumlabel[$avalue]) {
                    $color = $tcolor[0];
                    $htmlval = sprintf('<pre style="background-color:%s;display:inline">&nbsp;-&nbsp;</pre>', $color);
                } else {
                    $color = $tcolor[1];
                    $htmlval = sprintf(
                        '<pre style="background-color:%s;display:inline">&nbsp;&bull;&nbsp;</pre>',
                        $color
                    );
                }
            } else {
                $htmlval = $avalue;
            }
        } else {
            if (array_key_exists($avalue, $enumlabel)) {
                $htmlval = $enumlabel[$avalue];
            } else {
                $htmlval = $avalue;
            }
        }
        return $htmlval;
    }

    /**
     * format Array attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string HTML value
     */
    public function formatArray($kvalue, $avalue)
    {
        $htmlval = '';
        if (count($this->doc->getArrayRawValues($this->oattr->id)) === 0 && $this->oattr->getOption('showempty')) {
            $htmlval = $this->oattr->getOption('showempty');
            return $htmlval;
        }

        $mustache = new Mustache_Engine();
        $arrayData = [];
        $template = file_get_contents(__DIR__ . "/Layout/htmlArray.mustache.html");

        if (!method_exists($this->doc->attributes, "getArrayElements")) {
            return $htmlval;
        }
        $arrayData["caption"] = $this->oattr->getLabel();


        $ta = $this->doc->attributes->getArrayElements($this->oattr->id);


        $emptyarray = true;
        $nbitem = 0;

        $tval = array();
        foreach ($ta as $k => $v) {
            if ($v->getAccess() === \Anakeen\Core\SmartStructure\BasicAttribute::NONE_ACCESS) {
                continue;
            }
            $arrayData["headcells"][] = array(
                "headLabel" => ucfirst($v->getLabel()),
            );
            $tval[$k] = $this->doc->getMultipleRawValues($k);
            $nbitem = max($nbitem, count($tval[$k]));
            if ($emptyarray && ($this->doc->getRawValue($k) != "")) {
                $emptyarray = false;
            }
        }
        if (!$emptyarray) {
            for ($k = 0; $k < $nbitem; $k++) {
                $row = array();
                /**
                 * @var \Anakeen\Core\SmartStructure\NormalAttribute $va
                 */
                foreach ($ta as $ka => $va) {
                    if ($va->getAccess() === \Anakeen\Core\SmartStructure\BasicAttribute::NONE_ACCESS) {
                        $row[] = array(
                            "cellValue" => ""
                        );
                        continue;
                    }
                    if (isset($tval[$ka][$k])) {
                        $hval = $this->doc->getHtmlValue($va, $tval[$ka][$k], $this->target, $this->htmlLink, $k);
                    } else {
                        $hval = '';
                    }
                    if ($va->type == "image") {
                        $iwidth = $va->getOption("iwidth", "80px");
                        if (empty($tval[$ka][$k])) {
                            $hval = "";
                        } elseif ($va->link == "") {
                            if (strstr($hval, '?')) {
                                $optwidth = "&width=" . intval($iwidth);
                            } else {
                                $optwidth = '';
                            }
                            $hval = "<a  href=\"$hval\"><img border='0' width=\"$iwidth\" src=\"" . $hval . $optwidth . "\"></a>";
                        } else {
                            $hval = preg_replace(
                                "/>(.+)</",
                                ">&nbsp;<img class=\"button\" width=\"$iwidth\" src=\"\\1\">&nbsp;<",
                                $hval
                            );
                        }
                    }
                    $row[] = array(
                        "cellValue" => $hval
                    );
                }
                $arrayData["rows"][] = ["line" => $row];
            }

            $htmlval = $mustache->render($template, $arrayData);
        } else {
            $htmlval = "";
        }

        return $htmlval;
    }

    /**
     * @param DomElement $node
     *
     * @return bool
     */
    public static function xtInnerXML(&$node)
    {
        if (!$node) {
            return false;
        }
        $document = $node->ownerDocument;
        $nodeAsString = $document->saveXML($node);
        preg_match('!\<.*?\>(.*)\</.*?\>!s', $nodeAsString, $match);
        return $match[1];
    }

    /**
     * format Account attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string HTML value
     */
    public function formatAccount($kvalue, $avalue)
    {
        if (!$this->oattr->format) {
            $this->oattr->format = "x";
        }
        return $this->formatDocid($kvalue, $avalue);
    }

    /**
     * format Docid attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string HTML value
     */
    public function formatDocid($kvalue, $avalue)
    {
        if ($this->oattr->format != "") {
            $this->cancelFormat = true;
            $multiple = ($this->oattr->getOption("multiple") == "yes");
            $dtarget = $this->target;
            if ($this->target != "mail") {
                $ltarget = $this->oattr->getOption("ltarget");
                if ($ltarget != "") {
                    $dtarget = $ltarget;
                }
            }
            $isLatest = $this->oattr->getOption("docrev", "latest") == "latest";
            if ($multiple) {
                if (!is_array($avalue)) {
                    $avalue = str_replace("\n", "<BR>", $avalue);
                    $tval = explode("<BR>", $avalue);
                } else {
                    $tval = $avalue;
                }
                $thval = array();
                foreach ($tval as $kv => $vv) {
                    if (trim($vv) == "") {
                        $thval[] = $vv;
                    } else {
                        $title = DocTitle::getRelationTitle(trim($vv), $isLatest, $this->doc);
                        if ($this->oattr->link != "" && $title) {
                            $link = $this->doc->urlWhatEncode($this->oattr->link, $kvalue);
                            if ($link) {
                                $thval[] = '<a target="' . $dtarget . '" href="' . $link . '">' . $this->doc->htmlEncode($title) . '</a>';
                            } else {
                                if ($title === false) {
                                    $title = $this->doc->htmlEncode($this->oattr->getOption(
                                        "noaccesstext",
                                        _("information access deny")
                                    ));
                                }
                                $thval[] = $this->doc->htmlEncode($title);
                            }
                        } else {
                            if ($title === false) {
                                $thval[] = $this->doc->htmlEncode($this->oattr->getOption(
                                    "noaccesstext",
                                    _("information access deny")
                                ));
                            } else {
                                $thval[] = $this->doc->getDocAnchor(
                                    trim($vv),
                                    $dtarget,
                                    $this->htmlLink,
                                    $title,
                                    true,
                                    $this->oattr->getOption("docrev"),
                                    true
                                );
                            }
                        }
                    }
                }
                if ($this->oattr->link) {
                    $this->htmlLink = false;
                }
                $htmlval = implode("<br/>", $thval);
            } else {
                if ($avalue == "") {
                    $htmlval = $avalue;
                } elseif ($this->oattr->link != "") {
                    $title = DocTitle::getRelationTitle(trim($avalue), $isLatest, $this->doc);
                    $htmlval = $this->doc->htmlEncode($title);
                } else {
                    $title = DocTitle::getRelationTitle(trim($avalue), $isLatest, $this->doc);
                    if ($title === false) {
                        $htmlval = $this->doc->htmlEncode($this->oattr->getOption(
                            "noaccesstext",
                            _("information access deny")
                        ));
                    } else {
                        $htmlval = $this->doc->getDocAnchor(
                            trim($avalue),
                            $dtarget,
                            $this->htmlLink,
                            $title,
                            true,
                            $this->oattr->getOption("docrev"),
                            true
                        );
                    }
                }
            }
        } else {
            $htmlval = $avalue;
        }
        return $htmlval;
    }

    /**
     * format Image attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string HTML value
     */
    public function formatThesaurus($kvalue, $avalue)
    {
        $this->cancelFormat = true;
        $multiple = ($this->oattr->getOption("multiple") == "yes");
        if ($multiple) {
            $avalue = str_replace("\n", "<BR>", $avalue);
            $tval = explode("<BR>", $avalue);
            $thval = array();
            foreach ($tval as $vv) {
                if (trim($vv) == "") {
                    $thval[] = $vv;
                } else {
                    $thc = \Anakeen\Core\SEManager::getDocument(trim($vv));
                    if ($thc && $thc->isAlive()) {
                        $thval[] = $this->doc->getDocAnchor(
                            trim($vv),
                            $this->target,
                            $this->htmlLink,
                            $thc->getCustomTitle()
                        );
                    } else {
                        $thval[] = "th error1 $vv";
                    }
                }
            }
            $htmlval = implode("<br/>", $thval);
        } else {
            if ($avalue == "") {
                $htmlval = $avalue;
            } else {
                $avalue = trim($avalue);
                $thc = \Anakeen\Core\SEManager::getDocument($avalue);
                if ($thc && $thc->isAlive()) {
                    $htmlval = $this->doc->getDocAnchor(
                        trim($avalue),
                        $this->target,
                        $this->htmlLink,
                        $thc->getCustomTitle()
                    );
                } else {
                    $htmlval = "th error2 [$avalue]";
                }
            }
        }
        return $htmlval;
    }

    /**
     * format Option attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string HTML value
     */
    public function formatOption($kvalue, $avalue)
    {
        $lay = new \Anakeen\Layout\TextLayout("FDL/Layout/viewdocoption.xml");
        $htmlval = "";

        if ($kvalue > -1) {
            $di = $this->doc->getMultipleRawValues($this->oattr->format, "", $kvalue);
        } else {
            $di = $this->doc->getRawValue($this->oattr->format);
        }
        if ($di > 0) {
            $lay->set("said", $di);
            $lay->set("uuvalue", urlencode($avalue));

            $htmlval = $lay->gen();
        }
        return $htmlval;
    }

    /**
     * format Money attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string
     */
    public function formatMoney($kvalue, $avalue)
    {
        if ($avalue == '' && $this->oattr->getOption('showempty')) {
            $htmlval = $this->oattr->getOption('showempty');
            $this->cancelFormat = true;
        } else {
            if ($avalue !== '') {
                $htmlval = \Anakeen\Core\Internal\Format\MoneyAttributeValue::formatMoney(doubleval($avalue));
                // Be carreful replace normal space by non breaking spaces : Narrow No-Break Space
                $htmlval = str_replace(" ", "\u202F", $htmlval);
            } else {
                $htmlval = '';
            }
        }
        return $htmlval;
    }

    /**
     * format HTML attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string
     */
    public function formatHtmltext($kvalue, $avalue)
    {
        $htmlValue = $avalue;
        if ($avalue == '' && $this->oattr->getOption('showempty')) {
            $avalue = $this->oattr->getOption('showempty');
            $this->cancelFormat = true;
        }
        $shtmllink = $this->htmlLink ? "true" : "false";
        $avalue = preg_replace_callback('/(\[|&#x5B;)ADOC ([^\]]*)\]/', function ($matches) use ($shtmllink) {
            return $this->doc->getDocAnchor($matches[2], $this->target, $shtmllink);
        }, $avalue);
        if (stripos($avalue, "data-initid") !== false) {
            try {
                $domDoc = new DOMDocument();

                $domDoc->loadHTML(mb_convert_encoding($avalue, 'HTML-ENTITIES', 'UTF-8'));

                $aElements = $domDoc->getElementsByTagName("a");
                /**
                 * @var DOMElement $currentA
                 */
                foreach ($aElements as $currentA) {
                    if ($currentA->hasAttribute("data-initid")) {
                        $newA = $this->doc->getDocAnchor(
                            $currentA->getAttribute("data-initid"),
                            $this->target,
                            $shtmllink,
                            false,
                            true,
                            $currentA->getAttribute("data-docrev")
                        );
                        $newAFragment = $domDoc->createDocumentFragment();
                        $newAFragment->appendXML($newA);
                        $currentA->parentNode->replaceChild($newAFragment, $currentA);
                    }
                }

                $avalue = $domDoc->saveHTML();
            } catch (Exception $e) {
                error_log(sprintf(
                    "%s unable to parse/create html width docLink elements(document :%s, error %)s",
                    __METHOD__,
                    $this->doc->id,
                    $e->getMessage()
                ));
            }
        } else {
            $prefix = uniqid("");
            $avalue = str_replace(array(
                "[",
                "&#x5B;",
                "]"
            ), array(
                "B$prefix",
                "B$prefix",
                "D$prefix"
            ), $avalue);
            $avalue = \Anakeen\Core\Utils\HtmlClean::normalizeHTMLFragment(mb_convert_encoding(
                $avalue,
                'HTML-ENTITIES',
                'UTF-8'
            ), $error);
            $avalue = str_replace(array(
                "B$prefix",
                "D$prefix"
            ), array(
                "[",
                "]"
            ), $avalue);
            if ($error != '') {
                \Anakeen\Core\Utils\System::addWarningMsg(___("Malformed HTML:", "sed") . "\n" . $error);
            }
            if ($avalue === false) {
                $avalue = '';
            }
        }
        $htmlval = '<div class="htmltext">' . str_replace("[", "&#x5B;", $avalue) . '</div>';
        return $htmlval;
    }

    /**
     * format Date attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string
     */
    public function formatDate($kvalue, $avalue)
    {
        if (($this->cFormat != "") && (trim($avalue) != "")) {
            if ($avalue) {
                $htmlval = strftime($this->cFormat, \Anakeen\Core\Utils\Date::stringDateToUnixTs($avalue));
            } else {
                $htmlval = $avalue;
            }
        } elseif (trim($avalue) == "") {
            $htmlval = "";
        } else {
            $htmlval = \Anakeen\Core\Utils\Date::stringDateToLocaleDate($avalue);
        }
        $this->cancelFormat = true;
        return $htmlval;
    }

    /**
     * format Time attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string
     */
    public function formatTime($kvalue, $avalue)
    {
        if (($this->cFormat != "") && (trim($avalue) != "")) {
            if ($avalue) {
                $htmlval = strftime($this->cFormat, strtotime($avalue));
            } else {
                $htmlval = $avalue;
            }
        } else {
            if ($avalue) {
                $htmlval = (string)substr($avalue, 0, 5); // do not display second
            } else {
                $htmlval = '';
            }
        }
        $this->cancelFormat = true;
        return $htmlval;
    }

    /**
     * format TimeStamp attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string
     */
    public function formatTimestamp($kvalue, $avalue)
    {
        if (($this->cFormat != "") && (trim($avalue) != "")) {
            if ($avalue) {
                $htmlval = strftime($this->cFormat, \Anakeen\Core\Utils\Date::stringDateToUnixTs($avalue));
            } else {
                $htmlval = $avalue;
            }
        } elseif (trim($avalue) == "") {
            $htmlval = "";
        } else {
            $htmlval = \Anakeen\Core\Utils\Date::stringDateToLocaleDate($avalue);
        }
        $this->cancelFormat = true;
        return $htmlval;
    }

    /**
     * format IFile attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string
     */
    public function formatIfile($kvalue, $avalue)
    {
        $lay = new \Anakeen\Layout\TextLayout("FDL/Layout/viewifile.xml");
        $lay->set("aid", $this->oattr->id);
        $lay->set("id", $this->doc->id);
        $lay->set("iheight", $this->oattr->getOption("height", "200px"));
        $htmlval = $lay->gen();
        return $htmlval;
    }

    /**
     * format Color attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string
     */
    public function formatColor($kvalue, $avalue)
    {
        if ($avalue) {
            $htmlval = sprintf("<span style=\"background-color:%s\">%s</span>", $avalue, $avalue);
        } else {
            $htmlval = '';
        }
        return $htmlval;
    }


    /**
     * Format the given size in human readable SI format (up to terabytes).
     *
     * @param int $size
     *
     * @return string
     */
    private static function humanSize($size)
    {
        if (abs($size) < 1000) {
            return sprintf("%d %s", $size, n___("unit:byte", "unit:bytes", abs($size), 'sde'));
        }
        $size = $size / 1000;
        foreach ([_("unit:kB"), _("unit:MB"), _("unit:GB")] as $unit) {
            if (abs($size) < 1000) {
                return sprintf("%3.2f %s", $size, $unit);
            }
            $size = $size / 1000;
        }
        return sprintf("%.2f %s", $size, _("unit:TB"));
    }
}
