<?php

namespace Anakeen\Routes\Admin\Parameters;

use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\ContextParameterManager;
use Dcp\Db\Exception;

class AlterParameter
{
    /**
     * Check if the value is correct (match a possible value)
     * @param $ns
     * @param $name
     * @param $value
     * @return bool
     */
    private function isCorrect($ns, $name, $value) {
        $full = $ns.'::'.$name;
        $sqlRequest = sprintf("select paramdef.*, paramv.val as value, paramv.type as usefor from paramdef, paramv where paramdef.name = paramv.name and paramv.type='G' and paramdef.name='%s';", pg_escape_string($full));
        $output = [];

        try {
            DbManager::query($sqlRequest, $output);
        } catch (Exception $e) {

        }

        $paramType = $output[0]['kind'];

        switch ($paramType) {
            case "text":
                return is_string($value);
                break;
            case "number":
                return is_numeric($value);
                break;
            case "integer":
                return ctype_digit($value);
                break;
            case "double":
                return is_numeric($value);
                break;
            case "json":
                return is_string($value) && is_array(json_decode($value, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
                break;
            default:
                if (stripos($paramType, 'enum') === 0) {
                    $values = substr($paramType, 5);
                    $values = substr($values, 0, -1);
                    $values = explode('|', $values);
                    return in_array($value, $values);
                } else {
                    return false;
                }
        }
    }

    /**
     * Modify a system parameter's value
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     * @throws \Dcp\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        // Parameter to change
        $parameterName = $args['parameter_name'];
        $nameSpace = $args['namespace'];

        // New value
        $newValue = $request->getParam('value');

        if ($this->isCorrect($nameSpace, $parameterName, $newValue)) {
            // Change value
            ContextParameterManager::setValue($nameSpace, $parameterName, $newValue);

            // Get saved new value
            $returnNewValue = ContextParameterManager::getValue($nameSpace, $parameterName);

            $responseData = ['namespace' => $nameSpace, 'parameter_name' => $parameterName, 'value' => $returnNewValue];
            return $response->withJson($responseData);
        } else {
            return $response->withStatus(400, 'Wrong value');
        }
    }
}