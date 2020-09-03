<?php


namespace Anakeen\Hub\Exchange;

use Anakeen\Core\Account;
use Anakeen\Core\AccountManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod;
use Anakeen\Core\SmartStructure\ExportConfiguration;
use Anakeen\Core\SmartStructure\NormalAttribute;
use Anakeen\Exception;
use Anakeen\Vault\FileInfo;

class HubExport
{

    const NSHUBURL = ExportConfiguration::NSBASEURL . "hub/1.0";
    const NSHUB = "hub";

    /**
     * @var \DOMDocument
     */
    protected $dom;
    /**
     * @var \Anakeen\Core\Internal\SmartElement|null
     */
    protected $smartElement;
    /**
     * @var \DOMElement
     */
    protected $domConfig;

    public function __construct(\Anakeen\Core\Internal\SmartElement $smartElement)
    {
        $this->smartElement = $smartElement;
    }

    protected function initDom()
    {
        $this->dom = new \DOMDocument("1.0", "UTF-8");
        $this->dom->formatOutput = true;
        $this->dom->preserveWhiteSpace = false;
        $this->domConfig = $this->cel("config");
        $this->domConfig->setAttribute("xmlns:" . self::NSHUB, self::NSHUBURL);
        $this->dom->appendChild($this->domConfig);

        return $this->domConfig;
    }

    /**
     * @param $name
     * @param string|null $value
     * @param \DOMElement|null $parent
     * @return \DOMElement
     */
    protected function cel($name, $value = null, $parent = null)
    {
        $node = $this->dom->createElementNS(
            self::NSHUBURL,
            self::NSHUB . ":" . $name
        );

        if ($value !== null) {
            $node->nodeValue = $value;
        }

        if ($parent !== null) {
            $parent->appendChild($node);
        }

        return $node;
    }

    protected function comment($comment, $parent = null)
    {
        $node = $this->dom->createComment($comment);




        if ($parent !== null) {
            $parent->appendChild($node);
        }

        return $node;
    }

    public static function getLogicalName($id)
    {
        $name = SEManager::getNameFromId($id);
        if ($name === null) {
            $name = "HUB_".$id;
            $se=SEManager::getDocument($id);
            $err=$se->setLogicalName($name);
            if ($err) {
                throw new Exception($err);
            }
        }
        return $name;
    }

    protected function addField($fieldId, $tagName, $parent)
    {
        $oa = $this->smartElement->getAttribute($fieldId);
        if ($oa) {
            $value = $this->smartElement->getRawValue($oa->id);
            if ($value !== "") {
                $this->comment($oa->fieldSet->getLabel() . " / " . $oa->getLabel(), $parent);
                if ($oa->isMultiple()) {
                    $values = $this->smartElement->getMultipleRawValues($oa->id);
                    foreach ($values as $value) {
                        if ($value !== "") {
                            $this->setXmlValue($this->cel($tagName, null, $parent), $oa, $value);
                        }
                    }
                } else {
                    if ($value !== "") {
                        $this->setXmlValue($this->cel($tagName, null, $parent), $oa, $value);
                    }
                }
            }
        }
    }

    protected function addCallable(\DOMElement $node)
    {
        $value = $node->nodeValue;
        $parse = new ParseFamilyMethod();
        $parse->parse($value);
        if ($parse->methodName) {
            $callableNode = $this->cel("asset-callable");
            $callableNode->setAttribute("function", sprintf("%s::%s", $parse->className, $parse->methodName));
            $node->nodeValue = null;
            $node->appendChild($callableNode);
            foreach ($parse->inputs as $input) {
                $argNode = $this->cel("asset-argument");
                $argNode->nodeValue = $input->name;
                $node->appendChild($argNode);
            }
        }
    }

    protected function setXmlValue(\DOMElement $node, NormalAttribute $oa, $rawvalue)
    {
        switch ($oa->type) {
            case "account":
                $uid = AccountManager::getIdFromSEId($rawvalue);
                $u = new Account("", $uid);
                $node->setAttribute("login", $u->login);
                break;
            case "docid":
                $seName = $this->getLogicalName($rawvalue);

                $node->setAttribute("ref", $seName?:$rawvalue);
                break;
            case "image":
                /**  @var FileInfo $fileInfo */
                $fileInfo = $this->smartElement->getFileInfo($rawvalue, "", "object");
                $node->nodeValue = base64_encode(file_get_contents($fileInfo->path));
                $node->setAttribute("title", $fileInfo->name);
                $node->setAttribute("mime", $fileInfo->mime_s);
                break;
            default:
                $node->nodeValue = $rawvalue;
        }
        return $node;
    }

    protected function addFieldArrayTwoColumns($fieldId, $tagName, $fieldId2, $attrName, $parent)
    {
        $oa = $this->smartElement->getAttribute($fieldId);
        if ($oa) {
            if ($oa->isMultiple()) {
                $tagValues = $this->smartElement->getMultipleRawValues($fieldId);
                $attrValues = $this->smartElement->getMultipleRawValues($fieldId2);
                foreach ($tagValues as $k => $v) {
                    if ($v) {
                        $node = $this->setXmlValue($this->cel($tagName, null, $parent), $oa, $v);

                        $node->setAttribute($attrName, strtolower($attrValues[$k]));

                        if ($tagName === "css" || $tagName === "js") {
                            $this->addCallable($node);
                        }
                    }
                }
            }
        }
    }
}
