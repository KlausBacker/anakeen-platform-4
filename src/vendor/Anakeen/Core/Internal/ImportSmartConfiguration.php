<?php


namespace Anakeen\Core\Internal;

use Anakeen\Core\SmartStructure\ExportConfiguration;

class ImportSmartConfiguration
{
    /**
     * @var \DOMDocument $dom ;
     */
    protected $dom;
    /**
     * @var array report
     */
    private $cr = array();

    private $onlyAnalyze = false;

    /**
     * @param bool $onlyAnalyze
     */
    public function setOnlyAnalyze(bool $onlyAnalyze): void
    {
        $this->onlyAnalyze = $onlyAnalyze;
    }

    protected $fileName = '';


    public function import($xmlFile)
    {

        $this->dom = new \DOMDocument();

        $this->dom->load($xmlFile);

        $this->importSmartStructureConfigurations();
    }


    protected function importSmartStructureConfigurations()
    {
        $configs = $this->getNodes($this->dom->documentElement, "structure-configuration");
        foreach ($configs as $config) {
            $data = $this->importSmartStructureConfig($config);
            $this->print($data);
        }
        return $data;
    }

    public function print($data)
    {
        foreach ($data as $line) {
            printf("%s\n", implode(" - ", $line));
        }
    }

    protected function importSmartStructureConfig(\DOMElement $config)
    {
        $data[] = $this->extractBegin($config);
        $data = array_merge($data, $this->extractProps($config));
        $data = array_merge($data, $this->extractAttrs($config));
        $data = array_merge($data, $this->extractParams($config));
        $data[] = ["END"];

        $this->importSmartData($data);


        return $data;
    }


    protected function importSmartData(array $data)
    {
        $import = new \ImportDocumentDescription();

        $cr = $import->importData($data);
        print_r($cr);
    }

    protected function extractBegin(\DOMElement $config)
    {
        $data[0] = "BEGIN";
        // Inherit
        $data[1] = $config->getAttribute("extends");
        // Label
        $data[2] = $config->getAttribute("label");
        // Id not used
        $data[3] = "";
        // Old Class not used
        $data[4] = "";
        // Name
        $data[5] = $config->getAttribute("name");

        return $data;
    }


    protected function extractParams(\DOMElement $config)
    {
        $data = [];
        $nodeAttributes = $this->getNode($config, "parameters");
        if ($nodeAttributes) {
            foreach ($nodeAttributes->childNodes as $attrNode) {
                if (!is_a($attrNode, \DOMElement::class)) {
                    continue;
                }
                /**
                 * @var \DOMElement $attrNode
                 */
                if (preg_match('/smart:attr-/', $attrNode->tagName)) {
                    $data = array_merge($data, $this->extractAttr($attrNode, "PARAM"));
                }
            }
        }

        return $data;
    }

    protected function extractAttrs(\DOMElement $config)
    {
        $data = [];
        $nodeAttributes = $this->getNode($config, "attributes");
        if ($nodeAttributes) {
            foreach ($nodeAttributes->childNodes as $attrNode) {
                if (!is_a($attrNode, \DOMElement::class)) {
                    continue;
                }
                /**
                 * @var \DOMElement $attrNode
                 */
                if (preg_match('/smart:attr-/', $attrNode->tagName)) {
                    $data = array_merge($data, $this->extractAttr($attrNode, "ATTR"));
                }
            }
        }

        return $data;
    }

    protected function extractAttr(\DOMElement $attrNode, $key, $fieldName = "")
    {
        $data = [];
        if ($attrNode->tagName === "smart:attr-fieldset") {
            $data[] = $this->extractSingleAttr($attrNode, $key, $fieldName);
            $fieldName = $attrNode->getAttribute("name");
            foreach ($attrNode->childNodes as $childNode) {
                if (!is_a($childNode, \DOMElement::class)) {
                    continue;
                }
                /**
                 * @var \DOMElement $childNode
                 */
                if (preg_match('/smart:attr-/', $attrNode->tagName)) {
                    $data = array_merge($data, $this->extractAttr($childNode, $key, $fieldName));
                }
            }
        } else {
            $data[] = $this->extractSingleAttr($attrNode, $key, $fieldName);
        }
        return $data;
    }


    protected function extractSingleAttr(\DOMElement $attrNode, $key, $fieldName = "")
    {
        $attr = new ImportSmartAttr();
        if ($attrNode->tagName === "smart:attr-fieldset") {
            $attr->type = $attrNode->getAttribute("type");
        } else {
            $attr->type = substr($attrNode->tagName, strlen("smart:attr-"));
            $rel = $attrNode->getAttribute("relation");
            if ($rel) {
                $attr->type .= '("' . $rel . '")';
            }
        }
        $attr->id = $attrNode->getAttribute("name");
        $attr->label = $attrNode->getAttribute("label");
        $attr->idfield = $fieldName;
        $attr->visibility = $attrNode->getAttribute("visibility");
        $attr->link = $attrNode->getAttribute("link");
        $attr->need = ($attrNode->getAttribute("needed") === "true") ? "Y" : "N";
        $attr->isAbstract = ($attrNode->getAttribute("is-abstract") === "true") ? "Y" : "N";
        $attr->isTitle = ($attrNode->getAttribute("is-title") === "true") ? "Y" : "N";
        $attr->order = $attrNode->getAttribute("insert-after");
        if (!$attr->order) {
            $attr->order = "::auto";
        }
        $attr->constraint = $this->extractAttrHooks($attrNode, function (\DOMElement $e) {
            return $e->getAttribute("type") === "constraint";
        });
        $attr->phpfunc = $this->extractAttrHooks($attrNode, function (\DOMElement $e) {
            return $e->getAttribute("event") === "onPreRefresh";
        });
        $attr->option = $this->extractAttrOptions($attrNode);

        $data = $attr->getData($key);

        return $data;
    }


    protected function extractAttrHooks(\DOMElement $attrNode, \Closure $filter)
    {
        $config = $this->getClosest($attrNode, "structure-configuration");

        $attrid = $attrNode->getAttribute("name");
        $hooks = $this->getNodes($config, "attr-hook");
        $method = "";
        /**
         * @var \DOMElement $hook
         */
        foreach ($hooks as $hook) {
            if ($hook->getAttribute("attr") === $attrid) {
                if ($filter($hook)) {
                    $callableNode = $this->getNode($hook, "attr-callable");
                    $method .= $callableNode->getAttribute("function") . "(";
                    $argNodes = $this->getNodes($hook, "attr-argument");
                    $args = [];
                    /**
                     * @var  \DOMElement $argNode
                     */
                    foreach ($argNodes as $argNode) {
                        $type = $argNode->getAttribute("type");
                        $arg = $argNode->nodeValue;
                        if ($type === "string") {
                            // Escape quote
                            $arg = '"' . str_replace('"', '\\"', $arg) . '"';
                        }
                        $args[] = $arg;
                    }
                    $method .= implode(",", $args);

                    $method .= ')';


                    $returnNodes = $this->getNodes($hook, "attr-return");
                    $returns = [];
                    /**
                     * @var  \DOMElement $returnNode
                     */
                    foreach ($returnNodes as $returnNode) {
                        $attrid = $returnNode->getAttribute("attr");
                        $returns[] = strtolower($attrid);
                    }
                    if ($returns) {
                        $method .= ":" . implode(",", $returns);
                    }
                }
            }
        }
        return $method;
    }

    protected function extractAttrOptions(\DOMElement $attrNode)
    {
        $options = $this->getNodes($attrNode, "attr-option");
        $optData = [];
        /**
         * @TODO to delete ne need use flat notation
         */
        $optRaw = [];
        foreach ($options as $optNode) {
            /**
             * @var \DOMElement $optNode
             */
            $optData[$optNode->getAttribute("name")] = $optNode->getAttribute("name");

            $optRaw[] = sprintf("%s=%s", $optNode->getAttribute("name"), $optNode->nodeValue);
        }
        return implode("|", $optRaw);
        //return $optData;
    }

    protected function extractProps(\DOMElement $config)
    {
        $data = [];
        $node = $this->getNode($config, "usefor");
        if ($node) {
            $data[] = ["USEFOR", $node->nodeValue];
        }
        $node = $this->getNode($config, "schar");
        if ($node) {
            $data[] = ["SCHAR", $node->nodeValue];
        }
        $node = $this->getNode($config, "revisable");
        if ($node) {
            if ($node->nodeValue === "false") {
                $data[] = ["SCHAR", "S"];
            } elseif ($node->nodeValue === "auto") {
                $data[] = ["SCHAR", "R"];
            }
            if ($node->getAttribute("max")) {
                $data[] = ["MAXREV", $node->getAttribute("max")];
            }
        }

        $node = $this->getNode($config, "class");
        if ($node) {
            $data[] = ["CLASS", $node->nodeValue];
        }
        $node = $this->getNode($config, "methods");
        if ($node) {
            $data[] = ["METHOD", $node->nodeValue];
        }
        $node = $this->getNode($config, "icon");
        if ($node) {
            $data[] = ["ICON", $node->getAttribute("file")];
        }
        $node = $this->getNode($config, "tag");
        if ($node) {
            $data[] = ["TAG", $node->nodeValue];
        }
        return $data;
    }

    /**
     * @param string      $name
     * @param \DOMElement $e
     * @return \DOMNodeList
     */
    private function getNodes(\DOMElement $e, $name)
    {
        return $e->getElementsByTagNameNS(ExportConfiguration::NSURL, $name);
    }

    /**
     * @param \DOMElement $e
     * @param string      $name
     * @return \DOMElement
     */
    private function getNode(\DOMElement $e, $name)
    {
        $nodes = $this->getNodes($e, $name);
        if ($nodes) {
            return $nodes[0];
        }
        return null;
    }

    /**
     * @param \DOMElement $e
     * @param string      $name
     * @return \DOMElement
     */
    private function getClosest(\DOMElement $e, $name)
    {
        $tagName = "smart:" . $name;
        while ($e) {
            if (is_a($e, \DOMElement::class) && $e->tagName === $tagName) {
                return $e;
            }
            $e = $e->parentNode;
        }
        return null;
    }

    /**
     * return all error message concatenated
     * @return string
     */
    public function getErrorMessage()
    {
        $terr = array();
        foreach ($this->cr as $cr) {
            if ($cr["err"]) {
                $terr[] = $cr["err"];
            }
        }
        if (count($terr) > 0) {
            return '[' . implode("]\n[", $terr) . ']';
        } else {
            return '';
        }
    }
}
