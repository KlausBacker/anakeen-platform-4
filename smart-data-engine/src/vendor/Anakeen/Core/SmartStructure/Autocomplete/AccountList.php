<?php

namespace Anakeen\Core\SmartStructure\Autocomplete;

use Anakeen\Core\ContextManager;
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
        $s = new \Anakeen\Accounts\SearchAccounts();
        // Get the parameter value to know how many values to return
        $slice = ContextManager::getParameterValue("Core", "CORE_AUTOCOMPLETE_SLICE", 100);
        $s->setSlice($slice);
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
            // No use here mail group
            $mail = $account->mail ? (' (' . mb_substr($account->mail, 0, 40) . ')') : '';

            $response->appendEntry(
                sprintf(
                    "<span>%s <i>%s</i></span>",
                    \Anakeen\Core\Utils\Strings::xmlEncode($account->lastname . " " . $account->firstname),
                    \Anakeen\Core\Utils\Strings::xmlEncode($mail)
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
