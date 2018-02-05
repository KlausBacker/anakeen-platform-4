<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace Anakeen\Routes\Authent;

use Dcp\HttpApi\V1\Crud\Crud;
use Dcp\HttpApi\V1\Api\Exception;

class Login extends Crud
{
    const failDelay=2;

    /**
     * Create new ressource
     * @return mixed
     * @throws Exception
     */
    public function create()
    {

        $login=$this->urlParameters["login"];
        $password=isset($this->contentParameters["password"])?$this->contentParameters["password"]:null;
        $language=isset($this->contentParameters["language"])?$this->contentParameters["language"]:null;
        if (!isset($password)) {
            sleep(self::failDelay);
            $e = new Exception('AUTH0001', __METHOD__);
            $e->setHttpStatus('403', 'Forbidden');
            throw $e;
        }
        $user = new \Account();
        $user->setLoginName($login);
        $result=false;
        if ($user->isAffected()) {
            try {
                $result=$user->checkpassword($password);
            } catch (Exception $e) {

                sleep(self::failDelay);
                $e = new Exception('AUTH0001', __METHOD__);
                $e->setHttpStatus('403', 'Forbidden');
                throw $e;
            }
        } else if (!$user->isAffected()){
            sleep(self::failDelay);
            $e = new Exception('AUTH0001', __METHOD__);
            $e->setHttpStatus('403', 'Forbidden');
            throw $e;
        }
        if(!$result){
            sleep(self::failDelay);
            $e = new Exception('AUTH0001', __METHOD__);
            $e->setHttpStatus('403', 'Forbidden');
            throw $e;
        }
        $_SERVER['PHP_AUTH_USER']=$login;
        $session = new \Session();
        $session->set();
        $session->register('username', $login);
        if ($language) {
            $u=new \Account();
            $u->setLoginName($login);

            \Dcp\Core\ContextManager::initContext($u, "HTTPAPI_V1", "", \AuthenticatorManager::$session);
            \ApplicationParameterManager::setUserParameterValue("CORE", "CORE_LANG",$language );
        }

        return [];
    }

    /**
     * Close session to logout
     * @param string|int $resourceId Resource identifier
     * @return mixed
     */
    public function delete($resourceId)
    {
        $session = new \Session();
        $session->set();
        $session->close();
        return [];
    }

    /**
     * Read a ressource
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
     * Update the ressource
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