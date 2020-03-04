<?php

namespace Anakeen\Components\Grid;

use Anakeen\Components\Grid\Exceptions\Exception;

interface SmartGridBuilder
{
    /**
     * Set the smart collection which the smart element Smart Element Grid is based on
     *
     * @param  mixed $collectionId - Identifier of the collection (structure name/id, folder or report id),
     * it could be 0 for searching in all Smart Elements, or -1 for searching in all Smart Structures
     *
     * @return SmartGridBuilder - the current instance
     */
    public function setCollection($collectionId);

    /**
     * Add an abstract column in Smart Element Grid
     *
     * @param  string $colId - identifier of the column
     * @param  array $options - options for the column
     * @return SmartGridBuilder - the current instance
     */
    public function addAbstract(string $colId, array $options = []);

    /**
     * Add a property as a Smart Element Grid column
     *
     * @param string $propertyName the name of the property
     * @param array $overload overload the configuration of the property
     *
     * @return SmartGridBuilder - the current instance
     * @throws Exception
     */
    public function addProperty(string $propertyName, $overload = []);

    /**
     * Add a field as a Smart Element Grid column
     *
     * @param string $fieldId - the id of the field
     * @param array $overload overload the configuration of the property
     * @param string $structureName - the identifier of the structure containing the field, by default it is computed by the provided collection
     * @return SmartGridBuilder - the current instance
     * @throws Exception
     */
    public function addField(string $fieldId, $overload = [], $structureName = "");

    /**
     * Add a Smart Element Grid column
     *
     * @param array $column - the column object
     * @return SmartGridBuilder - the current instance
     * @throws Exception
     */
    public function addColumn($column);

    /**
     * Set grid columns
     *
     * @param  mixed $columns
     *
     * @return SmartGridBuilder - the current instance
     */
    public function setColumns($columns);

    /**
     * Set the pageable Smart Element Grid configuration
     * @param $pageable
     * @return SmartGridBuilder - the current instance
     */
    public function setPageable($pageable);

    /**
     * Set the client Smart Element Grid configuration in the builder
     * @param array $clientConfig
     * @return SmartGridBuilder - the current instance
     */
    public function setClientConfig(array $clientConfig);
}
