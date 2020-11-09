<?php

namespace Anakeen\Core\Internal;

use Anakeen\Core\AccountManager;
use Anakeen\Core\EnumManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\ExportConfiguration;
use Anakeen\Core\Utils\Xml;
use Anakeen\Exception;
use Anakeen\Exchange\ExportSearch;
use SmartStructure\Dsearch;
use SmartStructure\Fields\Fieldaccesslayer as FalFields;
use SmartStructure\Fields\Fieldaccesslayerlist as FallFields;
use SmartStructure\Fields\Task as TaskFields;
use SmartStructure\Fields\Report as ReportFields;
use SmartStructure\Report;

class ImportSmartConfiguration
{
    /**
     * @var \DOMDocument $dom ;
     */
    protected $dom;
    protected $verbose = false;
    protected $profilElements = [];
    protected $smartPrefix = "smart";
    protected $taskPrefix = "task";
    protected $smartNs = ExportConfiguration::NSURL;

    protected $attrToOptions = [
        "match" => "match",
        "group" => "group",
        "role" => "role"
    ];
    /**
     * @var array
     */
    protected $verboseMessages = [];
    /**
     * @var array
     */
    protected $debugData = [];
    /**
     * @var string
     */
    protected $searchPrefix;


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

        if (!Xml::getPrefix($this->dom, $this->smartNs)) {
            throw new Exception(sprintf('Xml Configuration file "%s" is not a smart configuration file', $xmlFile));
        }

        $this->cr = [];
        $this->debugData = [];
        $this->importConfigurations();
    }

    public function importAll($xmlFile)
    {
        $this->import($xmlFile);
        $data = $this->importTasks();
        $this->recordSmartData($data);
        $data = $this->importSearches();
        $this->recordSmartData($data);
    }

    protected function importTasks()
    {
        $this->taskPrefix = Xml::getPrefix($this->dom, ExportConfiguration::NSTASKURL);
        $configs = $this->getTaskNodes($this->dom->documentElement, "task");
        $data = [];
        foreach ($configs as $config) {
            $data = array_merge($data, $this->importTask($config));
        }
        return $data;
    }

    protected function importSearches()
    {
        $this->searchPrefix = Xml::getPrefix($this->dom, ExportSearch::NSSURL);
        $configs = $this->getSearchNodes($this->dom->documentElement, "search");
        $data = [];
        foreach ($configs as $config) {
            $data = array_merge($data, $this->importSearch($config));
        }
        return $data;
    }

    /**
     * @param string $name
     * @param \DOMElement $e
     *
     * @return \DOMNodeList
     */
    protected function getTaskNodes(\DOMElement $e, $name)
    {
        return $e->getElementsByTagNameNS(ExportConfiguration::NSTASKURL, $name);
    }

    /**
     * @param string $name
     * @param \DOMElement $e
     *
     * @return \DOMNodeList
     */
    protected function getSearchNodes(\DOMElement $e, $name)
    {
        return $e->getElementsByTagNameNS(ExportSearch::NSSURL, $name);
    }

    protected function importSearch(\DOMElement $searchNode)
    {
        $type = $searchNode->getAttribute("structure-type");
        if (!$type) {
            throw new Exception("Cannot import search without structure-type");
        }
        $name = $searchNode->getAttribute("name");
        if (!$name) {
            throw new Exception("Cannot import search without name");
        }
        $search = SEManager::getDocument($name);
        if (!$search) {
            $search = SEManager::createDocument($type, false);
            $search->name = $name;
        }
        $search->name = $name;
        $SP = $this->searchPrefix;

        $this->setEltValue($search, $searchNode->getAttribute("title"), ReportFields::ba_title);


        $userLogin = $this->evaluate($searchNode, "string($SP:author/@login)");
        if ($userLogin) {
            $user = AccountManager::getAccount($userLogin);
            if (!$user) {
                throw new Exception(sprintf("Search author:login \"%s\" not exists", $userLogin));
            }
            $search->setValue(ReportFields::se_author, $user->fid);
        }

        $this->setEltValue(
            $search,
            $this->evaluate($searchNode, "string($SP:criteria/$SP:keyword)"),
            ReportFields::se_key
        );
        $revision = $this->evaluate($searchNode, "string($SP:criteria/$SP:revision)");
        switch ($revision) {
            case "all":
                $search->setValue(ReportFields::se_latest, "no");
                break;
            case "latest-fixed":
                $search->setValue(ReportFields::se_latest, "fixed");
                break;
            case "fixed":
                $search->setValue(ReportFields::se_latest, "allfixed");
                break;
            case "distinct-fixed":
                $search->setValue(ReportFields::se_latest, "lastfixed");
                break;
            case "latest":
            default:
                $search->setValue(ReportFields::se_latest, "yes");
        }


        $this->setEltValue(
            $search,
            $this->evaluate($searchNode, "string($SP:criteria/$SP:keyword/@case-sensitive)") === "true" ? "yes" : "no",
            ReportFields::se_case
        );

        $this->setEltValue(
            $search,
            $this->evaluate($searchNode, "string($SP:criteria/$SP:structure/@ref)"),
            ReportFields::se_famid
        );
        $this->setEltValue(
            $search,
            $this->evaluate($searchNode, "string($SP:criteria/$SP:order-by)"),
            ReportFields::se_orderby
        );

        $this->setEltValue(
            $search,
            $this->evaluate($searchNode, "string($SP:criteria/$SP:structure/@only)") === "true" ? "yes" : "no",
            ReportFields::se_famonly
        );

        $this->setEltValue(
            $search,
            $this->evaluate($searchNode, "string($SP:criteria/$SP:search-deleted)"),
            ReportFields::se_trash
        );

        $sqlSelect = $this->evaluate($searchNode, "string($SP:criteria/$SP:sql-query)");
        if ($sqlSelect) {
            $search->setValue(ReportFields::se_static, "1");
            $search->setValue(ReportFields::se_sqlselect, $sqlSelect);
        }

        $ol = $this->evaluate($searchNode, "string($SP:criteria/$SP:query-filters/@logical-operator)");
        $search->setValue(ReportFields::se_ol, $ol ?: "perso");

        $permFilterNodes = $this->evaluate($searchNode, "$SP:criteria/$SP:permission-filter");
        $permFilter = [];
        foreach ($permFilterNodes as $permFilterNode) {
            $permFilter[] = $permFilterNode->nodeValue;
        }
        if ($permFilter) {
            $search->setValue(ReportFields::se_acl, $permFilter);
        }

        if (is_a($search, Dsearch::class)) {
            // -----------------------
            // --- DSEARCH Part -------
            /** @var \DOMNodeList $filterNodes */
            $filterNodes = $this->evaluate($searchNode, "$SP:criteria/$SP:query-filters");
            if ($filterNodes->length > 0) {
                $search->clearArrayValues(ReportFields::se_t_detail);
                /** @var \DOMElement $filterNode */
                $filterNode = $filterNodes[0];

                $emptyFilter = [
                    ReportFields::se_leftp => "no",
                    ReportFields::se_attrids => "",
                    ReportFields::se_ols => "",
                    ReportFields::se_funcs => "",
                    ReportFields::se_keys => "",
                    ReportFields::se_rightp => "no",
                ];
                $currentFilter = $emptyFilter;
                foreach ($filterNode->childNodes as $filterPartNode) {
                    if (is_a($filterPartNode, \DOMElement::class)) {
                        $tag = $filterPartNode->tagName;
                        if ($ol) {
                            if ($tag === "$SP:filter") {
                                $search->addArrayRow(
                                    ReportFields::se_t_detail,
                                    [
                                        ReportFields::se_ols => "",
                                        ReportFields::se_leftp => "",
                                        ReportFields::se_attrids => $filterPartNode->getAttribute("field"),
                                        ReportFields::se_funcs => $filterPartNode->getAttribute("operator"),
                                        ReportFields::se_keys => $filterPartNode->getAttribute("value"),
                                        ReportFields::se_rightp => "",
                                    ]
                                );
                            }
                        } else {
                            switch ($tag) {
                                case "$SP:start-parenthesis":
                                    $currentFilter[ReportFields::se_leftp] = "yes";
                                    break;
                                case "$SP:end-parenthesis":
                                    $currentFilter[ReportFields::se_rightp] = "yes";
                                    break;
                                case "$SP:logication-operator":
                                    $search->addArrayRow(ReportFields::se_t_detail, $currentFilter);
                                    $currentFilter = $emptyFilter;
                                    $currentFilter[ReportFields::se_ols] = $filterPartNode->nodeValue;
                                    break;
                                case "$SP:filter":
                                    if (!empty($currentFilter[ReportFields::se_attrids])) {
                                        throw new Exception("Cannot import serach : Error in filter - missing logication-operator");
                                    }
                                    $currentFilter[ReportFields::se_attrids] = $filterPartNode->getAttribute("field");
                                    $currentFilter[ReportFields::se_funcs] = $filterPartNode->getAttribute("operator");
                                    $currentFilter[ReportFields::se_keys] = $filterPartNode->getAttribute("value");
                                    break;
                            }
                        }
                    }
                }
                if (!$ol) {
                    $search->addArrayRow(ReportFields::se_t_detail, $currentFilter);
                }
            }
        }
        if (is_a($search, Report::class)) {
            // -----------------------
            // --- REPORT Part -------
            $this->setEltValue(
                $search,
                $this->evaluate($searchNode, "string($SP:report-configuration/$SP:caption)"),
                ReportFields::rep_caption
            );
            $this->setEltValue(
                $search,
                $this->evaluate($searchNode, "string($SP:report-configuration/$SP:sort/@order-by)"),
                ReportFields::rep_idsort
            );
            $this->setEltValue(
                $search,
                $this->evaluate($searchNode, "string($SP:report-configuration/$SP:sort/@direction)"),
                ReportFields::rep_ordersort
            );
            $this->setEltValue(
                $search,
                $this->evaluate($searchNode, "string($SP:report-configuration/$SP:result-limit)"),
                ReportFields::rep_limit
            );

            $columnNodes = $this->evaluate($searchNode, "$SP:report-configuration/$SP:columns/$SP:column");

            $search->clearArrayValues(ReportFields::rep_tcols);
            foreach ($columnNodes as $columnNode) {
                /** @var \DOMElement $columnNode */
                $search->addArrayRow(
                    ReportFields::rep_tcols,
                    [
                        ReportFields::rep_idcols => $columnNode->getAttribute("field"),
                        ReportFields::rep_displayoption => $columnNode->getAttribute("display-option"),
                        ReportFields::rep_foots => strtoupper($columnNode->getAttribute("footer")),
                    ]
                );
            }
        }
        /*
        $args = $this->evaluate($searchNode, "({$this->taskPrefix}:route/{$this->taskPrefix}:argument)");

        foreach ($args as $arg) {
            $search->addArrayRow(
                TaskFields::task_t_args,
                [
                    TaskFields::task_arg_name => $arg->getAttribute("name"),
                    TaskFields::task_arg_value => $arg->nodeValue
                ]
            );
        }*/


        return $this->getElementdata($search);
    }

    protected function importTask(\DOMElement $taskNode)
    {
        $name = $taskNode->getAttribute("name");
        if (!$name) {
            throw new Exception("Cannot import task without name");
        }
        $task = SEManager::getDocument($name);
        if (!$task) {
            $task = SEManager::createDocument("TASK", false);
            $task->name = $name;
        }

        $this->setEltValue($task, $taskNode->getAttribute("label"), TaskFields::task_title);
        $this->setEltValue(
            $task,
            $this->evaluate($taskNode, "string({$this->taskPrefix}:description)"),
            TaskFields::task_desc
        );
        $this->setEltValue(
            $task,
            $this->evaluate($taskNode, "string({$this->taskPrefix}:crontab)"),
            TaskFields::task_crontab
        );
        $this->setEltValue(
            $task,
            $this->evaluate($taskNode, "string({$this->taskPrefix}:status)"),
            TaskFields::task_status
        );


        $this->setEltValue(
            $task,
            $this->evaluate($taskNode, "string({$this->taskPrefix}:route/@ns)"),
            TaskFields::task_route_ns
        );
        $this->setEltValue(
            $task,
            $this->evaluate($taskNode, "string({$this->taskPrefix}:route/@ref)"),
            TaskFields::task_route_name
        );
        $this->setEltValue(
            $task,
            $this->evaluate($taskNode, "string({$this->taskPrefix}:route/@method)"),
            TaskFields::task_route_method
        );

        $args = $this->evaluate($taskNode, "({$this->taskPrefix}:route/{$this->taskPrefix}:argument)");
        /** @var \DOMElement $arg */
        foreach ($args as $arg) {
            $task->addArrayRow(
                TaskFields::task_t_args,
                [
                    TaskFields::task_arg_name => $arg->getAttribute("name"),
                    TaskFields::task_arg_value => $arg->nodeValue
                ]
            );
        }

        $args = $this->evaluate($taskNode, "({$this->taskPrefix}:route/{$this->taskPrefix}:query-field)");
        /** @var \DOMElement $arg */
        foreach ($args as $arg) {
            $task->addArrayRow(
                TaskFields::task_t_queryfield,
                [
                    TaskFields::task_queryfield_name => $arg->getAttribute("name"),
                    TaskFields::task_queryfield_value => $arg->nodeValue
                ]
            );
        }

        $userLogin = $this->evaluate($taskNode, "string({$this->taskPrefix}:user/@login)");
        if ($userLogin) {
            $user = AccountManager::getAccount($userLogin);
            if (!$user) {
                throw new Exception(sprintf("Task user:login \"%s\" not exists", $userLogin));
            }
            $task->setValue(TaskFields::task_iduser, $user->fid);
        }

        return $this->getElementdata($task);
    }

    /**
     * @return array
     */
    public function getDebugData(): array
    {
        return $this->debugData;
    }

    protected function getElementdata(SmartElement $elt)
    {
        $values = $elt->getValues();
        $order = ["ORDER", $elt->fromname, "", ""];
        $data = ["DOC", $elt->fromname, $elt->name, ""];
        foreach ($values as $aid => $value) {
            if ($value !== "") {
                $order[] = $aid;
                $data[] = $value;
            }
        }

        return ([$order, $data]);
    }

    protected function evaluate(\DOMElement $e, $path)
    {
        $xpath = new \DOMXpath($this->dom);
        return $xpath->evaluate($path, $e);
    }

    protected function setEltValue(SmartElement $elt, $value, $fieldName)
    {
        $err = "";
        if ($value) {
            $err = $elt->setValue($fieldName, $value);
        }
        if ($err) {
            throw new Exception(sprintf('Unable to import %s for %s (value : %s)', $elt->getTitle(), $err, $value));
        }
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

    protected function importConfigurations()
    {
        $this->smartPrefix = Xml::getPrefix($this->dom, $this->smartNs);
        $configs = $this->getNodes($this->dom->documentElement, "structure-configuration");
        $data = [];
        $this->profilElements = [];
        $data = array_merge($data, $this->extractEnumConfig($this->dom->documentElement));
        foreach ($configs as $config) {
            $data = array_merge($data, $this->importSmartStructureConfig($config));
        }

        $accessConfigs = $this->getNodes($this->dom->documentElement, "access-configuration");

        foreach ($accessConfigs as $config) {
            $data = array_merge($data, $this->importSmartAccessConfig($config));
        }

        $this->importFieldAccessElements(); // set data un profilElements attribute

        $data = array_merge($this->profilElements, $data);


        $this->recordSmartData($data);
        return $data;
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
            $famid = $layerList->getAttribute("structure");

            $prfDesc = $this->getDescription($layerList);
            $this->profilElements[] = [
                "ORDER",
                $prfType,
                "",
                "",
                FallFields::ba_title,
                FallFields::ba_desc,
                FallFields::fall_layer,
                FallFields::fall_aclname,
                FallFields::fall_famid
            ];
            $this->profilElements[] = [
                "DOC",
                $prfType,
                $prfName,
                "-",
                $prfLabel,
                $prfDesc,
                $layerNameList,
                $layerAccessList,
                $famid
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
            if ($attrNode->tagName === "{$this->smartPrefix}:description") {
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
        $famid = $config->getAttribute("structure");
        $fas = $this->getNodes($config, "field-access");

        if (!$famid) {
            // Search in access list if not found itself
            $parent = $config->parentNode;
            if ($parent && $parent->tagName === '{$this->smartPrefix}:field-access-layer-list') {
                $famid = $parent->getAttribute("structure");
            }
        }

        $fieldId = [];
        $fieldAccess = [];
        foreach ($fas as $fa) {
            /* @var \DOMElement $fa ; */
            $fieldId[] = $fa->getAttribute("field");
            $fieldAccess[] = $fa->getAttribute("access");
        }

        $this->profilElements[] = [
            "ORDER",
            $prfType,
            "",
            "",
            FalFields::fal_title,
            FalFields::fal_desc,
            FalFields::fal_fieldid,
            FalFields::fal_fieldaccess,
            FalFields::fal_famid
        ];
        $this->profilElements[] = [
            "DOC",
            $prfType,
            $prfName,
            "-",
            $prfLabel,
            $prfDEsc,
            $fieldId,
            $fieldAccess,
            $famid
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

        if ($prfName && ($prfType === 'PFAM') && !$prfLabel) {
            $prfLabel = sprintf('Profile %s', $prfName);
        }

        if ($prfName && (($prfLabel && !$prfLink) || $prfDynamic)) {
            if (!$prfType) {
                $prfType = "PDOC";
            }
            $dbName = SEManager::getIdFromName($prfName);
            if ($dbName && $prfDynamic) {
                $this->profilElements[] = ["ORDER", $prfType, "", "", "dpdoc_famid"];
                $this->profilElements[] = ["DOC", $prfType, $prfName, "-", $prfDynamic];
            } elseif (!$dbName || $prfDynamic) {
                $this->profilElements[] = ["ORDER", $prfType, "", "", "ba_title", "ba_desc", "dpdoc_famid"];
                $this->profilElements[] = ["DOC", $prfType, $prfName, "-", $prfLabel, $prfDEsc, $prfDynamic];
            }
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
                $prfData[] = sprintf(
                    "%s=account(%s)",
                    $access->getAttribute("access"),
                    $access->getAttribute("account")
                );
            }
            if ($access->getAttribute("field")) {
                $prfData[] = sprintf(
                    "%s=attribute(%s)",
                    $access->getAttribute("access"),
                    $access->getAttribute("field")
                );
            }
            if ($access->getAttribute("element")) {
                $prfData[] = sprintf(
                    "%s=document(%s)",
                    $access->getAttribute("access"),
                    $access->getAttribute("element")
                );
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
        if ($this->verbose) {
            $this->debugData[] = $data;
        }
        $import = new \Anakeen\Exchange\ImportDocumentDescription();
        $import->analyzeOnly($this->onlyAnalyze);
        $cr = $import->importData($data);

        $this->cr = array_merge($this->cr, $cr);
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
            $reset = $nodeAttributes->getAttribute("reset");
            if ($reset === "true") {
                $data[] = ["RESET", "parameters"];
            }
            foreach ($nodeAttributes->childNodes as $attrNode) {
                if (!is_a($attrNode, \DOMElement::class)) {
                    continue;
                }
                /**
                 * @var \DOMElement $attrNode
                 */
                if (preg_match(
                    "/{$this->smartPrefix}:field-/",
                    $attrNode->tagName
                ) && $attrNode->tagName !== "{$this->smartPrefix}:field-option") {
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
            $reset = $nodeAttributes->getAttribute("reset");
            if ($reset === "true") {
                $data[] = ["RESET", "default"];
            }
            foreach ($nodeAttributes->childNodes as $attrNode) {
                if (!is_a($attrNode, \DOMElement::class)) {
                    continue;
                }
                /**
                 * @var \DOMElement $attrNode
                 */
                if ($attrNode->tagName === "{$this->smartPrefix}:default") {
                    $data[] = $this->extractDefault($attrNode, "DEFAULT");
                }
                if ($attrNode->tagName === "{$this->smartPrefix}:initial") {
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
        $hookNodes = $this->getNodes($config, "field-hook");
        foreach ($hookNodes as $hookNode) {
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

            $attr->autocomplete = $this->getCallableString(
                $autoNode,
                ["id", "initid", "fromid", "title", "viewId"]
            );

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


            list($attr->autocomplete, $attr->phpfile) = $this->extractAttrAutoComplete($attrNode, function () {
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
            $reset = $nodeAttributes->getAttribute("reset");
            if ($reset === "true") {
                $data[] = ["RESET", "structure"];
            }
            foreach ($nodeAttributes->childNodes as $attrNode) {
                if (!is_a($attrNode, \DOMElement::class)) {
                    continue;
                }
                /**
                 * @var \DOMElement $attrNode
                 */
                if (preg_match(
                    "/{$this->smartPrefix}:field-/",
                    $attrNode->tagName
                ) && $attrNode->tagName !== "{$this->smartPrefix}:field-option") {
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
        $callsNodes = $this->getNodes($attrNode, "field-callable");


        $inherit = $attrNode->getAttribute("inherit");
        if ($inherit === "true") {
            $data[2] = null;
        } else {
            if ($callsNodes->length === 0) {
                $data[2] = $nodeValue;
            } else {
                $data[2] = $this->getCallableString($attrNode);
            }
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
            } else {
                $data[] = [
                    0 => "ENUM",
                    "name" => $enumConfig->getAttribute("name"),
                    "key" => EnumManager::EXTENDABLEKEY,
                    "label" => "",
                    "parentKey" => ""
                ];
            }
            $data = array_merge($data, $this->extractEnum($enumConfig, $enumConfig->getAttribute("name")));
        }

        return $data;
    }


    protected function extractEnum(\DOMElement $enumConfig, $enumName, $parentKey = "")
    {
        $data = [];

        $enumCall = $this->evaluate($enumConfig, "{$this->smartPrefix}:enum-callable");
        /** @var \DOMNodeList $enumCall */
        if ($enumCall->length === 1) {
            /** @var \DOMElement $item0 */
            $item0 = $enumCall->item(0);
            return $this->extractEnumCallable($item0, $enumName);
        }
        $enumItems = $this->evaluate($enumConfig, "{$this->smartPrefix}:enum");
        foreach ($enumItems as $enumNode) {
            /**
             * @var \DOMElement $enumNode
             */

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

    protected function extractEnumCallable(\DOMElement $attrNode, $enumName)
    {
        $data = [];
        $data[] = [
            0 => "ENUM",
            "name" => $enumName,
            "key" => $attrNode->getAttribute("function"),
            "label" => "",
            "parentKey" => EnumManager::CALLABLEKEY
        ];

        return $data;
    }

    protected function extractAttr(\DOMElement $attrNode, $key, $fieldName = "")
    {
        $data = [];
        if ($attrNode->tagName === "{$this->smartPrefix}:field-set") {
            if ($attrNode->getAttribute("extended") !== "true") {
                $data[] = $this->extractSingleAttr($attrNode, $key, $fieldName);
            } else {
                if ($attrNode->getAttribute("label") != "" ||
                    $attrNode->getAttribute("type") != "" ||
                    $attrNode->getAttribute("access") != "" || $attrNode->getAttribute("insert-after") != "") {
                    throw new \Anakeen\Exception("ATTR0106", $attrNode->getAttribute("name"));
                }
            }
            $fieldName = $attrNode->getAttribute("name");
            foreach ($attrNode->childNodes as $childNode) {
                if (!is_a($childNode, \DOMElement::class)) {
                    continue;
                }
                /**
                 * @var \DOMElement $childNode
                 */
                if (preg_match(
                    "/{$this->smartPrefix}:field-/",
                    $childNode->tagName
                ) && $childNode->tagName !== "{$this->smartPrefix}:field-option") {
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

        if ($attrNode->tagName === "{$this->smartPrefix}:field-set") {
            $attr->type = $attrNode->getAttribute("type");
        } else {
            $attr->type = substr($attrNode->tagName, strlen("{$this->smartPrefix}:field-"));
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

        list($attr->autocomplete, $attr->phpfile) = $this->extractAttrAutoComplete($attrNode, function () {
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
                    $method = $this->getCallableString($hook, ["id", "initid", "fromid", "title", "viewId"]);
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
        /**
         * @TODO to delete no need use flat notation
         */
        $optRaw = [];
        if ($attrNode->getAttribute("multiple")) {
            $optRaw[] = sprintf("multiple=%s", ($attrNode->getAttribute("multiple") === "true") ? "yes" : "no");
        }

        foreach ($this->attrToOptions as $attrName => $optName) {
            if ($attrNode->getAttribute($attrName)) {
                $optRaw[] = sprintf("%s=%s", $optName, $attrNode->getAttribute($attrName));
            }
        }

        foreach ($attrNode->childNodes as $optNode) {
            /**
             * @var \DOMElement $optNode
             */
            if (!is_a($optNode, \DOMElement::class) || $optNode->tagName !== "{$this->smartPrefix}:field-option") {
                continue;
            }

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
            } else {
                $data[] = ["SCHAR", ""];
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
            $iconData = ["ICON", $node->getAttribute("file")];
            if ($node->getAttribute("reset") === "true") {
                $iconData[] = "force=yes";
            }
            $data[] = $iconData;
        }

        $node = $this->getNode($config, "default-folder");
        if ($node) {
            $data[] = ["DFLDID", $node->nodeValue];
        }

        $nodes = $this->getNodes($config, "tag");

        foreach ($nodes as $node) {
            /**  @var \DOMElement $node */
            $tagName = $node->getAttribute("name");
            $data[] = ["TAG", $tagName, $node->nodeValue];
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
        $node = $this->getNode($config, "default-workflow");
        if ($node && $node->getAttribute("ref")) {
            $data[] = ["WID", $node->getAttribute("ref")];
        }

        return $data;
    }

    /**
     * @param string $name
     * @param \DOMElement $e
     *
     * @return \DOMNodeList
     */
    protected function getNodes(\DOMElement $e, $name)
    {
        return $e->getElementsByTagNameNS($this->smartNs, $name);
    }

    /**
     * @param \DOMElement $e
     * @param string $name
     *
     * @return \DOMElement
     */
    protected function getNode(\DOMElement $e, $name)
    {
        $nodes = $this->getNodes($e, $name);
        if ($nodes) {
            return $nodes[0];
        }
        return null;
    }


    /**
     * @param \DOMElement $e
     * @param string $name
     *
     * @return \DOMElement
     */
    private function getClosest(\DOMElement $e, $name)
    {
        $tagName = $this->smartPrefix . ":" . $name;
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
     * @param \DOMElement $hook node
     * @param string[] $allowedProperties if not empty, restreint properties possibilities
     *
     * @return string
     */
    protected function getCallableString(\DOMElement $hook, $allowedProperties = []): string
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
            $arg = trim($argNode->nodeValue);

            if ($allowedProperties && $type === "property") {
                if (array_search($arg, $allowedProperties) === false) {
                    throw new Exception("ATTR1103", $arg, implode(", ", $allowedProperties));
                }
            }

            if ($type === "string") {
                // Escape quote
                $arg = '"' . str_replace('"', '\\"', $arg) . '"';
            } else {
                $arg .= '::' . $type;
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
                $attridreturn = strtolower($attridreturn);
                $returnName = $returnNode->getAttribute("name");
                if ($returnName) {
                    $attridreturn .= sprintf("{%s}", $returnName);
                }
                $returns[] = $attridreturn;
            }
        }

        if ($returns) {
            $method .= ":" . implode(",", $returns);
        }
        return $method;
    }


    public function clearVerboseMessages()
    {
        $this->verboseMessages = [];
    }

    /**
     * @return array
     */
    public function getVerboseMessages(): array
    {
        foreach ($this->cr as $cr) {
            if (empty($cr["code"])) {
                $this->verboseMessages[] = sprintf("%s", $cr["msg"]);
            } else {
                $this->verboseMessages[] = sprintf("[%s] %s", $cr["code"], $cr["msg"]);
            }
        }
        return $this->verboseMessages;
    }
}
