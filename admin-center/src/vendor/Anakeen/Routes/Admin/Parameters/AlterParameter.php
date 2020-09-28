<?php

namespace Anakeen\Routes\Admin\Parameters;

use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Router\ApiV2Response;

/**
 * Class AlterParameter
 *
 * @note Used by route : PUT /api/v2/admin/parameters/{namespace}/{parameter_name}/
 * @package Anakeen\Routes\Admin\Parameters
 */
class AlterParameter
{
    protected $nameSpace;
    protected $parameterName;
    protected $newValue;

    /**
     * Alter a system parameter value
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        if ($this->isCorrect($this->nameSpace, $this->parameterName, $this->newValue)) {
            $return = $this->alterValue($this->nameSpace, $this->parameterName, $this->newValue);
            return ApiV2Response::withData($response, $return);
        } else {
            $response = $response->withStatus(400, 'Wrong value');
            return ApiV2Response::withMessages($response, ['Wrong value']);
        }
    }

    /**
     * Init parameters from request
     *
     * @param \Slim\Http\request $request
     * @param $args
     */
    private function initParameters(\Slim\Http\request $request, $args)
    {
        $this->nameSpace = $args['namespace'];
        $this->parameterName = $args['parameter_name'];

        $this->newValue = $request->getParam('value');
    }

    /**
     * Alter parameter value in database
     * Return the namespace, parameter name and new saved value
     *
     * @param $nameSpace
     * @param $parameterName
     * @param $newValue
     * @return array
     */
    private function alterValue($nameSpace, $parameterName, $newValue)
    {
        // Change value
        ContextParameterManager::setValue($nameSpace, $parameterName, $newValue);

        // Get saved new value
        $returnNewValue = ContextParameterManager::getValue($nameSpace, $parameterName);

        return ['namespace' => $nameSpace, 'parameter_name' => $parameterName, 'value' => $returnNewValue];
    }

    /**
     * Check if the new value can be saved according to the parameter type
     *
     * @param $nameSpace
     * @param $parameterName
     * @param $newValue
     * @return bool
     * @throws \Anakeen\Database\Exception
     */
    private function isCorrect($nameSpace, $parameterName, $newValue)
    {
        $full = $nameSpace . '::' . $parameterName;
        $sqlRequest = sprintf(
            "select paramdef.*, paramv.val as value, paramv.type as usefor from paramdef, paramv where paramdef.name = paramv.name and paramv.type='G' and paramdef.name='%s';",
            pg_escape_string($full)
        );
        $output = [];

        DbManager::query($sqlRequest, $output);


        $paramType = strtolower($output[0]['kind']);
        switch ($paramType) {
            case "password":
                return is_string($newValue);
            case "text":
                return is_string($newValue);
            case "double":
            case "number":
                return is_numeric($newValue);
            case "integer":
                return ctype_digit($newValue);
            case "json":
                return is_string($newValue) && is_array(json_decode($newValue, true)) && (json_last_error() === JSON_ERROR_NONE);
            default:
                if (stripos($paramType, 'enum') === 0) {
                    $values = substr($output[0]['kind'], 5);
                    $values = substr($values, 0, -1);
                    $values = explode('|', $values);
                    return in_array($newValue, $values);
                } else {
                    return false;
                }
        }
    }
}
