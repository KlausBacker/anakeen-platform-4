<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 14/09/18
 * Time: 09:16
 */

namespace Anakeen\SmartStructures\Dsearch\Routes;


use Anakeen\Components\Grid\Routes\GridConfig;

class SearchGridConfig extends GridConfig
{
    protected function getConfig()
    {
        $config = parent::getConfig();
        $config["actions"] = [
            "title" => "Actions",
            "actionConfigs" => [
                [ "action" => "consult", "title" => "Consulter" ],
                [ "action" => "edit", "title" => "Editer" ]
            ]
        ];
        return $config;
    }
}