<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 14/09/18
 * Time: 09:16
 */

namespace Anakeen\Routes\UiTest;


use Anakeen\Components\Grid\Routes\ColumnsConfig;
use Anakeen\Components\Grid\Routes\GridConfig;


class TestGridConfig extends GridConfig
{
    protected function getAllTypeConfig($originalConfig) {
        if ($this->structureRef->name === "TST_DDUI_ALLTYPE") {
            $originalConfig["toolbar"] = [
                "actionConfigs" => [
                    [ "action" => "columns", "title" => "Paramètres de la grille"]
                ]
            ];
            $originalConfig["smartFields"] = [
                ColumnsConfig::getColumnConfig("title", $this->collectionDoc),
                ColumnsConfig::getColumnConfig("test_ddui_all__title", $this->collectionDoc),
                ColumnsConfig::getColumnConfig("test_ddui_all__account_multiple", $this->collectionDoc),
                ColumnsConfig::getColumnConfig("test_ddui_all__date", $this->collectionDoc),
                ColumnsConfig::getColumnConfig("test_ddui_all__integer", $this->collectionDoc),
                ColumnsConfig::getColumnConfig("test_ddui_all__double", $this->collectionDoc),
                ColumnsConfig::getColumnConfig("test_ddui_all__money", $this->collectionDoc),
                ColumnsConfig::getColumnConfig("test_ddui_all__color_array", $this->collectionDoc),
                ColumnsConfig::getColumnConfig("test_ddui_all__text_array", $this->collectionDoc)
            ];

            $originalConfig["actions"] = [
                "title" => "Actions",
                "actionConfigs" => [
                    [ "action" => "edit", "title" => "Modifier"]
                ]
            ];
            $originalConfig["footer"] = [
                "test_ddui_all__money" => "<div style='text-align: right'><b>Total : </b>248,00 €</div>"
            ];
        } else if ($this->structureRef->name === "DEVNOTE") {
            $originalConfig["toolbar"] = [
                "actionConfigs" => [
                    [ "action" => "export", "title" => "Exportation des données"],
                    [ "action" => "columns", "title" => "Paramètres de la grille"]
                ]
            ];
            $originalConfig["smartFields"] = [
                ColumnsConfig::getColumnConfig("title", $this->collectionDoc),
                ColumnsConfig::getColumnConfig("note_author", $this->collectionDoc),
                ColumnsConfig::getColumnConfig("note_redactdate", $this->collectionDoc),
                ColumnsConfig::getColumnConfig("note_location", $this->collectionDoc),
                ColumnsConfig::getColumnConfig("note_coauthor", $this->collectionDoc),
                ColumnsConfig::getColumnConfig("note_phone", $this->collectionDoc)
            ];

            $originalConfig["actions"] = [
                "title" => "Actions",
                "actionConfigs" => [
                    [ "action" => "consult", "title" => "Ouvrir" ],
                    [ "action" => "edit", "title" => "Modifier"],
                    [ "action" => "customAction", "title" => "Custom"]
                ]
            ];
        }
        return $originalConfig;
    }
    protected function getConfig()
    {
        $config = parent::getConfig();
        return $this->getAllTypeConfig($config);
    }
}