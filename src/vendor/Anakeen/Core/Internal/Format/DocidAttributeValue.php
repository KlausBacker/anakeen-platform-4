<?php

namespace Anakeen\Core\Internal\Format;

class DocidAttributeValue extends StandardAttributeValue
{
    public $familyRelation;

    public $url;
    public $icon = null;
    public $revision = -1;
    public $initid;
    public $fromid;
    protected $visible = true;

    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v, \Anakeen\Core\Internal\SmartElement & $doc, $iconsize = 24, $relationNoAccessText = '')
    {
        $this->familyRelation = $oa->format;
        $this->value = ($v === '') ? null : $v;
        $info = array();
        $docRevOption = $oa->getOption("docrev", "latest");
        $this->displayValue = \DocTitle::getRelationTitle($v, $docRevOption == "latest", $doc, $docRevOption, $info);
        if ($this->displayValue !== false) {
            if ($v !== '' && $v !== null) {
                if ($iconsize > 0) {
                    if (!empty($info["icon"])) {
                        $this->icon = $doc->getIcon($info["icon"], $iconsize, $info["initid"]);
                    } else {
                        $this->icon = $doc->getIcon("doc.png", $iconsize);
                    }
                }
                $this->url = $this->getDocUrl($v, $docRevOption);
                if ($docRevOption === "fixed") {
                    $this->revision = intval($info["revision"]);
                } elseif (preg_match('/^state\(([^\)]+)\)/', $docRevOption, $matches)) {
                    $this->revision = array(
                        "state" => $matches[1]
                    );
                }
                if (isset($info["initid"])) {
                    $this->initid = intval($info["initid"]);
                }
                if (isset($info["fromid"])) {
                    $this->fromid = intval($info["fromid"]);
                }
            }
        } else {
            $this->visible = false;
            if ($relationNoAccessText) {
                $this->displayValue = $relationNoAccessText;
            } else {
                $this->displayValue = $oa->getOption("noaccesstext", _("information access deny"));
            }
        }
    }

    protected function getDocUrl($v, $docrev)
    {
        if (!$v) {
            return '';
        }
        $ul = "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=" . $v;

        if ($docrev == "latest" || $docrev == "" || !$docrev) {
            $ul .= "&amp;latest=Y";
        } elseif ($docrev != "fixed") {
            // validate that docrev looks like state(xxx)
            if (preg_match('/^state\(([a-zA-Z0-9_:-]+)\)/', $docrev, $matches)) {
                $ul .= "&amp;state=" . $matches[1];
            }
        }
        return $ul;
    }
}
