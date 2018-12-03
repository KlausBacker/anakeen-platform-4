<?php
/**
 * Transformation server engine manager
 *
 * @author  Anakeen
 * @version $Id: Class.TEClient.php,v 1.12 2007/08/14 09:39:33 eric Exp $
 * @package FDL
 */

/**
 */

namespace Dcp\TransformationEngine;

use Anakeen\Core\Internal\ContextParameterManager;

class Manager
{

    const Ns = "TE";

    /**
     * check if TE params are set
     *
     * @return string error message, if no error empty string
     */
    public static function checkParameters()
    {
        if (ContextParameterManager::getValue(self::Ns, "E_HOST") == ""
            || ContextParameterManager::getValue(self::Ns, "TE_PORT") == "") {
            return ___("Please set all TE parameters", "TransformationEngine");
        }
        return "";
    }


    /**
     * check if TE is accessible
     *
     * @return string error message, if no error empty string
     */
    public static function isAccessible()
    {
        $err = self::checkParameters();
        if ($err != '') {
            return $err;
        } else {
            $te = new \Dcp\TransformationEngine\Client(
                ContextParameterManager::getValue(self::Ns, "TE_HOST"),
                ContextParameterManager::getValue(self::Ns, "TE_PORT")
            );
            $err = $te->retrieveServerInfo($info, true);
            if ($err != '') {
                return $err;
            }
        }
        return '';
    }
}
