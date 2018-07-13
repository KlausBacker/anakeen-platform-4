<?php

namespace Anakeen\Routes\Admin\Parameters;

use Anakeen\Core\AccountManager;
use Anakeen\Core\DbManager;
use Anakeen\Router\ApiV2Response;

/**
 * Class UserParameters
 *
 * @note Used by route : GET /api/v2/admin/parameters/users/{user}/
 * @package Anakeen\Routes\Admin\Parameters
 */
class UserParameters
{
    protected $userLogin;
    protected $userId;

    /**
     * Get user defined parameters and user definable parameters
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);

        try {
            $return = $this->doRequest();
            return ApiV2Response::withData($response, $return);
        } catch (\Exception $e) {
            $response->withStatus(500, 'Error fechting parameters');
            return ApiV2Response::withMessages($response, ['Error fetching parameters']);
        }
    }

    /**
     * Init parameters from request
     *
     * @param $args
     */
    private function initParameters($args)
    {
        $this->userLogin = $args['user'];
        $this->userId = "U".AccountManager::getIdFromLogin($this->userLogin);
    }

    /**
     * Execute function to get all parameters for user
     *
     * @return array
     * @throws \Dcp\Db\Exception
     */
    private function doRequest()
    {
        $rawParameters = $this->getDataFromDb();
        $rawFilteredParameters = $this->filterParameters($rawParameters, $this->userId);
        $formatedParameters = $this->formatParameters($rawFilteredParameters);
        $treeListParameters = $this->formatTreeDataSource($formatedParameters);

        return $treeListParameters;
    }

    /**
     * Get all parameters from database
     *
     * @return array
     * @throws \Dcp\Db\Exception
     */
    private function getDataFromDb()
    {
        $sqlRequest = 'select paramdef.*, paramv.val as value, paramv.type as usefor from paramdef, paramv where  paramdef.name = paramv.name;';
        $outputResult = [];

        DbManager::query($sqlRequest, $outputResult);

        return $outputResult;
    }

    private function filterParameters($parameters, $userId)
    {
        $data = [];

        foreach ($parameters as $param) {
            if ($param['usefor'] === $userId) {
                $param['initialValue'] = $this->initialValue($param, $parameters);
                $param['forUser'] = true;
                $data[] = $param;
            } elseif ($param['isuser'] === 'Y'
                && !$this->userDefined($param, $parameters, $userId)
                && ($param['usefor'] === 'G' || $param['usefor'] === 'A')) {
                $param['initialValue'] = $param['value'];
                $param['value'] = '';
                $param['forUser'] = false;
                $data[] = $param;
            }
        }

        return $data;
    }

    /**
     * Correctly format the parameter to send in the response
     *
     * @param $parameters
     * @return array
     */
    private function formatParameters($parameters)
    {
        $allParameters = [];

        foreach ($parameters as $parameter) {
            $formatedParameter = [];

            $nsName = explode('::', $parameter['name'], 2);

            $formatedParameter['nameSpace'] = $nsName[0];
            $formatedParameter['name'] = $nsName[1];

            $formatedParameter['description'] = $parameter['descr'];
            $formatedParameter['category'] = $parameter['category'];

            $formatedParameter['value'] = $parameter['value'];
            $formatedParameter['initialValue'] = $parameter['initialValue'];

            $formatedParameter['isUser'] = ($parameter['isuser'] === 'Y');
            $formatedParameter['isGlobal'] = ($parameter['isglob'] === 'Y');
            $formatedParameter['forUser'] = $parameter['forUser'];

            $formatedParameter['isStatic'] = ($parameter['kind'] === 'static');
            $formatedParameter['isReadOnly'] = ($parameter['kind'] === 'readonly');

            if (!$formatedParameter['isStatic'] && !$formatedParameter['isReadOnly']) {
                $formatedParameter['type'] = $parameter['kind'];
            } else {
                $formatedParameter['type'] = '';
            }

            $allParameters[] = $formatedParameter;
        }

        return $allParameters;
    }

    /** Format the parameters as a treListDataSource to be displayed in a kendoTreeList
     *
     * @param $parameters
     * @return array
     */
    private function formatTreeDataSource($parameters)
    {
        $params = $parameters;
        uasort($params, function ($a, $b) {
            if ($a['nameSpace'] < $b['nameSpace']) {
                return -1;
            } elseif ($a['nameSpace'] > $b['nameSpace']) {
                return 1;
            } else {
                if ($a['category'] && !$b['category']) {
                    return -1;
                } elseif (!$a['category'] && $b['category']) {
                    return 1;
                } elseif ($a['category'] && $b['category']) {
                    if ($a['category'] < $b['category']) {
                        return -1;
                    } elseif ($a['category'] > $b['category']) {
                        return 1;
                    } else {
                        return ($a['name'] < $b['name']) ? -1 : 1;
                    }
                } else {
                    return ($a['name'] < $b['name']) ? -1 : 1;
                }
            }
        });

        // treeData to return
        $data = [];

        // Id iterator
        $currentId = 1;

        // Memorize  namespace / catgories id
        $nameSpaceIds = [];
        $categoryIds = [];


        foreach ($params as $param) {
            $param['id'] = $currentId++;
            $currentNameSpace = $nameSpaceIds[$param['nameSpace']];
            if ($currentNameSpace === null) {
                $newId = $currentId++;
                $data[] = ['id' => $newId, 'parentId' => null, 'name' => $param['nameSpace'], 'rowLevel' => 1];
                $nameSpaceIds[$param['nameSpace']] = $newId;
                $categoryIds[$param['nameSpace']] = [];
                $currentNameSpace = $newId;
            }

            if ($param['category']) {
                $currentCategory = $categoryIds[$param['nameSpace']][$param['category']];
                if ($currentCategory === null) {
                    $newId = $currentId++;
                    $data[] = ['id' => $newId, 'parentId' => $currentNameSpace, 'name' => $param['category'], 'rowLevel' => 2];
                    $categoryIds[$param['nameSpace']][$param['category']] = $newId;
                    $currentCategory = $newId;
                }

                $param['parentId'] = $currentCategory;
                $data[] = $param;
            } else {
                $param['parentId'] = $currentNameSpace;
                $data[] = $param;
            }
        }

        return $data;
    }

    /**
     * Get the system value for a user defined parameter
     *
     * @param $param
     * @param $allParams
     * @return null
     */
    private function initialValue($param, $allParams)
    {
        foreach ($allParams as $parameter) {
            if ($parameter['name'] === $param['name'] && ($parameter['usefor'] === 'A' || $parameter['usefor'] === 'G')) {
                return $parameter['value'];
            }
        }

        return null;
    }

    /**
     * determine if a system parameter is redefined for the specified user
     *
     * @param $param
     * @param $allParams
     * @param $user
     * @return bool
     */
    private function userDefined($param, $allParams, $user)
    {
        foreach ($allParams as $parameter) {
            if ($parameter['name'] === $param['name'] && $parameter['usefor'] === $user) {
                return true;
            }
        }
        return false;
    }
}
