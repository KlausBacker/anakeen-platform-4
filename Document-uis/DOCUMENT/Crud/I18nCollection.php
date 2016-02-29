<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
namespace Dcp\Ui\Crud;

use Dcp\HttpApi\V1\Crud\Exception;
use Dcp\HttpApi\V1\DocManager\DocManager as DocManager;
use Dcp\HttpApi\V1\Crud\Crud;

class I18nCollection extends Crud
{
    protected $userLocale = null;
    /**
     * Create new ressource
     * @throws Exception
     * @return mixed
     */
    public function create()
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot create an i18n element with the API");
        throw $exception;
    }

    /**
     * Read a ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @return mixed
     * @throws Exception
     */
    public function read($resourceId)
    {
        $currentLocale = $this->getUserLocale();
        $shortLocale = strtok($currentLocale, '_');
        
        if ($resourceId === "_all") {
            $file = sprintf("./locale/%s/js/catalog.js", $shortLocale);
        } else {
            $file = sprintf("./locale/%s/js/catalog-%s_%s.js", $shortLocale, $resourceId, $shortLocale);
        }
        if (!file_exists($file)) {
            $exception = new Exception("CRUDUI0009", $file);
            $exception->setHttpStatus("404", "Catalog file not found");
            throw $exception;
        }
        $catalog = json_decode(file_get_contents($file) , true);
        $response = array(
            "locale" => getLocaleConfig($currentLocale) ,
            "catalog" => $catalog
        );
        return $response;
    }
    /**
     * Update the ressource
     * @param string|int $resourceId Resource identifier
     * @throws Exception
     * @return mixed
     */
    public function update($resourceId)
    {
        
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot update an i18n element with the API");
        throw $exception;
    }
    /**
     * Delete ressource
     * @param string|int $resourceId Resource identifier
     * @throws Exception
     * @return mixed
     */
    public function delete($resourceId)
    {
        
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot delete an i18n element with the API");
        throw $exception;
    }
    /**
     *
     */
    protected function getUserLocale()
    {
        if ($this->userLocale === null) {
            $this->userLocale = \ApplicationParameterManager::getUserParameterValue("CORE", "CORE_LANG");
            if (empty($this->userLocale)) {
                $this->userLocale = "fr_FR";
            }
        }
        return $this->userLocale;
    }
    /**
     * Return etag info
     *
     * @return null|string
     */
    public function getEtagInfo()
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        return $version." ".$this->getUserLocale();
    }
}
