<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Routes\Authent;

use Dcp\HttpApi\V1\Crud\Crud;
use Dcp\HttpApi\V1\DocManager\DocManager;
use Dcp\HttpApi\V1\Api\Exception;

class Password extends Crud
{
    const failDelay = 2;

    /**
     * Reset password
     * @param string|int $resourceId Resource identifier
     * @return mixed
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     */
    public function update($resourceId)
    {

        $userIdentifier = $this->urlParameters["identifier"];
        $password = $this->contentParameters["password"];

        $currentUser = getCurrentUser();


        if ($currentUser->login !== $userIdentifier && $currentUser->mail !== $userIdentifier) {
            $e = new Exception("AUTH0020");
            $e->setUserMessage(___("Invalid key to update password", "authent"));
            throw $e;
        }

        /**
        * @var \Dcp\Core\UserAccount $udoc
        */
        $udoc = DocManager::getDocument($currentUser->fid);

        if ($udoc) {
            $err = $udoc->testForcePassword($password);
            if ($err) {
                throw new Exception("AUTH0021", $err);
            }
        }

        $currentUser->password_new = $password;
        $err = $currentUser->modify();
        if ($err) {
            throw new Exception("AUTH0021", $err);
        }

        return ["message" => sprintf(___("Password has been reset for \"%s\"", "authent"), $currentUser->getAccountName())];
    }


    /**
     * Create new ressource
     * @return mixed
     * @throws Exception
     */
    public function create()
    {
        $e = new \Dcp\HttpApi\V1\Crud\Exception('CRUD0103', __METHOD__);
        $e->setHttpStatus('405', 'You cannot create element with the API');
        throw $e;
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


}