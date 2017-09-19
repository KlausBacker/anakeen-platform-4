<?php

function admin_actions_list(Action &$action)
{
    $return = array(
        "success" => true,
        "error"   => array(),
        "body"    => array()
    );

    try {
        $appId = $action->parent->id;
        if (!is_numeric($appId)) {
            throw new Exception(sprintf("unexpected application id: %s", var_export($appId, true)));
        }

        $appName = $action->parent->name;
        $body = array();
        $adminActions = array();

        $query = <<< "SQL"
SELECT
    action.name,
    action.short_name,
    action.long_name,
    (select with_frame from application where id=$appId) as with_frame
FROM action
WHERE
    action.name in ('DOCS_ADMIN', 'TIMERS_ADMIN', 'SYSTEM_DOCS')
    AND action.id_application = $appId
;
SQL;
/*
    'DOCS_ADMIN',
    'TIMERS_ADMIN'
*/

        simpleQuery('', $query, $adminActions, false, false, true);

        foreach ($adminActions as $adminAction) {
            if(!$action->canExecute($adminAction["name"], $appId)){
                $actionUrl = "?app=$appName&action=".$adminAction["name"];
                if ($adminAction["with_frame"] !== 'Y') {
                    $actionUrl .= "&sole=A";
                }
                $body[] = array(
                    "url"   => $actionUrl,
                    "label" => _($adminAction["short_name"]),
                    "title" => (empty($adminAction["long_name"]) ? _($adminAction["short_name"]) : _($adminAction["long_name"]))
                );
            }
        }

        $body[]=array("url"=>"?app=FREEDOM&action=FREEDOM_MAINIMPORT",
            "label"=> _("docadmin : Import Documents"),
            "title" => _("docadmin : Import Documents")
        );
        $sortFunction = function ($value1, $value2) {
            return strnatcasecmp($value1["label"], $value2["label"]);
        };

        usort($body, $sortFunction);

        $return["body"] = $body;

    } catch (Exception $e) {
        $return["success"] = false;
        $return["error"][] = $e->getMessage();
        unset($return["body"]);
    }

    $action->lay->template = json_encode($return);
    $action->lay->noparse = true;
    header('Content-type: application/json');
}