<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
