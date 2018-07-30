<?php


namespace Anakeen\Core\Internal;

use Anakeen\Core\SmartStructure\ExportConfiguration;
use Dcp\Exception;
use SmartStructure\Fields\Fieldaccesslayer as FalFields;
use SmartStructure\Fields\Fieldaccesslayerlist as FallFields;

class ImportSmartConfiguration
{
    /**
     * @var \DOMDocument $dom ;
     */
    protected $dom;
    protected $verbose = false;
    protected $profilElements = [];


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

    /**
     * @param bool $verbose
     *
     * @return ImportSmartConfiguration
     */
    public function setVerbose(bool $verbose)
    {
        $this->verbose = $verbose;
        return $this;
    }

    protected function importSmartStructureConfigurations()
    {
        $configs = $this->getNodes($this->dom->documentElement, "structure-configuration");
        $data = [];
        foreach ($configs as $config) {
            $data = array_merge($data, $this->importSmartStructureConfig($config));
        }

        $data = array_merge($data, $this->extractEnumConfig($this->dom->documentElement));
        $accessConfigs = $this->getNodes($this->dom->documentElement, "access-configuration");

        foreach ($accessConfigs as $config) {
            $data = array_merge($data, $this->importSmartAccessConfig($config));
        }

        $this->importFieldAccessElements(); // set data un profilElements attribute

        $data = array_merge($this->profilElements, $data);
        if ($this->verbose) {
            $this->print($data);
        }

        $this->recordSmartData($data);
        return $data;
    }

    public function print($data)
    {
        foreach ($data as $line) {
            foreach ($line as $item) {
                printf(" , %-20s", str_replace("\n", " ", print_r($item, true)));
            }
            printf("\n");
        }
    }

    protected function importFieldAccessElements()
    {
        $layers = $this->getNodes($this->dom->documentElement, "field-access-layer");
        foreach ($layers as $layer) {
            /** @var \DOMElement $layer */
            $name = $layer->getAttribute("name");
            if ($name) {
                $this->addFieldLayer($layer);
            }
        }

        $layerLists = $this->getNodes($this->dom->documentElement, "field-access-layer-list");
        foreach ($layerLists as $layerList) {
            /** @var \DOMElement $layerList */
            $layers = $this->getNodes($layerList, "field-access-layer");
            $layerNameList = $layerAccessList = [];
            foreach ($layers as $layer) {
                /** @var \DOMElement $layer */
                $name = $layer->getAttribute("name");
                if (!$name) {
                    $name = $layer->getAttribute("ref");
                }
                if ($name) {
                    $layerNameList[] = $name;
                    $layerAccessList[] = $layer->getAttribute("access-name");
                }
            }
            $prfType = "FIELDACCESSLAYERLIST";
            $prfName = $layerList->getAttribute("name");
            $prfLabel = $layerList->getAttribute("label");
            $prfDesc = $this->getDescription($layerList);
            $this->profilElements[] = ["ORDER", $prfType, "", "", FallFields::ba_title, FallFields::ba_desc, FallFields::fall_layer, FallFields::fall_aclname];
            $this->profilElements[] = [
                "DOC",
                $prfType,
                $prfName,
                "-",
                $prfLabel,
                $prfDesc,
                $layerNameList,
                $layerAccessList
            ];
        }
    }

    protected function getDescription(\DOMElement $node)
    {
        $desc = "";
        foreach ($node->childNodes as $attrNode) {
            if (!is_a($attrNode, \DOMElement::class)) {
                continue;
            }
            if ($attrNode->tagName === "smart:description") {
                /* @var \DOMElement $attrNode ; */
                $desc .= $attrNode->nodeValue;
            }
        }
        return $desc;
    }

    protected function addFieldLayer(\DOMElement $config)
    {

        $prfType = "FIELDACCESSLAYER";
        $prfDEsc = $this->getDescription($config);

        $prfName = $config->getAttribute("name");
        $prfLabel = $config->getAttribute("label");
        $fas = $this->getNodes($config, "field-access");

        $fieldId = [];
        $fieldAccess = [];
        foreach ($fas as $fa) {
            /* @var \DOMElement $fa ; */
            $fieldId[] = $fa->getAttribute("field");
            $fieldAccess[] = $fa->getAttribute("access");
        }

        $this->profilElements[] = ["ORDER", $prfType, "", "", FalFields::fal_title, FalFields::fal_desc, FalFields::fal_fieldid, FalFields::fal_fieldaccess];
        $this->profilElements[] = [
            "DOC",
            $prfType,
            $prfName,
            "-",
            $prfLabel,
            $prfDEsc,
            $fieldId,
            $fieldAccess
        ];
    }

    protected function importSmartAccessConfig(\DOMElement $config)
    {
        $data = [];
        $prfName = $config->getAttribute("name");
        $prfReset = $config->getAttribute("policy");
        $prfLabel = $config->getAttribute("label");
        if ($config->hasAttribute("access-structure")) {
            $prfDynamic = $config->getAttribute("access-structure");
            if ($prfDynamic === "null") {
                // Explicit deletion
                $prfDynamic = " ";
            }
        } else {
            $prfDynamic = null;
        }
        $prfLink = $config->getAttribute("ref");
        $prfType = $config->getAttribute("profil-type");

        $prfDEsc = $this->getDescription($config);

        if ($prfName && (($prfLabel && !$prfLink) || $prfDynamic)) {
            if (!$prfType) {
                $prfType = "PDOC";
            }
            $this->profilElements[] = ["ORDER", $prfType, "", "", "ba_title", "ba_desc", "dpdoc_famid"];
            $this->profilElements[] = ["DOC", $prfType, $prfName, "-", $prfLabel, $prfDEsc, $prfDynamic];
        } elseif ($prfName && $prfLink) {
            $data[] = ["PROFIL", $prfName, $prfLink];
        }
        $accesses = $this->getNodes($config, "element-access");
        $prfData = [];
        foreach ($accesses as $access) {
            /**
             * @var \DOMElement $access
             */
            if ($access->getAttribute("account")) {
                $prfData[] = sprintf("%s=account(%s)", $access->getAttribute("access"), $access->getAttribute("account"));
            }
            if ($access->getAttribute("field")) {
                $prfData[] = sprintf("%s=attribute(%s)", $access->getAttribute("access"), $access->getAttribute("field"));
            }
            if ($access->getAttribute("element")) {
                $prfData[] = sprintf("%s=document(%s)", $access->getAttribute("access"), $access->getAttribute("element"));
            }
        }
        if ($prfData) {
            $data[] = array_merge(["PROFIL", $prfName, "", $prfReset], $prfData);
        }

        return $data;
    }

    protected function importSmartStructureConfig(\DOMElement $config)
    {
        $data[] = $this->extractBegin($config);
        $data = array_merge($data, $this->extractProps($config));
        $data = array_merge($data, $this->extractAttrs($config));
        $data = array_merge($data, $this->extractParams($config));
        $data = array_merge($data, $this->extractDefaults($config));

        $data = array_merge($data, $this->extractModAttrs($config));
        $data = array_merge($data, $this->extractAloneHooks($config));
        $data[] = ["END"];

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

    protected function recordSmartData(array $data)
    {
        $import = new \ImportDocumentDescription();
        $import->analyzeOnly($this->onlyAnalyze);

        $this->cr = $import->importData($data);
    }

    protected function extractBegin(\DOMElement $config)
    {
        $data[0] = "BEGIN";
        // Inherit
        $extends = $this->getNode($config, "extends");
        if ($extends) {
            $data[1] = $extends->getAttribute("ref");
        } else {
            $data[1] = "";
        }
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
                if (preg_match('/smart:field-/', $attrNode->tagName) && $attrNode->tagName !== "smart:field-option") {
                    $data = array_merge($data, $this->extractAttr($attrNode, "PARAM"));
                }
            }
        }

        return $data;
    }


    protected function extractDefaults(\DOMElement $config)
    {
        $data = [];
        $nodeAttributes = $this->getNode($config, "defaults");
        if ($nodeAttributes) {
            foreach ($nodeAttributes->childNodes as $attrNode) {
                if (!is_a($attrNode, \DOMElement::class)) {
                    continue;
                }
                /**
                 * @var \DOMElement $attrNode
                 */
                if ($attrNode->tagName === "smart:default") {
                    $data[] = $this->extractDefault($attrNode, "DEFAULT");
                }
                if ($attrNode->tagName === "smart:initial") {
                    $data[] = $this->extractDefault($attrNode, "INITIAL");
                }
            }
        }

        return $data;
    }

    protected function extractAloneHooks(\DOMElement $config)
    {
        $data = [];

        // Search Constraint and Computed
        $autocompletes = $this->getNodes($config, "field-hook");
        foreach ($autocompletes as $hookNode) {
            /**
             * @var \DOMElement $hookNode
             */
            if ($hookNode->getAttribute("__used__") === "true") {
                continue;
            }

            $attr = new ImportSmartAttr();
            $attr->id = $hookNode->getAttribute("field");


            if ($hookNode->getAttribute("type") === "constraint") {
                $attr->constraint = $this->getCallableString($hookNode);
            }
            if ($hookNode->getAttribute("event") === "onPreRefresh") {
                $attr->phpfunc = $this->getCallableString($hookNode);
            }

            if ($hookNode->getAttribute("event") === "onPreRefresh") {
                $attr->phpfunc = $this->getCallableString($hookNode);
            }

            $data[] = $attr->getData("UPDTATTR");
        }

        // Search Autocomplete
        $autocompletes = $this->getNodes($config, "field-autocomplete");
        foreach ($autocompletes as $autoNode) {

            /**
             * @var \DOMElement $autoNode
             */
            if ($autoNode->getAttribute("__used__") === "true") {
                continue;
            }

            $attr = new ImportSmartAttr();
            $attr->id = $autoNode->getAttribute("field");

            $attr->autocomplete = $this->getCallableString($autoNode);

            $data[] = $attr->getData("UPDTATTR");
        }
        return $data;
    }

    protected function extractModAttrs(\DOMElement $config)
    {
        $data = [];
        $modAttrs = $this->getNodes($config, "field-override");

        foreach ($modAttrs as $attrNode) {

            /**
             * @var \DOMElement $attrNode
             */

            $attr = new ImportSmartAttr();
            $attr->id = $attrNode->getAttribute("field");
            $attr->label = $attrNode->getAttribute("label");
            $attr->idfield = $attrNode->getAttribute("fieldset");
            $attr->access = $attrNode->getAttribute("access");
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


            list($attr->autocomplete, $attr->phpfile) = $this->extractAttrAutoComplete($attrNode, function (\DOMElement $e) {
                return true;
            });
            if ($attr->phpfile && !$attr->phpfunc) {
                // For compatibility on old autocomplete
                $attr->phpfunc = $attr->autocomplete;
                $attr->autocomplete = "";
            }


            $attr->option = $this->extractAttrOptions($attrNode);

            $data[] = $attr->getData("MODATTR");
        }

        return $data;
    }

    protected function extractAttrs(\DOMElement $config)
    {
        $data = [];
        $nodeAttributes = $this->getNode($config, "fields");
        if ($nodeAttributes) {
            foreach ($nodeAttributes->childNodes as $attrNode) {
                if (!is_a($attrNode, \DOMElement::class)) {
                    continue;
                }
                /**
                 * @var \DOMElement $attrNode
                 */
                if (preg_match('/smart:field-/', $attrNode->tagName) && $attrNode->tagName !== "smart:field-option") {
                    $data = array_merge($data, $this->extractAttr($attrNode, "ATTR"));
                }
            }
        }

        return $data;
    }


    protected function extractDefault(\DOMElement $attrNode, $key)
    {
        $data = [$key];

        $nodeValue = trim($attrNode->nodeValue);
        $data[1] = $attrNode->getAttribute("field");
        if ($nodeValue !== "") {
            $data[2] = $nodeValue;
        } else {
            $data[2] = $this->getCallableString($attrNode);
        }
        $reset = $attrNode->getAttribute("reset");
        if ($reset === "true") {
            $data[3] = "force=yes";
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
            if ($enumConfig->getAttribute("extendable") !== "true") {
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
        if ($attrNode->tagName === "smart:field-set") {
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
                if (preg_match('/smart:field-/', $childNode->tagName) && $childNode->tagName !== "smart:field-option") {
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

        if ($attrNode->tagName === "smart:field-set") {
            $attr->type = $attrNode->getAttribute("type");
        } else {
            $attr->type = substr($attrNode->tagName, strlen("smart:field-"));
            $rel = $attrNode->getAttribute("relation");
            if ($rel) {
                $attr->type .= '("' . $rel . '")';
            }
        }
        $attr->label = $attrNode->getAttribute("label");
        $attr->idfield = $fieldName;
        $attr->access = $attrNode->getAttribute("access");
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
        if ($attr->phpfile && !$attr->phpfunc) {
            // For compatibility on old autocomplete
            $attr->phpfunc = $attr->autocomplete;
            $attr->autocomplete = "";
        }

        $attr->option = $this->extractAttrOptions($attrNode);
        $data = $attr->getData($key);
        return $data;
    }

    protected function extractAttrAutoComplete(\DOMElement $attrNode, \Closure $filter)
    {
        $config = $this->getClosest($attrNode, "structure-configuration");
        $attrid = $attrNode->getAttribute("name");
        $hooks = $this->getNodes($config, "field-autocomplete");
        $method = "";
        $file = "";

        /**
         * @var \DOMElement $hook
         */
        foreach ($hooks as $hook) {
            if ($hook->getAttribute("field") === $attrid) {
                if ($filter($hook)) {
                    $method = $this->getCallableString($hook);
                    $callable = $this->getNode($hook, "field-callable");
                    $file = $callable->getAttribute("external-file");
                    $hook->setAttribute("__used__", "true");
                }
            }
        }
        return [$method, $file];
    }

    protected function extractAttrHooks(\DOMElement $attrNode, \Closure $filter)
    {
        $config = $this->getClosest($attrNode, "structure-configuration");

        $attrid = $attrNode->getAttribute("name");
        $hooks = $this->getNodes($config, "field-hook");
        $method = "";
        /**
         * @var \DOMElement $hook
         */
        foreach ($hooks as $hook) {
            if ($hook->getAttribute("field") === $attrid) {
                if ($filter($hook)) {
                    $method = $this->getCallableString($hook);
                    // Add special attribute in case of hook declaration is outside attr declaration
                    $hook->setAttribute("__used__", "true");
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
        if ($attrNode->getAttribute("multiple")) {
            $optRaw[] = sprintf("multiple=%s", ($attrNode->getAttribute("multiple") === "true") ? "yes" : "no");
        }

        foreach ($attrNode->childNodes as $optNode) {
            /**
             * @var \DOMElement $optNode
             */
            if (!is_a($optNode, \DOMElement::class) || $optNode->tagName !== "smart:field-option") {
                continue;
            }
            $optData[$optNode->getAttribute("name")] = $optNode->getAttribute("name");

            $optRaw[] = sprintf("%s=%s", $optNode->getAttribute("name"), $optNode->nodeValue);
        }
        return implode("|", $optRaw);
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


        $node = $this->getNode($config, "structure-access-configuration");
        if ($node && $node->getAttribute("ref")) {
            $data[] = ["PROFID", $node->getAttribute("ref")];
        }

        $node = $this->getNode($config, "element-access-configuration");
        if ($node && $node->getAttribute("ref")) {
            $data[] = ["CPROFID", $node->getAttribute("ref")];
        }
        $node = $this->getNode($config, "field-access-configuration");
        if ($node && $node->getAttribute("ref")) {
            $data[] = ["CFALLID", $node->getAttribute("ref")];
        }

        return $data;
    }

    /**
     * @param string      $name
     * @param \DOMElement $e
     *
     * @return \DOMNodeList
     */
    private function getNodes(\DOMElement $e, $name)
    {
        return $e->getElementsByTagNameNS(ExportConfiguration::NSURL, $name);
    }

    /**
     * @param \DOMElement $e
     * @param string      $name
     *
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
     *
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
     *
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
     *
     * @return string
     */
    protected function getCallableString(\DOMElement $hook): string
    {
        $callableNode = $this->getNode($hook, "field-callable");
        if (!$callableNode) {
            throw new Exception(sprintf("Error in callable %s", $hook->getAttribute("field")));
        }
        $method = $callableNode->getAttribute("function") . "(";
        $argNodes = $this->getNodes($hook, "field-argument");
        $args = [];
        /**
         * @var  \DOMElement $argNode
         */
        foreach ($argNodes as $argNode) {
            $type = $argNode->getAttribute("type");
            $name = $argNode->getAttribute("name");
            $arg = $argNode->nodeValue;
            if ($type === "string") {
                // Escape quote
                $arg = '"' . str_replace('"', '\\"', $arg) . '"';
            }
            if ($name) {
                $arg = sprintf("{%s}%s", $name, $arg);
            }
            $args[] = $arg;
        }
        $method .= implode(",", $args);

        $method .= ')';


        $returnNodes = $this->getNodes($hook, "field-return");
        $returns = [];
        /**
         * @var  \DOMElement $returnNode
         */
        foreach ($returnNodes as $returnNode) {
            $attridreturn = $returnNode->getAttribute("field");
            if ($attridreturn) {
                $returns[] = strtolower($attridreturn);
            }
        }

        if ($returns) {
            $method .= ":" . implode(",", $returns);
        }
        return $method;
    }
}
