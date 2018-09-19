<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 14/09/18
 * Time: 09:16
 */

namespace Anakeen\Routes\UiTest;


use Anakeen\Components\Grid\Routes\GridConfig;

class TestGridConfig extends GridConfig
{
    protected function getConfig()
    {
        $config = parent::getConfig();
        $config["toolbar"] = [
            "actionConfigs" => [
                [ "action" => "export", "title" => "Exporter les données"],
                [ "action" => "columns", "title" => "Paramètres de la grille"]
            ]
        ];
        $config["actions"] = [
            "title" => "Actions",
            "actionConfigs" => [
                [ "action" => "consult", "title" => "Ouvrir" ],
                [ "action" => "edit", "title" => "Modifier"],
                [ "action" => "delete", "title" => "Supprimer"]
            ]
        ];
        return $config;
    }
}