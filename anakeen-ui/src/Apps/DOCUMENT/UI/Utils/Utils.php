<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class Utils
{
    /**
     * Return custom client date send by client browser
     * @return mixed
     */
    public static function getCustomClientData()
    {
        if (isset($_GET["customClientData"])) {
            return json_decode($_GET["customClientData"], true);
        }
        if ($_SERVER["REQUEST_METHOD"] === "POST" || $_SERVER["REQUEST_METHOD"] === "PUT") {
            $post = json_decode(file_get_contents("php://input") , true);
            if (isset($post["customClientData"])) {
                return $post["customClientData"];
            }
        }
        return null;
    }
}
