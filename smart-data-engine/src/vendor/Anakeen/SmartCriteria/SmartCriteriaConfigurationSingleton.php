<?php


namespace Anakeen\SmartCriteria;

class SmartCriteriaConfigurationSingleton
{

    private static $instance;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = SmartCriteriaConfigLib::getSmartCriteriaConfig();
        }

        return self::$instance;
    }

    /**
     * Constructeur en privé pour éviter toute autre instanciation.
     */
    private function __construct()
    {
    }

    /**
     * Méthode __clone en privé pour éviter tout clonage.
     *
     * @return void
     */
    private function __clone()
    {
    }
}
