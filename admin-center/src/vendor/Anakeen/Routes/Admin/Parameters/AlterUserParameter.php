<?php

namespace Anakeen\Routes\Admin\Parameters;


use Anakeen\Core\AccountManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\ContextParameterManager;
use Dcp\Db\Exception;

class AlterUserParameter
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
            return false;
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
                return is_int($value);
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
     * Modify the value of a parameter for a specific user
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     * @throws \Dcp\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        // Alter parameter for user
        $userLogin = $args['user'];
        $userId = AccountManager::getIdFromLogin($userLogin);

        // Namespace and parameter name
        $nameSpace = $args['namespace'];
        $parameterName = $args['parameter_name'];

        // New value of parameter
        $newValue = $request->getParam('value');

        if ($this->isCorrect($nameSpace, $parameterName, $newValue)) {
            // Modify parameter value
            ContextParameterManager::setUserValue($nameSpace, $parameterName, $newValue, $userId);

            return $response->withJson(['namespace' => $nameSpace, 'parameter_name' => $parameterName, 'value' => $newValue]);
        } else {
            return $response->withStatus(400, 'Wrong value');
        }
    }
}