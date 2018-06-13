<?php

namespace Anakeen\Core\SmartStructure\Autocomplete;

use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class AccountList
{
    protected static $limit = 15;

    /**
     * list of Accounts
     *
     *
     * @param SmartAutocompleteRequest  $request
     * @param SmartAutocompleteResponse $response
     * @param                           $args
     * @return SmartAutocompleteResponse
     */
    public static function getAccounts(SmartAutocompleteRequest $request, SmartAutocompleteResponse $response, $args): SmartAutocompleteResponse
    {
        $sort = 'lastname';
        $searchinmail = false;
        $s = new \SearchAccount();
        $s->setSlice(self::$limit);
        if (!empty($args["usemailfilter"])) {
            $searchinmail = ($args["usemailfilter"] === "yes");
        }
        if (!empty($args["role"])) {
            $roles = explode(',', $args["role"]);

            foreach ($roles as $role) {
                try {
                    $s->addRoleFilter($role);
                } catch (\Exception $e) {
                    return $response->setError($e->getMessage());
                }
            }
        }
        if (!empty($args["group"])) {
            $groups = explode(',', $args["group"]);

            foreach ($groups as $group) {
                try {
                    $s->addGroupFilter($group);
                } catch (\Exception $e) {
                    return $response->setError($e->getMessage());
                }
            }
        }

        if (!empty($args["match"])) {
            $match = $args["match"];
            switch ($match) {
                case 'all':
                    break;

                case 'group':
                    $s->setTypeFilter($s::groupType);
                    break;

                case 'role':
                    $s->setTypeFilter($s::roleType);
                    break;

                default:
                    $s->setTypeFilter($s::userType);
            }
        } else {
            $s->setTypeFilter($s::userType);
        }

        if (!empty($args["smartstructure"])) {
            $match = trim($args["smartstructure"]);
            $s->filterFamily($match);
        }

        $condName = "";
        $filterName = $request->getFilterValue();
        if ($filterName) {
            $tname = explode(' ', $filterName);
            $condmail = '';
            if ($searchinmail) {
                $condmail = sprintf("|| ' ' || coalesce(mail,'')");
            }
            foreach ($tname as $name) {
                if ($condName) {
                    $condName .= " AND ";
                }
                $condName .= sprintf(
                    "(coalesce(firstname,'') || ' ' || coalesce(lastname,'') %s ~* '%s')",
                    $condmail,
                    pg_escape_string(SmartElementList::setDiacriticRules($name))
                );
            }
        }

        if ($condName) {
            $s->addFilter($condName);
        }
        if (!$sort) {
            $sort = 'lastname';
        }
        $s->setOrder($sort);
        $s->overrideViewControl(false);
        $al = $s->search();
        foreach ($al as $account) {
            $mail = $account->mail ? (' (' . mb_substr($account->mail, 0, 40) . ')') : '';

            $response->appendEntry(
                sprintf(
                    "<span>%s <i>%s</i></span>",
                    xml_entity_encode($account->lastname . " " . $account->firstname),
                    xml_entity_encode($mail)
                ),
                [
                    [
                        "value" => $account->fid,
                        "displayValue" => $account->lastname . " " . $account->firstname
                    ]
                ]
            );
        }


        return $response;
    }
}
