<?php

namespace Anakeen\Routes\Admin\Parameters;

use Anakeen\Core\DbManager;
use Dcp\Db\Exception;

class AllParameters
{
    private function formatParameter($parameter)
    {
        if ($parameter['usefor'] === 'A' || $parameter['usefor'] === 'G') {
            $formatedParameter = [];

            $formatedParameter['name'] = $parameter['name'];
            $formatedParameter['description'] = $parameter['descr'];

            $formatedParameter['category'] = $parameter['category'];

            $formatedParameter['value'] = $parameter['value'];

            $formatedParameter['isUser'] = ($parameter['isuser'] === 'Y');
            $formatedParameter['isGlobal'] = ($parameter['isglob'] === 'Y');

            $formatedParameter['domainId'] = $parameter['appid'];
            $formatedParameter['domainName'] = $parameter['domain'];

            $formatedParameter['isStatic'] = ($parameter['kind'] === 'static');
            $formatedParameter['isReadOnly'] = ($parameter['kind'] === 'readonly');

            if (!$formatedParameter['isStatic'] && !$formatedParameter['isReadOnly']) {
                $formatedParameter['type'] = $parameter['kind'];
            } else {
                $formatedParameter['type'] = '';
            }

            return $formatedParameter;
        } else {
            return null;
        }
    }

    private function formatTreeDataSource($parameters)
    {
        $params = $parameters;
        uasort($params, function ($a, $b)
        {
            if ($a['category'] && !$b['category']) {
                return -1;
            } elseif (!$a['category'] && $b['category']) {
                return 1;
            } else {
                return ($a['name'] < $b['name']) ? -1 : 1;
            }
        });

        $data = [];
        $globalParametersId = 1;
        $domainParametersId = 2;
        $currentId = 3;

        $data[] = [ 'id' => $globalParametersId, 'parentId' => '', 'name' => 'Global parameters', 'rowLevel' => 1];
        $data[] = [ 'id' => $domainParametersId, 'parentId' => '', 'name' => 'Domain parameters', 'rowLevel' => 1 ];

        $globalCategoriesId = [];
        $domainId = [];
        $domainCatgoriesId = [];

        foreach ($params as $param) {
            $param['id'] = $currentId++;

            if ($param['isGlobal']) {
                if ($param['category']) {
                    $currentCategory = $globalCategoriesId[$param['category']];
                    if ($currentCategory === null) {
                        $newId = $currentId++;
                        $data[] = ['id' => $newId, 'parentId' => $globalParametersId, 'name' => $param['category'], 'rowLevel' => 2];
                        $globalCategoriesId[$param['category']] = $newId;
                        $currentCategory = $newId;
                    }
                    $param['parentId'] = $currentCategory;
                    $data[] = $param;
                } else {
                    $param['parentId'] = $globalParametersId;
                    $data[] = $param;
                }

            } else {
                $currentDomain = $domainId[$param['domainName']];
                if ($currentDomain === null) {
                    $newId = $currentId++;
                    $data[] = ['id' => $newId, 'parentId' => $domainParametersId, 'name' => $param['domainName'], 'rowLevel' => 2];
                    $domainId[$param['domainName']] = $newId;
                    $domainCatgoriesId[$param['domainName']] = [];
                    $currentDomain = $newId;
                }

                if ($param['category']) {
                    $currentCategory = $domainCatgoriesId[$param['domainName']]['category'];
                    if ($currentCategory === null) {
                        $newId = $currentId++;
                        $data[] = ['id' => $newId, 'parentId' => $currentDomain, 'name' => $param['category'], 'rowLevel' => 3];
                        $domainCatgoriesId[$param['domainName']]['category'] = $newId;
                        $currentCategory = $newId;
                    }
                    $param['parentId'] = $currentCategory;
                    $data[] = $param;
                } else {
                    $param['parentId'] = $currentDomain;
                    $data[] = $param;
                }
            }
        }

        return $data;
    }

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response)
    {
        $sqlRequest = 'select paramdef.*, paramv.val as value, paramv.type as usefor  from paramdef, paramv where  paramdef.name = paramv.name;';
        $outputResult = [];
        $allParameters = [];

        try {
            DbManager::query($sqlRequest, $outputResult);
        } catch (Exception $e) {

        }

        foreach ($outputResult as $parameter) {
            $formatedParameter = $this->formatParameter($parameter);
            if ($formatedParameter !== null) {
                $allParameters[] = $formatedParameter;
            }
        }

        $treeDataSource = $this->formatTreeDataSource($allParameters);

        return $response->withJson($treeDataSource);
    }
}