<?php

namespace Anakeen\Exchange;

use Anakeen\Core\Account;
use Anakeen\Core\AccountManager;
use Anakeen\Core\Internal\SmartCollectionOperators;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\ExportConfiguration;
use Anakeen\Core\SmartStructure\NormalAttribute;
use Anakeen\Vault\FileInfo;
use SmartStructure\Dsearch;
use SmartStructure\Fields\Dsearch as SearchFields;
use SmartStructure\Fields\Report as ReportFields;
use SmartStructure\Report;

class ExportSearch extends ExportConfiguration
{
    const NSSURL = self::NSBASEURL . "search/1.0";
    const NSS = "search";

    protected $search;

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(\SmartStructure\Search $search)
    {
        $this->search = $search;
        $this->sst = SEManager::getFamily($this->search->getRawValue(\SmartStructure\Fields\Search::se_famid));
    }

    /**
     * Return Xml string for smart structre configuration
     *
     * @return string
     */
    public function toXml()
    {
        $this->dom = new \DOMDocument("1.0", "UTF-8");
        $this->dom->formatOutput = true;
        $this->dom->preserveWhiteSpace = false;
        $this->domConfig = $this->cel("config");
        $this->domConfig->setAttribute("xmlns:" . self::NSS, self::NSSURL);
        $this->dom->appendChild($this->domConfig);
        $this->domConfig->appendChild($this->extractData());
        return parent::toXml();
    }

    public function getXmlNode(\DOMDocument $rootNode)
    {
        $this->dom = $rootNode;

        return $this->extractData();
    }

    protected function extractData()
    {
        $searchNode = $this->celSearch("search");
        $searchNode->setAttribute("name", $this->search->name ?: $this->search->id);
        $searchNode->setAttribute("title", $this->search->getRawValue(SearchFields::ba_title));
        $searchNode->setAttribute("structure-type", $this->search->fromname);

        $this->addField(SearchFields::se_author, "author", $searchNode);

        $criteriaNode = $this->celSearch("criteria", null, $searchNode);
        $nodeStructure = $this->addField(SearchFields::se_famid, "structure", $criteriaNode);
        if ($nodeStructure) {
            if ($this->search->getRawValue(SearchFields::se_famonly) === "yes") {
                $nodeStructure->setAttribute("only", "true");
            }
            switch ($this->search->getRawValue(SearchFields::se_latest)) {
                case "no":
                    $this->celSearch("revision", "all", $criteriaNode);
                    break;
                case "fixed":
                    $this->celSearch("revision", "latest-fixed", $criteriaNode);
                    break;
                case "allfixed":
                    $this->celSearch("revision", "fixed", $criteriaNode);
                    break;
                case "lastfixed":
                    $this->celSearch("revision", "distinct-fixed", $criteriaNode);
                    break;
                default:
                    $this->celSearch("revision", "latest", $criteriaNode);
            }
        } else {
            $this->celSearch(
                "search-system-structures",
                $this->search->getRawValue(SearchFields::se_sysfam) === "no" ? "false" : "true",
                $criteriaNode
            );
        }

        $nodeKey = $this->addField(SearchFields::se_key, "keyword", $criteriaNode);
        if ($nodeKey) {
            $nodeKey->setAttribute(
                "case-sensitive",
                ($this->search->getRawValue(SearchFields::se_case) === "yes") ? "true" : "false"
            );
        }

        if ($this->search->getRawValue(SearchFields::se_static) === "1") {
             $this->addField(SearchFields::se_sqlselect, "sql-query", $criteriaNode);
        }

        $this->addField(SearchFields::se_trash, "search-deleted", $criteriaNode);
        $this->addField(SearchFields::se_orderby, "order-by", $criteriaNode);
        $this->addField(SearchFields::se_acl, "permission-filter", $criteriaNode);


        if (is_a($this->search, Dsearch::class)) {
            $criteriaNode->appendChild($this->dSearchFilters());
        }
        if (is_a($this->search, Report::class)) {
            $searchNode->appendChild($this->reportConfiguration());
        }

        $otherData = $this->getOtherData();
        if ($otherData) {
            $searchNode->appendChild($otherData);
        }

        return $searchNode;
    }


    /**
     * Retrieve extra data when use custom search structure
     */
    protected function getOtherData()
    {
        $reportStructure = SEManager::getFamily("REPORT");
        $supportedfields = array_keys($reportStructure->getAttributes());

        $currentFields = array_keys($this->search->getAttributes());

        $otherFields = array_diff($currentFields, $supportedfields);
        if (empty($otherFields)) {
            return null;
        }
        $customDataNode = $this->celSearch("custom-data");
        foreach ($otherFields as $otherField) {
            $oa = $this->search->getAttribute($otherField);
            if ($oa && $oa->isNormal && $this->search->getRawValue($otherField)) {
                $customValueNode = $this->celSearch("custom-field", null, $customDataNode);
                $customValueNode->setAttribute("field", $otherField);
                $this->addField($otherField, "custom-value", $customValueNode);
            }
        }

        return $customDataNode;
        //print_r(array_diff( $currentFields,$supportedfields));
    }

    protected function reportConfiguration()
    {
        $reportsNode = $this->celSearch("report-configuration");

        $this->addField(ReportFields::rep_caption, "caption", $reportsNode);


        if ($this->search->getRawValue(ReportFields::rep_idsort)) {
            $sortNode = $this->celSearch("sort");
            $sortNode->setAttribute("order-by", $this->search->getRawValue(ReportFields::rep_idsort));
            $sortNode->setAttribute("direction", $this->search->getRawValue(ReportFields::rep_ordersort));
            $reportsNode->appendChild($sortNode);
        }

        $columns = $this->search->getAttributeValue(ReportFields::rep_tcols);
        $columnsNode = $this->celSearch("columns", null, $reportsNode);
        foreach ($columns as $column) {
            $this->comment($column[ReportFields::rep_lcols], $columnsNode);
            $columnNode = $this->celSearch("column", null, $columnsNode);
            $columnNode->setAttribute("field", $column[ReportFields::rep_idcols]);
            if ($column[ReportFields::rep_foots] && $column[ReportFields::rep_foots]!== "NONE") {
                $columnNode->setAttribute("footer", strtolower($column[ReportFields::rep_foots]));
            }
            if ($column[ReportFields::rep_displayoption]) {
                $columnNode->setAttribute("display-option", $column[ReportFields::rep_displayoption]);
            }
        }

        $this->addField(ReportFields::rep_limit, "result-limit", $reportsNode);

        return $reportsNode;
    }

    protected function dSearchFilters()
    {

        $filtersNode = $this->celSearch("query-filters");
        $filters = $this->search->getAttributeValue(SearchFields::se_t_detail);


        $ol = $this->search->getRawValue(SearchFields::se_ol);
        if ($ol !== "perso") {
            $filtersNode->setAttribute("logical-operator", $this->search->getRawValue(SearchFields::se_ol));
        }


        foreach ($filters as $k => $filter) {
            if ($k === 0) {
                $filter[SearchFields::se_ols] = null;
            }
            $filtersNode->appendChild($this->getFilterComment($filter));
            if ($filter[SearchFields::se_ols]) {
                $this->celSearch("logication-operator", $filter[SearchFields::se_ols], $filtersNode);
            }
            if ($filter[SearchFields::se_leftp] === "yes") {
                $this->celSearch("start-parenthesis", null, $filtersNode);
            }


            $nodeFilter = $this->celSearch("filter", null, $filtersNode);
            $nodeFilter->setAttribute("field", $filter[SearchFields::se_attrids]);
            $nodeFilter->setAttribute("operator", $filter[SearchFields::se_funcs]);
            $nodeFilter->setAttribute(
                "value",
                $this->getRelationName(
                    $filter[SearchFields::se_attrids],
                    $filter[SearchFields::se_funcs],
                    $filter[SearchFields::se_keys]
                )
            );
            if ($filter[SearchFields::se_rightp]=== "yes") {
                $this->celSearch("end-parenthesis", null, $filtersNode);
            }
        }
        return $filtersNode;
    }

    protected function getFilterComment($filter)
    {

        $operators = SmartCollectionOperators::getOperators();
        $funcLabel = $filter[SearchFields::se_funcs];
        if (isset($operators[$funcLabel])) {
            $funcLabel = $operators[$funcLabel]["label"];
        }

        $fieldLabel = $filter[SearchFields::se_attrids];

        $oa = $this->sst->getAttribute($fieldLabel);
        if ($oa) {
            $fieldLabel = $oa->getLabel();
        }

        $stringFilter = sprintf(
            "'%s' %s '%s'",
            $fieldLabel,
            $funcLabel,
            $filter[SearchFields::se_keys]
        );

        $relationTitle = $this->getRelationTitle(
            $filter[SearchFields::se_attrids],
            $filter[SearchFields::se_funcs],
            $filter[SearchFields::se_keys]
        );
        if ($relationTitle) {
            $stringFilter .= "{" . $relationTitle . "}";
        }

        if ($filter[SearchFields::se_leftp]) {
            $stringFilter = '(' . $stringFilter;
        }
        if ($filter[SearchFields::se_rightp]) {
            $stringFilter .= ')';
        }
        if ($filter[SearchFields::se_ols]) {
            $stringFilter = $filter[SearchFields::se_ols] . " " . $stringFilter;
        }

        return $this->comment("query filter: " . $stringFilter);
    }

    protected function getRelationName($fieldId, $operator, $key)
    {
        $value = $key;
        $oa = $this->sst->getAttribute($fieldId);
        if ($oa) {
            if ($oa->type === "docid" || $oa->type === "account") {
                if ($operator === "=" || $operator === "!=" || $operator === "~y") {
                    $name = SEManager::getNameFromId($key);
                    if ($name) {
                        $value = $name;
                    }
                }
            }
        }
        return $value;
    }

    protected function getRelationTitle($fieldId, $operator, $key)
    {

        $oa = $this->sst->getAttribute($fieldId);
        if ($oa) {
            if ($oa->type === "docid" || $oa->type === "account") {
                if ($operator === "=" || $operator === "!=" || $operator === "~y") {
                    return SEManager::getTitle($key);
                }
            }
        }
        return "";
    }


    protected function celSearch($name, $value = null, $parent = null)
    {
        $node = $this->dom->createElementNS(self::NSSURL, self::NSS . ":" . $name);

        if ($value !== null) {
            $node->nodeValue = $value;
        }

        if ($parent !== null) {
            $parent->appendChild($node);
        }
        return $node;
    }

    /**
     * @param $fieldId
     * @param $tagName
     * @param $parent
     * @return \DOMElement[]|\DOMElement|null
     */
    protected function addField($fieldId, $tagName, $parent)
    {
        $oa = $this->search->getAttribute($fieldId);
        if ($oa) {
            $value = $this->search->getRawValue($oa->id);
            if ($value !== "") {
                $this->comment($oa->fieldSet->getLabel() . " / " . $oa->getLabel(), $parent);
                if ($oa->isMultiple()) {
                    $values = $this->search->getMultipleRawValues($oa->id);
                    $nodeValues = [];
                    foreach ($values as $value) {
                        if ($value !== "") {
                            $nodeValue = $this->setXmlValue($this->celSearch($tagName, null, $parent), $oa, $value);
                            $nodeValues[] = $nodeValue;
                        }
                    }
                    return $nodeValues;
                } else {
                    if ($value !== "") {
                        return $this->setXmlValue($this->celSearch($tagName, null, $parent), $oa, $value);
                    }
                }
            }
        }
        return null;
    }

    protected function comment($comment, $parent = null)
    {
        $node = $this->dom->createComment($comment);


        if ($parent !== null) {
            $parent->appendChild($node);
        }

        return $node;
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
                if ($rawvalue) {
                    $seName = $this->getLogicalName($rawvalue);

                    $node->setAttribute("ref", $seName ?: $rawvalue);
                }
                break;
            case "image":
                /**  @var FileInfo $fileInfo */
                $fileInfo = $this->search->getFileInfo($rawvalue, "", "object");
                $node->nodeValue = base64_encode(file_get_contents($fileInfo->path));
                $node->setAttribute("title", $fileInfo->name);
                $node->setAttribute("mime", $fileInfo->mime_s);
                break;
            default:
                $node->nodeValue = $rawvalue;
        }
        return $node;
    }
}
