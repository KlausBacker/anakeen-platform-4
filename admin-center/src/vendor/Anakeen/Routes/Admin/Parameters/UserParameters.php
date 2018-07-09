<?php

namespace Anakeen\Routes\Admin\Parameters;


use Anakeen\Core\AccountManager;
use Anakeen\Core\DbManager;
use Dcp\Db\Exception;

class UserParameters
{
    /**
     * Format correctly the parameter to send in the response
     * @param $parameter
     * @return array
     */
    private function formatParameter($parameter)
    {
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

        return $formatedParameter;
    }

    /**
     * sort data to organize it as treeDataSource to display it in kendo treeList
     * @param $parameters
     * @return array
     */
    private function formatTreeDataSource($parameters)
    {
        // Sort parameters : 1) Categorized / not categorized 2) By alphabetlical order
        $params = $parameters;
        uasort($params, function ($a, $b)
        {
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


        foreach ($params as $param)
        {
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
     * Return the initial value of a parameter (system value)
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
     * Check if a parameter has been redefined for a specific user
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


    /**
     * Return the list of parameters for a specific user
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $user = $args['user'];
        $userId = "U".AccountManager::getIdFromLogin($user);

        $sqlRequest = 'select paramdef.*, paramv.val as value, paramv.type as usefor from paramdef, paramv where  paramdef.name = paramv.name;';
        $outputResult = [];

        try {
            DbManager::query($sqlRequest, $outputResult);
        } catch (Exception $e) {

        }

        $data = [];

        foreach ($outputResult as $param) {
            if ($param['usefor'] === $userId) {
                $param['initialValue'] = $this->initialValue($param, $outputResult);
                $param['forUser'] = true;
                $data[] = $this->formatParameter($param);
            } elseif ($param['isuser'] === 'Y'
                && !$this->userDefined($param, $outputResult, $userId)
                && ($param['usefor'] === 'G' || $param['usefor'] === 'A')) {
                $param['initialValue'] = $param['value'];
                $param['value'] = '';
                $param['forUser'] = false;
                $data[] = $this->formatParameter($param);
            }
        }

        return $response->withJson($this->formatTreeDataSource($data));
    }
}