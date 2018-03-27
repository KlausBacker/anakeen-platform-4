<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace Anakeen\Routes\Authent;

use Anakeen\Core\DocManager;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

/**
 * Class MailPassword
 * Send mail to reset password
 * @note Used by route : POST /api/v2/authent/mailPassword/{userId}
 * @package Anakeen\Routes\Authent
 */
class MailPassword
{
    const failDelay=2;


    /**
     * Send mail password
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     * @throws Exception
     */
    public function  __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $login=$args["userId"];
        // $language=$request->getParam("language");

        $user = new \Anakeen\Core\Account();
        $user->setLoginName($login);

        if (!$user->isAffected()) {
            $s = new \SearchAccount();

            $s->setTypeFilter(\SearchAccount::userType);
            $s->addFilter("mail = '%s'", $login);


            $s->overrideViewControl();
            /**
             * @var \AccountList $accounts
             */
            $accounts=$s->search();
            if ($accounts->count() === 1) {
                $user=$accounts->current();
            }
            if ($accounts->count() > 1) {
                 throw new Exception('AUTH0011', $login);
            }
        }
        $output=[];
        if ($user->isAffected()) {
            \Anakeen\Core\ContextManager::initContext($user, "CORE", "", \AuthenticatorManager::$session);

            $userDocument=DocManager::getDocument($user->fid);
            $mailTemplateId=\Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue("AUTHENT", "AUTHENT_MAILASKPWD");
            /**
             * @var \Dcp\Family\Mailtemplate $mailTemplate
             */
            $mailTemplate=DocManager::getDocument($mailTemplateId);
            if (!$mailTemplate) {
                throw new Exception('AUTH0010', $mailTemplateId);
            }


            $description="Reset password";
            $context=array([
                "methods"=>["PUT"],
                "pattern"=>"api/v2/authent/password/"
            ]);
            $expire=3600*24; // One day
            $oneshot=true;
            $tokenKey = \Anakeen\Router\AuthenticatorManager::getAuthorizationToken($user, $context, $expire, $oneshot , $description);

            $key["LINK_CHANGE_PASSWORD"]=sprintf("%s/login/?passkey=%s&uid=%s", \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("CORE_EXTERNURL"),  urlencode($tokenKey), urlencode($user->login));
            $err=$mailTemplate->sendDocument($userDocument, $key);
            if ($err) {
                 throw new Exception('AUTH0012', $err);
            }

        } else {
            sleep(self::failDelay);
            $e= new Exception('AUTH0013', $login);
            $e->setUserMessage(sprintf(___("Cannot find user \"%s\".", "authent"),$login ));
            throw $e;
        }

        // $output["debugurlpass"]=$key["LINK_CHANGE_PASSWORD"];
        $output["message"]=sprintf(___("An email has been sended to user \"%s\"", "authent"), $login);
        return ApiV2Response::withData($response, $output);
    }

}