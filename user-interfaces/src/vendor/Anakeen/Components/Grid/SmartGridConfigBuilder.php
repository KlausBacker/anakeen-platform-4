<?php

namespace Anakeen\Components\Grid;

use Anakeen\Components\Grid\Exceptions\Exception;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Core\SmartStructure\BasicAttribute;
use Anakeen\Core\Utils\Strings;
use Anakeen\SmartElementManager;
use Anakeen\Ui\CallableMenu;
use Closure;
use SmartStructure\Fields\Dir;
use SmartStructure\Fields\Report as ReportFields;
use SmartStructure\Fields\Search;

class SmartGridConfigBuilder
{
    const DEFAULT_COLUMNS = ["title"];
    const DEFAULT_CONTENT_URL = "/api/v2/grid/content/%s?fields=%s";

    protected $fields = [];
    protected $actions = [];
    protected $toolbarActions = false;
    protected $footer = false;
    protected $pageable = false;

    /**
     * @var  int
     */
    protected $smartCollectionId = null;

    /**
     * @var  \Anakeen\Core\Internal\SmartElement
     */
    protected $smartCollection = null;

    protected $contentUrl = null;

    /**
     * @var Closure
     */
    protected $hookConfig = null;

    /**
     * Constructor of SmartGridConfigBuilder
     *
     * @param  mixed $collectionId - Identifier of the collection (structure name/id, folder or report id),
     * it could be 0 for searching in all Smart Elements, or -1 for searching in all Smart Structures
     *
     * @param  mixed $columns
     *
     * @return $this - the current instance
     */
    public function __construct($collectionId = 0, $columns = [])
    {
        $this->setCollection($collectionId);
        if (!empty($fields)) {
            $this->setColumns($columns);
        }
    }

    /**
     * Get the Smart Element Grid configuration array
     *
     * @return array
     */
    public function getConfig()
    {
        $fields = $this->getFields();
        $contentUrl = $this->contentUrl ?: sprintf(self::DEFAULT_CONTENT_URL, $this->smartCollectionId, static::fieldsToUrl($fields));
        $config = array(
            "columns" => $fields,
            "pageable" => $this->getPageable(),
            "collection" => $this->getCollectionInfo(),
            "contentURL" => $contentUrl,
            "locales" => $this->getLocales(),
            "footer" => $this->getFooter(),
            // "toolbar" => [],
             "actions" => $this->getActions()
        );
        return $config;
    }

    /**
     * Set the smart collection which the smart element Smart Element Grid is based on
     *
     * @param  mixed $collectionId - Identifier of the collection (structure name/id, folder or report id),
     * it could be 0 for searching in all Smart Elements, or -1 for searching in all Smart Structures
     *
     * @return $this - the current instance
     */
    public function setCollection($collectionId)
    {
        $this->smartCollectionId = $collectionId;
        if ($this->smartCollectionId !== 0 && $this->smartCollectionId !== -1) {
            $this->smartCollection = SmartElementManager::getDocument($collectionId);
            if (!$this->smartCollection) {
                $exception = new Exception("GRID0001", $this->collectionId);
                $exception->setHttpStatus("404", "Smart Element not found");
                throw $exception;
            }
        }
        return $this;
    }

    /**
     * Set the Smart Element Grid content url.
     *
     * @param  string $url
     *
     * @return $this - the current instance
     */
    public function setContentUrl(string $url)
    {
        $this->contentUrl = $url;
        return $this;
    }

    /**
     * Add an abstract column in Smart Element Grid
     *
     * @param  string $colId - identifier of the column
     * @param  array $options - options for the column
     * @return $this - the current instance
     */
    public function addAbstractColumn(string $colId, array $options = [])
    {
        $type = $options["smartType"] ?? "text";
        $title = $options["title"] ?? $colId;
        $contextLabels = $options["context"] ?? [];
        $relation = $options["relation"] ?? "";
        $encoded = $options["encoded"] ?? false;
        $sortable = $options["sortable"] ?? false;
        $filterable = false;
        if (isset($options["filterable"])) {
            if ($options["filterable"] === true) {
                $filterable = static::getFilterable($type);
            } else {
                $filterable = $options["filterable"];
            }
        }
        $data = [
            "field" => $colId,
            "multiple" => false,
            "smartType" => $type,
            "title" => $title,
            "context" => $contextLabels,
            "relation" => $relation,
            "withContext" => isset($options["context"]) && count($options["context"]) > 0,
            "encoded" => $encoded,
            "abstract" => true,
            "property" => false,
            "sortable" => $sortable,
            "filterable" => $filterable
        ];
        $this->fields[] = $data;
        return $this;
    }

    /**
     * Add a property as a Smart Element Grid column
     *
     * @param  string $propertyName the name of the property
     * @param  string $overload overload the configuration of the property
     *
     * @return $this - the current instance
     */
    public function addProperty(string $propertyName, $overload = [])
    {
        $this->fields[] = array_merge($this->getPropertyConfig($propertyName), $overload);
        return $this;
    }

    /**
     * Add a field as a Smart Element Grid column
     *
     * @param  string $fieldId - the id of the field
     * @param  string $overload overload the configuration of the property
     * @param  string $structureName - the identifier of the structure containing the field, by default it is computed by the provided collection
     * @return $this - the current instance
     */
    public function addField(string $fieldId, $overload = [], $structureName = "")
    {
        $this->fields[] = array_merge($this->getFieldConfig($fieldId, $structureName), $overload);
        return $this;
    }

    /**
     * Add a row action in Smart Element Grid
     *
     * @param string $action - Name of the action to perform
     * @param string $title - Displayed title of the action
     * @param string $iconClass - iconClass to display
     * @return $this - the current instance
     */
    public function addRowAction(string $action, string $title, $iconClass = false)
    {
        array_push($this->actions, array("action" => $action, "title" => $title, "iconClass" => $iconClass));
        return $this;
    }

    /**
     * Add a toolbar action in Smart Element Grid
     *
     * @return $this - the current instance
     */
    public function addToolbarAction()
    {
        return $this;
    }

    /**
     * Add a footer to a specific column in Smart Element Grid
     *
     * @return $this - the current instance
     */
    public function addFooter()
    {
        return $this;
    }

    /**
     * Set grid columns
     *
     * @param  mixed $columns
     *
     * @return $this - the current instance
     */
    public function setColumns($columns)
    {
        foreach ($columns as $column) {
            if (isset($column["property"]) && $column["property"] === true) {
                // Give 2nd argument to mix the property with the client given data for overloading
                $this->addProperty($column["field"], $column);
            } elseif (isset($column["abstract"]) && $column["abstract"] === true) {
                $this->addAbstractColumn($column["field"], $column);
            } else {
                // Give 2nd argument to mix the field with the client given data for overloading
                $this->addField($column["field"], $column);
            }
        }
        return $this;
    }


    /**
     * Use default available configuration for specified collection.
     * If collection is a report, the default available configuration is computed from the report configuration.
     *
     * @param  boolean $columns use default columns
     * @param  boolean $rowActions use default row actions
     * @param  boolean $toolbarActions use default toolbar actions
     * @param  boolean $footer use default footer
     *
     * @return $this - the current instance
     */
    public function useDefaultConfiguration($columns = true, $rowActions = true, $toolbarActions = true, $footer = true)
    {
        if ($columns) {
            $this->useDefaultColumns();
        }
        if ($rowActions) {
            $this->useDefaultRowActions();
        }
        if ($toolbarActions) {
            $this->useDefaultToolbarActions();
        }
        if ($footer) {
            $this->useDefaultFooter();
        }
        return $this;
    }

    /**
     * Use default available columns for specified collection.
     * If collection is a report, the default available columns are the columns specified in the report.
     *
     * @return $this - the current instance
     */
    public function useDefaultColumns()
    {
        $cols = self::DEFAULT_COLUMNS;
        // Case of a report
        if (!empty($this->smartCollection) && is_a($this->smartCollection, SEManager::getFamilyClassName("REPORT"))) {
            // Get report columns
            $reportCols = $this->smartCollection->getMultipleRawValues(ReportFields::rep_idcols);
            $displayOptions = $this->smartCollection->getMultipleRawValues(ReportFields::rep_displayoption);
            if (!empty($reportCols)) {
                $cols = $reportCols;
            }

            foreach ($cols as $kf => $attrid) {
                if ($attrid) {
                    // get prop or field config
                    $config = $this->getUnknownConfig($attrid);
                    if (!empty($config)) {
                        if (!empty($displayOptions[$kf])) {
                            switch ($displayOptions[$kf]) {
                                case "docid":
                                    $config["title"] .= sprintf(" (%s)", ___("id", "Report"));
                                    $config["smartType"] = "int";
                                    $config["filterable"]["cell"]["enable"] = false;
                                    break;
                                case "title":
                                    $config["title"] .= sprintf(" (%s)", ___("title", "Report"));
                                    $config["smartType"] = "text";
                                    break;
                            }
                        }
                        $this->fields[] = $config;
                    }
                }
            }
        } else {
            // in not report case, return DEFAULT_COLUMNS config
            foreach ($cols as $kf => $attrid) {
                $this->fields[] = $this->getUnknownConfig($attrid);
            }
        }
        return $this;
    }

    /**
     * Use default available actions.
     *
     * @return $this - the current instance
     */
    public function useDefaultRowActions()
    {
        return $this;
    }

    /**
     * Use default available toolbar actions.
     *
     * @return $this - the current instance
     */
    public function useDefaultToolbarActions()
    {
        return $this;
    }

    /**
     * Use default available footer for specified collection.
     * If collection is a report, the default available footers are the footers specified in the report.
     *
     * @return $this - the current instance
     */
    public function useDefaultFooter()
    {
        return $this;
    }

    public function setPageable($pageable)
    {
        $this->pageable = $pageable;
    }

    protected function getDisplayableProperties()
    {
        $properties = \Anakeen\Core\Internal\SmartElement::$infofields;
        array_walk($properties, function (&$value, $key) {
            $value["field"] = $key;
            $value["smartType"] = $value['type'];
            $value["title"] = Strings::mbUcfirst(_($value['label']));
            $value["property"] = true;
            $value["filterable"] = $this->getFilterable($value["type"]);

            if (isset($value["displayable"])) {
                unset($value["displayable"]);
            }
            return $value;
        });
        return $properties;
    }

    protected function getFields()
    {
        return $this->fields;
    }

    protected function getActions()
    {
        return $this->actions;
    }

    protected function getToolbarActions()
    {
        return $this->toolbarActions;
    }

    protected function getFooter()
    {
        return $this->footer;
    }

    protected function getPageable()
    {
        // Case of a report
        if (!empty($this->smartCollection) && is_a($this->smartCollection, SEManager::getFamilyClassName("REPORT"))) {
            $pageSlice = $this->smartCollection->getRawValue(ReportFields::rep_limit);
            if ($pageSlice) {
                return ["pageSize" => intval($pageSlice), "pageSizes" => [intval($pageSlice)]];
            }
        }
        return $this->pageable;
    }

    protected function getReferenceStructure($structureIdentifier = null)
    {
        if (!empty($structureIdentifier)) {
            $structureId = $structureIdentifier;
        } else {
            if (empty($this->smartCollection)) {
                return false;
            }
            switch ($this->smartCollection->defDoctype) {
                case "C": // Smart Structure
                    $structureId = $this->smartCollection->initid;
                    break;
                case "D": // Dir
                    $structureId = $this->smartCollection->getRawValue(Dir::fld_famids);
                    break;
                case "S": // Search
                    $structureId = $this->smartCollection->getRawValue(Search::se_famid);
                    break;
                default:
                    break;
            }
        }
        $structureRef = SmartElementManager::getFamily($structureId);
        if (empty($structureRef)) {
            throw new Exception("GRID0002", $structureId);
        }
        return $structureRef;
    }

    protected function getPropertyConfig(string $propertyName)
    {
        $properties = $this->getDisplayableProperties();
        if (isset($properties[$propertyName])) {
            return $properties[$propertyName];
        } else {
            throw new Exception("GRID0004", $propertyName);
        }
    }

    protected function getFieldConfig(string $fieldId, string $structureName = "")
    {
        $structureRef = $this->getReferenceStructure($structureName);
        if (!empty($structureRef)) {
            $field = $structureRef->getAttribute($fieldId);
            if (!empty($field)) {
                if ($field->type === "account" && !$field->format) {
                    $match = $field->getOption("match");
                    switch ($match) {
                        case "group":
                            $field->format = "IGROUP";
                            break;
                        case "role":
                            $field->format = "ROLE";
                            break;
                        default:
                            $field->format = "IUSER";
                    }
                }
                $data = [
                    "field" => $field->id,
                    "multiple" => $field->isMultiple(),
                    "smartType" => $field->type,
                    "title" => $field->getLabel(),
                    "context" => static::getContextLabels($field),
                    "relation" => $field->format,
                    "withContext" => true,
                    "encoded" => false,
                    "sortable" => false,
                    "filterable" => static::getFieldFilterableConfig($field),
                ];
                $isSortable = static::isFieldSortable($fieldId, $structureRef);
                if ($isSortable) {
                    $data["sortable"] = ["initialDirection" => "asc"];
                    if (($data["smartType"] == "docid" || $data["smartType"] == "account")) {
                        $data["sortable"]["compare"] = $field->getOption("doctitle") == "auto" ? $fieldId . "_title" : $field->getOption("doctitle");
                    }
                }
                return $data;
            } else {
                $e = new Exception("GRID0003", $fieldId, $structureRef->name);
                $e->setHttpStatus(404, "Smart Field " . $fieldId . " not found in " . $structureRef->name);
                throw $e;
            }
        } else {
            $e = new Exception("GRID0005", $fieldId);
            $e->setHttpStatus(500, "Cannot resolved smart field " . $fieldId);
            throw $e;
        }
    }

    protected function getUnknownConfig($id)
    {
        try {
            // try property
            $config = $this->getPropertyConfig($id);
            return $config;
        } catch (Exception $e) {
            if ($e->getDcpCode() === "GRID0004") {
                // try field
                $config = $this->getFieldConfig($id);
                return $config;
            } else {
                throw $e;
            }
        } catch (Exception $e) {
            if ($e->getDcpCode() === "GRID0004") {
                $error = new Exception("GRID0006", $id);
                $error->setHttpStatus(500, "Cannot resolved " . $id);
                throw $error;
            }
            throw $e;
        }
    }

    protected function getCollectionInfo()
    {
        if (!empty($this->smartCollection)) {
            return [
                "id" => $this->smartCollection->initid,
                "name" => $this->smartCollection->name,
                "title" => $this->smartCollection->getTitle(),
            ];
        }
        return [];
    }

    protected function getLocales()
    {
        return [
            "pageable" => [
                "messages" => [
                    "itemsPerPage" => ___("results per page", "smart-grid"),
                    "of" => ___("of", "smart-grid"),
                    "display" => ___("{0} - {1} of {2} results", "smart-grid"),
                    "empty" => ___("No results", "smart-grid")
                ],
            ],
            "filterable" => [
                "messages" => [
                    "and" => ___("And", "smart-grid"),
                    "clear" => ___("Clear", "smart-grid"),
                    "filter" => ___("Filter", "smart-grid"),
                ],
            ],
            "consult" => ___("Display", "smart-grid"),
            "edit" => ___("Modify", "smart-grid"),
            "export" => ___("Export as XLSX", "smart-grid"),
            "selectOperator" => ___("-- Select another operator --", "smart-grid"),
            "extraOperator" => ___("Grid Settings", "smart-grid"),
            "rowCollapse" => ___("Collapse / Expand rows", "smart-grid"),
            "uploadReport" => ___("Export as xlsx", "smart-grid"),
            "uploadAgain" => ___("Retry export", "smart-grid"),
            "uploadCancel" => ___("Cancel", "smart-grid"),
            "uploadAllResults" => ___("Upload all results", "smart-grid"),
            "uploadSelection" => ___("Upload selected items", "smart-grid"),
            "uploadSuccess" => ___("Upload succeeded", "smart-grid"),
            "uploadError" => ___("Upload error", "smart-grid"),
        ];
    }

    protected static function getContextLabels(BasicAttribute $field, $contextLabels = [])
    {
        if ($field && $field->fieldSet && $field->fieldSet->id != \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD) {
            array_unshift($contextLabels, $field->fieldSet->getLabel());
            return static::getContextLabels($field->fieldSet, $contextLabels);
        }
        return $contextLabels;
    }

    protected static function isFieldSortable($fieldId, SmartStructure $structure)
    {
        $sortable = $structure->getSortAttributes();
        return isset($sortable[$fieldId]);
    }

    protected static function getFieldFilterableConfig(BasicAttribute $field)
    {
        if ($field->getAccess() === BasicAttribute::NONE_ACCESS) {
            return false;
        }
        if ($field->getOption("searchCriteria") === "hidden") {
            return false;
        }

        return static::getFilterable($field->type . ($field->isMultiple() ? '[]' : '') . ($field->isMultipleInArray() ? '[]' : ''));
    }


    protected static function getFilterable($type)
    {
        $operators = Operators::getTypeOperators($type);

        if (!$operators) {
            return false;
        }

        $stringsOperators = [];
        foreach ($operators as $k => $operator) {
            if (!empty($operator["typedLabels"])) {
                $stringsOperators[$k] = $operator["typedLabels"][$type] ?? $operator["label"];
            } else {
                $stringsOperators[$k] = $operator["label"];
            }
        }


        $filterable = [
            "operators" => [
                "string" => $stringsOperators,
                "date" => $stringsOperators,
            ],
            "cell" => [
                "enable" => true,
                "delay" => 9999999999 // Wait 115 days : only way to have the clear button easyly
            ]
        ];
        return $filterable;
    }

    protected static function fieldsToUrl($fields)
    {
        return implode(",", array_map(function ($item) {
            $fieldType = "attributes";
            if (isset($item["property"]) && $item["property"]) {
                $fieldType = "properties";
            } else {
                $fieldType = "attributes";
            }
            return "document.$fieldType." . $item["field"];
        }, $fields));
    }
}
