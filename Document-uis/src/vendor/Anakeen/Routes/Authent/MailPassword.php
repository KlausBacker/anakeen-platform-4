<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace Anakeen\Routes\Authent;

use Dcp\HttpApi\V1\Crud\Crud;
use Dcp\HttpApi\V1\DocManager\DocManager;
use Dcp\HttpApi\V1\Api\Exception;

class MailPassword extends Crud
{
    const failDelay=2;

    /**
     * Create new ressource
     * @return mixed
     * @throws Exception
     */
    public function create()
    {

        $login=$this->urlParameters["identifier"];
        $language=isset($this->contentParameters["language"])?$this->contentParameters["language"]:null;





        $user = new \Account();
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

        if ($user->isAffected()) {
            $_SERVER['PHP_AUTH_USER']=$user->login;
            \Dcp\HttpApi\V1\ContextManager::initCoreApplication();

            $userDocument=DocManager::getDocument($user->fid);
            $mailTemplateId="AUTH_TPLMAILASKPWD";
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
                "route"=>"%authent/password$%"
            ]);
            $expire=3600*24; // One day
            $tokenKey = \Dcp\HttpApi\V1\AuthenticatorManager::getAuthorizationToken($user, $context, $expire, true , $description);

            $key["LINK_CHANGE_PASSWORD"]=sprintf("%s/login/?passkey=%s&uid=%s", \ApplicationParameterManager::getScopedParameterValue("CORE_EXTERNURL"),  urlencode($tokenKey), urlencode($user));
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

        return [];
    }

    /**
     * Delete a resource
     * @param string|int $resourceId Resource identifier
     * @return mixed
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     */
    public function delete($resourceId)
    {
        $e = new \Dcp\HttpApi\V1\Crud\Exception('CRUD0103', __METHOD__);
        $e->setHttpStatus('405', 'You cannot delete element with the API');
        throw $e;
    }

    /**
     * Read a resource
     * @param int|string $ressourceId
     * @return mixed
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     * @internal param int|string $resourceId Resource identifier
     */
    public function read($ressourceId)
    {
        $e = new \Dcp\HttpApi\V1\Crud\Exception('CRUD0103', __METHOD__);
        $e->setHttpStatus('405', 'You cannot consult element with the API');
        throw $e;
    }

    /**
     * Update the resource
     * @param string|int $resourceId Resource identifier
     * @return mixed
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     */
    public function update($resourceId)
    {
        $e = new \Dcp\HttpApi\V1\Crud\Exception('CRUD0103', __METHOD__);
        $e->setHttpStatus('405', 'You cannot update element with the API');
        throw $e;
    }

}