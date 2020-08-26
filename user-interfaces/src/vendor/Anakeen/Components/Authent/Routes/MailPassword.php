<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Components\Authent\Routes;

use Anakeen\Core\SEManager;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\Core\Utils\Gettext;
use SmartStructure\Iuser;

/**
 * Class MailPassword
 * Send mail to reset password
 * @note    Used by route : POST /api/v2/authent/mailPassword/{userId}
 * @package Anakeen\Routes\Authent
 */
class MailPassword
{
    const failDelay = 2;


    /**
     * Send mail password
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     * @return \Slim\Http\response
     * @throws Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $login = $args["userId"];
        // $language=$request->getParam("language");

        $user = new \Anakeen\Core\Account();
        $user->setLoginName($login);

        if (!$user->isAffected()) {
            $s = new \Anakeen\Accounts\SearchAccounts();

            $s->setTypeFilter(\Anakeen\Accounts\SearchAccounts::userType);
            $s->addFilter("mail = '%s'", $login);


            $s->overrideViewControl();
            /**
             * @var \Anakeen\Accounts\AccountList $accounts
             */
            $accounts = $s->search();
            if ($accounts->count() === 1) {
                $user = $accounts->current();
            }
            if ($accounts->count() > 1) {
                throw new Exception('AUTH0011', $login);
            }
        }
        $output = [];
        if ($user->isAffected()) {
            \Anakeen\Core\ContextManager::initContext($user, \Anakeen\Core\Internal\AuthenticatorManager::$session);

            /** @var Iuser $userDocument */
            $userDocument = SEManager::getDocument($user->fid);
            $mailTemplateId = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "AUTHENT_MAILASKPWD");
            /**
             * @var \SmartStructure\Mailtemplate $mailTemplate
             */
            $mailTemplate = SEManager::getDocument($mailTemplateId);
            if (!$mailTemplate) {
                throw new Exception('AUTH0010', $mailTemplateId);
            }

            if (! $userDocument->getMail()) {
                $e = new Exception('AUTH0014', $userDocument->getTitle());
                $e->setUserMessage(sprintf(
                    Gettext::___("User \"%s\" has no email address. Is is mandatory to apply forget password process", "authent"),
                    $userDocument->getTitle()
                ));
                throw $e;
            }

            $description = "Reset password";
            $context = array(
                [
                    "methods" => ["PUT"],
                    "pattern" => "/api/v2/authent/password/".urlencode($login)
                ]
            );
            $expire = 3600 * 24; // One day
            $oneshot = true;
            $tokenKey = \Anakeen\Router\AuthenticatorManager::getAuthorizationToken($user, $context, $expire, $oneshot, $description);

            $key["LINK_CHANGE_PASSWORD"] = sprintf(
                "%s/login/?passkey=%s&uid=%s",
                \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_EXTERNURL"),
                urlencode($tokenKey),
                urlencode($user->login)
            );
            $currentTime = new \DateTime();
            $key["EXPIRE_DATE"] =  $currentTime->add(new \DateInterval("PT24H"))->format('Y-m-d H:i');
            $err = $mailTemplate->sendDocument($userDocument, $key);
            if ($err) {
                throw new Exception('AUTH0012', $err);
            }
        } else {
            sleep(self::failDelay);
            $e = new Exception('AUTH0013', $login);
            $e->setUserMessage(sprintf(Gettext::___("Cannot find user \"%s\".", "authent"), $login));
            throw $e;
        }
        // $output["debugurlpass"]=$key["LINK_CHANGE_PASSWORD"];
        $output["message"] = sprintf(Gettext::___("An email has been sent to user \"%s\"", "authent"), $login);
        return ApiV2Response::withData($response, $output);
    }
}
