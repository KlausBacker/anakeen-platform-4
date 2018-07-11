<?php

namespace Anakeen\Routes\Admin\Parameters;

use Anakeen\Core\AccountManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Router\ApiV2Response;

/**
 * Class AlterUserParameter
 *
 * @note Used by route : PUT /api/v2/admin/parameters/{user}/{namespace}/{parameter_name}/
 * @package Anakeen\Routes\Admin\Parameters
 */
class AlterUserParameter
{
    protected $userLogin;
    protected $userId;
    protected $nameSpace;
    protected $parameterName;
    protected $newValue;

    /**
     * Alter a parameter value for a specified user
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        try {
            if ($this->isCorrect($this->nameSpace, $this->parameterName, $this->newValue)) {
                $return = $this->alterUserParameter($this->nameSpace, $this->parameterName, $this->newValue, $this->userId);
                return ApiV2Response::withData($response, $return);
            } else {
                $response->withStatus(400, 'Wrong value');
                return ApiV2Response::withMessages($response, ['Wrong value']);
            }
        } catch (\Exception $e) {
            $response->withStatus(500, 'Error modifying value');
            return ApiV2Response::withMessages($response, ['Error modifying value']);
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
        $this->userLogin = $args['user'];
        $this->userId = AccountManager::getIdFromLogin($this->userLogin);

        $this->nameSpace = $args['namespace'];
        $this->parameterName = $args['parameter_name'];

        $this->newValue = $request->getParam('value');
    }

    /**
     * Alter parameter value for the specified user
     * Return the namespace, parameter name and new saved value
     *
     * @param $nameSpace
     * @param $parameterName
     * @param $newValue
     * @param $userId
     * @return array
     * @throws \Dcp\Exception
     */
    private function alterUserParameter($nameSpace, $parameterName, $newValue, $userId)
    {
        ContextParameterManager::setUserValue($nameSpace, $parameterName, $newValue, $userId);
        $savedValue = $this->getUserParameterValue($nameSpace, $parameterName, $userId);

        return ['namespace' => $nameSpace, 'parameter_name' => $parameterName, 'value' => $savedValue];
    }

    /**
     * Check if the new value can be saved according to the parameter type
     *
     * @param $nameSpace
     * @param $parameterName
     * @param $newValue
     * @return bool
     * @throws \Dcp\Db\Exception
     */
    private function isCorrect($nameSpace, $parameterName, $newValue)
    {
        $full = $nameSpace.'::'.$parameterName;
        $sqlRequest = sprintf(
            "select paramdef.*, paramv.val as value, paramv.type as usefor from paramdef, paramv where paramdef.name = paramv.name and paramv.type='G' and paramdef.name='%s';",
            pg_escape_string($full)
        );
        $output = [];

        DbManager::query($sqlRequest, $output);

        $paramType = $output[0]['kind'];

        switch ($paramType) {
            case "text":
                return is_string($newValue);
                break;
            case "number":
                return is_numeric($newValue);
                break;
            case "integer":
                return is_int($newValue);
                break;
            case "double":
                return is_numeric($newValue);
                break;
            case "json":
                return is_string($newValue) && is_array(json_decode($newValue, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
                break;
            default:
                if (stripos($paramType, 'enum') === 0) {
                    $values = substr($paramType, 5);
                    $values = substr($values, 0, -1);
                    $values = explode('|', $values);
                    return in_array($newValue, $values);
                } else {
                    return false;
                }
        }
    }

    /**
     * Get the saved value for user parameter in database
     *
     * @param $nameSpace
     * @param $parameterName
     * @param $userId
     * @return mixed
     * @throws \Dcp\Db\Exception
     */
    private function getUserParameterValue($nameSpace, $parameterName, $userId)
    {
        $full = $nameSpace.'::'.$parameterName;
        $sqlRequest = sprintf(
            "select paramv.val as value, paramv.type as usefor from paramdef, paramv where paramdef.name = paramv.name and paramv.type='%s' and paramdef.name='%s';",
            pg_escape_string("U".$userId),
            pg_escape_string($full)
        );
        $output = [];

        DbManager::query($sqlRequest, $output);

        return $output[0]['value'];
    }
}
