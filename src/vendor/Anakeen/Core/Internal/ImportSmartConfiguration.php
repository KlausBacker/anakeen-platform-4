<?php


namespace Anakeen\Core\Internal;

use Anakeen\Core\SmartStructure\ExportConfiguration;
use Dcp\Exception;

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
        $data = [];
        foreach ($configs as $config) {
            $data = array_merge($data, $this->importSmartStructureConfig($config));
            //  $this->print($data);
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

        $data = array_merge($data, $this->extractModAttrs($config));
        $data = array_merge($data, $this->extractEnumConfig($this->dom->documentElement));
        $data[] = ["END"];

        $this->importSmartData($data);

        if ($this->getError()) {
            throw new Exception($this->getError());
        }

        return $data;
    }

    protected function getError()
    {
        foreach ($this->cr as $cr) {
            if ($cr["err"]) {
                return $cr["err"];
            }
        }
        return "";
    }

    protected function importSmartData(array $data)
    {
        $import = new \ImportDocumentDescription();

        $this->cr = $import->importData($data);

    }

    protected function extractBegin(\DOMElement $config)
    {
        $data[0] = "BEGIN";
        // Inherit
        $data[1] = $config->getAttribute("extends");
        // Label
        $data[2] = $config->getAttribute("label");
        // Id not used
        $data[3] = $config->getAttribute("id");
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

    protected function extractModAttrs(\DOMElement $config)
    {
        $data = [];
        $modAttrs = $this->getNodes($config, "attr-override");

        foreach ($modAttrs as $attrNode) {

            /**
             * @var \DOMElement $attrNode
             */

            $attr = new ImportSmartAttr();
            $attr->id = $attrNode->getAttribute("attr");
            $attr->label = $attrNode->getAttribute("label");
            $attr->idfield = $attrNode->getAttribute("fieldset");
            $attr->visibility = $attrNode->getAttribute("visibility");
            $attr->link = $attrNode->getAttribute("link");
            if ($attrNode->getAttribute("needed")) {
                $attr->need = ($attrNode->getAttribute("needed") === "true") ? "Y" : "N";
            }
            if ($attrNode->getAttribute("is-abstract")) {
                $attr->isAbstract = ($attrNode->getAttribute("is-abstract") === "true") ? "Y" : "N";
            }

            if ($attrNode->getAttribute("is-title")) {
                $attr->isTitle = ($attrNode->getAttribute("is-title") === "true") ? "Y" : "N";
            }
            $attr->order = $attrNode->getAttribute("insert-after");

            $attr->constraint = $this->extractAttrHooks($attrNode, function (\DOMElement $e) {
                return $e->getAttribute("type") === "constraint";
            });
            $attr->phpfunc = $this->extractAttrHooks($attrNode, function (\DOMElement $e) {
                return $e->getAttribute("event") === "onPreRefresh";
            });
            // For compatibility on old autocomplete
            if (!$attr->phpfunc) {
                list($attr->phpfunc, $attr->phpfile) = $this->extractAttrAutoComplete($attrNode, function (\DOMElement $e) {
                    return true;
                });
            }


            $attr->option = $this->extractAttrOptions($attrNode);

            $data[] = $attr->getData("MODATTR");
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

    protected function extractEnumConfig(\DOMElement $attrNode)
    {
        $data = [];
        $enumConfigs = $this->getNodes($attrNode, "enum-configuration");

        foreach ($enumConfigs as $enumConfig) {
            /**
             * @var \DOMElement $enumConfig
             */
            if ($enumConfig->getAttribute("extendable") === "false") {
                $data[] = ["RESET", "enums", $enumConfig->getAttribute("name")];
            }
            $data = array_merge($data, $this->extractEnum($enumConfig, $enumConfig->getAttribute("name")));
        }

        return $data;
    }


    protected function extractEnum(\DOMElement $enumConfig, $enumName, $parentKey = "")
    {
        $data = [];

        foreach ($enumConfig->childNodes as $enumNode) {
            /**
             * @var \DOMElement $enumNode
             */
            if (!is_a($enumNode, \DOMElement::class) || $enumNode->tagName !== "smart:enum") {
                continue;
            }
            $data[] = [
                0 => "ENUM",
                "name" => $enumName,
                "key" => $enumNode->getAttribute("name"),
                "label" => $enumNode->getAttribute("label"),
                "parentKey" => $parentKey

            ];
            $data = array_merge($data, $this->extractEnum($enumNode, $enumName, $enumNode->getAttribute("name")));
        }

        return $data;
    }

    protected function extractAttr(\DOMElement $attrNode, $key, $fieldName = "")
    {
        $data = [];
        if ($attrNode->tagName === "smart:attr-fieldset") {
            if ($attrNode->getAttribute("extended") !== "true") {
                $data[] = $this->extractSingleAttr($attrNode, $key, $fieldName);
            }
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
        $attr->id = $attrNode->getAttribute("name");

        if ($attrNode->tagName === "smart:attr-fieldset") {
            $attr->type = $attrNode->getAttribute("type");
        } else {
            $attr->type = substr($attrNode->tagName, strlen("smart:attr-"));
            $rel = $attrNode->getAttribute("relation");
            if ($rel) {
                $attr->type .= '("' . $rel . '")';
            }
        }
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

        list($attr->autocomplete, $attr->phpfile) = $this->extractAttrAutoComplete($attrNode, function (\DOMElement $e) {
            return true;
        });


        $attr->option = $this->extractAttrOptions($attrNode);

        $data = $attr->getData($key);

        return $data;
    }

    protected function extractAttrAutoComplete(\DOMElement $attrNode, \Closure $filter)
    {
        $config = $this->getClosest($attrNode, "structure-configuration");

        $attrid = $attrNode->getAttribute("name");
        $hooks = $this->getNodes($config, "attr-autocomplete");
        $method = "";
        $file = "";

        /**
         * @var \DOMElement $hook
         */
        foreach ($hooks as $hook) {
            if ($hook->getAttribute("attr") === $attrid) {
                if ($filter($hook)) {
                    $method = $this->getCallableString($hook);
                    $callable = $this->getNode($hook, "attr-callable");
                    $file = $callable->getAttribute("external-file");
                }
            }
        }
        return [$method, $file];
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
                    $method = $this->getCallableString($hook);
                }
            }
        }
        return $method;
    }

    protected function extractAttrOptions(\DOMElement $attrNode)
    {
        $optData = [];
        /**
         * @TODO to delete no need use flat notation
         */
        $optRaw = [];
        foreach ($attrNode->childNodes as $optNode) {
            /**
             * @var \DOMElement $optNode
             */
            if (!is_a($optNode, \DOMElement::class) || $optNode->tagName !== "smart:attr-option") {
                continue;
            }
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
            $data[] = [
                "CLASS",
                $node->nodeValue,
                ($node->getAttribute("disable-inheritance-condition") === "true") ? "disableInheritanceCondition" : ""
            ];
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

    /**
     * @param $hook
     * @return string
     */
    protected function getCallableString($hook): string
    {
        $callableNode = $this->getNode($hook, "attr-callable");
        $method = $callableNode->getAttribute("function") . "(";
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
            $attridreturn = $returnNode->getAttribute("attr");
            $returns[] = strtolower($attridreturn);
        }
        if ($returns) {
            $method .= ":" . implode(",", $returns);
        }
        return $method;
    }
}
