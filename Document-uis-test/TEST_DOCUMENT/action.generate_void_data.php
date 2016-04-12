<?php
/*
 * @author Anakeen
 * @package FDL
 */

function generate_void_data(Action & $action)
{
    $action->lay->template = json_encode([
        "success" => true,
        "" => []
    ]);
    $action->lay->noparse = true;

}
