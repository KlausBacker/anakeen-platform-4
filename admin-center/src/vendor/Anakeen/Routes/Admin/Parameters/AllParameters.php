<?php

namespace Anakeen\Routes\Admin\Parameters;

use Anakeen\Core\DbManager;
use Anakeen\Router\ApiV2Response;

/**
 * Class AllParameters
 *
 * @note Used by route : GET /api/v2/admin/parameters/
 * @package Anakeen\Routes\Admin\Parameters
 */
class AllParameters
{
    /**
     * Return all system parameters
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        try {
            $return = $this->doRequest();
            return ApiV2Response::withData($response, $return);
        } catch (\Exception $e) {
            $response->withStatus(500, 'Error fetching parameters');
            return ApiV2Response::withMessages($response, ['Error fetching paramters']);
        }
    }

    /**
     * Execute function to get all parameters
     *
     * @return array
     * @throws \Anakeen\Database\Exception
     */
    private function doRequest()
    {
        $rawParameters = $this->getDataFromDb();
        $formatedParameters = $this->formatParameters($rawParameters);
        $treeListParameters = $this->formatTreeDataSource($formatedParameters);

        return $treeListParameters;
    }

    /**
     * Get raw data from DataBase
     *
     * @return array
     * @throws \Anakeen\Database\Exception
     */
    private function getDataFromDb()
    {
        $sqlRequest = <<<SQL
select paramdef.*, paramv.val as value, paramv.type as usefor 
from paramdef full outer join paramv on  paramdef.name = paramv.name 
where paramdef.name is not null
SQL;
        $outputResult = [];

        DbManager::query($sqlRequest, $outputResult);

        return $outputResult;
    }

    /**
     * Format correctly the parameters to send in the response
     *
     * @param $parameters
     * @return array|null
     */
    private function formatParameters($parameters)
    {
        $allParameters = [];

        foreach ($parameters as $parameter) {
            if ($parameter['usefor'] === null || $parameter['usefor'] === 'G') {
                $formatedParameter = [];

                $nsName = explode('::', $parameter['name'], 2);

                $formatedParameter['nameSpace'] = $nsName[0];
                $formatedParameter['name'] = $nsName[1];

                $formatedParameter['description'] = $parameter['descr'];

                $formatedParameter['category'] = $parameter['category'];

                $formatedParameter['value'] = $parameter['value'];

                $formatedParameter['isUser'] = ($parameter['isuser'] === 'Y');

                $formatedParameter['isStatic'] = ($parameter['kind'] === 'static');
                $formatedParameter['isReadOnly'] = ($parameter['kind'] === 'readonly');

                if (!$formatedParameter['isStatic'] && !$formatedParameter['isReadOnly']) {
                    $formatedParameter['type'] = $parameter['kind'];
                } else {
                    $formatedParameter['type'] = '';
                }

                $allParameters[] = $formatedParameter;
            }
        }

        return $allParameters;
    }

    /**
     * sort data to organize it as treeDataSource to display it in kendo treeList
     *
     * @param $parameters
     * @return array
     */
    private function formatTreeDataSource($parameters)
    {
        // Sort parameters : 1) Categorized / not categorized 2) By alphabetlical order
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
            if (isset($nameSpaceIds[$param['nameSpace']])) {
                $currentNameSpace = $nameSpaceIds[$param['nameSpace']];
            } else {
                $currentNameSpace = null;
            }
            if ($currentNameSpace === null && $param['nameSpace'] !== null) {
                $newId = $currentId++;
                array_push($data, ['id' => $newId, 'parentId' => null, 'name' => $param['nameSpace'], 'rowLevel' => 1]);
                $nameSpaceIds[$param['nameSpace']] = $newId;
                $categoryIds[$param['nameSpace']] = [];
                $currentNameSpace = $newId;
            }

            if ($param['category']) {
                if (isset($categoryIds[$param['nameSpace']][$param['category']])) {
                    $currentCategory = $categoryIds[$param['nameSpace']][$param['category']];
                } else {
                    $currentCategory = null;
                }
                if ($currentCategory === null) {
                    $newId = $currentId++;
                    array_push($data, ['id' => $newId, 'parentId' => $currentNameSpace, 'name' => $param['category'], 'rowLevel' => 2]);
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
}
