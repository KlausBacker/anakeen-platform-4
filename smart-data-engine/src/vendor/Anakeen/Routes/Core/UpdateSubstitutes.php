<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\Account;
use Anakeen\Core\AccountManager;
use Anakeen\Core\SEManager;
use Anakeen\Search\Filters\IsNotEmpty;
use Anakeen\Search\Filters\OrOperator;
use Anakeen\Search\SearchElements;
use SmartStructure\Fields\Iuser;

/*
 * @note    Not used by any route
 */

class UpdateSubstitutes
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $s = new SearchElements("IUSER");
        $s->addFilter(new IsNotEmpty(Iuser::us_substitute));
        $s->addFilter(
            new OrOperator(
                new IsNotEmpty(Iuser::us_substitute_startdate),
                new IsNotEmpty(Iuser::us_substitute_enddate)
            )
        );


        $s->search();
        $users = $s->getResults();
        $errors = [];
        $messages = [];
        /** @var \SmartStructure\Iuser[] $users */
        foreach ($users as $user) {
            $err = $this->updateSubstitute($user, $message);
            $messages[] = $message;
            if ($err) {
                $errors[] = $err;
            }
        }

        $data = ["messages" => $messages, "errors" => $errors];

        return $response->withJson($data);
    }

    protected function updateSubstitute(\SmartStructure\Iuser $user, &$message)
    {
        $error = "";


        $todo = self::getActionTodo($user);
        $substituteId = $todo[1];
        $actionKey = $todo[0];
        switch ($actionKey) {
            case "KEEP":
                $message = sprintf(
                    "User \"%s\" has same substitute \"%s\"",
                    $user->getRawValue(Iuser::us_login),
                    AccountManager::getLoginFromId($substituteId)
                );
                break;
            case "CHANGE":
                $account = $user->getAccount();
                $error = $account->setSubstitute($substituteId);
                if (!$error) {
                    $message = sprintf(
                        "User \"%s\" has a new substitute \"%s\" ",
                        $user->getRawValue(Iuser::us_login),
                        AccountManager::getLoginFromId($substituteId)
                    );
                }
                break;
            case "REMOVE":
                $account = $user->getAccount();
                $err = $account->setSubstitute("");
                if (!$err) {
                    $message = sprintf(
                        "User \"%s\" has lost its substitute \"%s\" ",
                        $account->login,
                        AccountManager::getLoginFromId($substituteId)
                    );

                    $this->updateAccount($substituteId);
                }
                break;
            case "SKIP":
                $message = sprintf("User \"%s\" has no substitute ", $user->getRawValue(Iuser::us_login));
                break;
            case "ERROR":
                $error = sprintf(
                    "User \"%s\" has an invalid substitute (#%d) ",
                    $user->getRawValue(Iuser::us_login),
                    $user->getRawValue(Iuser::us_substitute)
                );
                break;
        }

        $startDate = $user->getRawValue(Iuser::us_substitute_startdate);
        $endDate = $user->getRawValue(Iuser::us_substitute_enddate);
        $periodMsg = sprintf("from %s to %s", $startDate, $endDate ?: "infinity");

        if ($message) {
            $message = sprintf("[%s]: %s: period %s", $actionKey, $message, $periodMsg);
        }

        return $error;
    }


    protected static function getActionTodo(\SmartStructure\Iuser $user)
    {
        $activateSubstitute = \Anakeen\SmartStructures\Iuser\SubstituteManager::isActivePeriod($user);

        $account = $user->getAccount();
        if ($account) {
            if ($activateSubstitute === true) {
                $substituteId = AccountManager::getIdFromSEId($user->getRawValue(Iuser::us_substitute));
                if (!$substituteId) {
                    return ["ERROR", $user->getRawValue(Iuser::us_substitute)];
                }
                if ($substituteId != $account->substitute) {
                    return ["CHANGE", AccountManager::getIdFromSEId($user->getRawValue(Iuser::us_substitute))];
                } else {
                    return ["KEEP", $account->substitute];
                }
            } else {
                if ($account->substitute) {
                    return ["REMOVE", $account->substitute];
                } else {
                    return ["SKIP", ""];
                }
            }
        }
        return "";
    }


    protected function updateAccount(int $id)
    {
        if ($id) {
            $substituteAccount = new Account("", $id);
            if ($substituteAccount) {
                /** @var \SmartStructure\Iuser $substitute */
                $substitute = SEManager::getDocument($substituteAccount->fid);
                if ($substitute) {
                    $substitute->refreshUserData();
                }
            }
        }
    }
}
