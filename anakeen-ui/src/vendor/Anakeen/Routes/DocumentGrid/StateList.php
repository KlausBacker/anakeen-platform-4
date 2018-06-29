<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 06/11/15
 * Time: 10:54
 */

namespace Dcp\DocumentGrid\HTML5\REST;

use Dcp\HttpApi\V1\Crud\Crud;

class StateList extends Crud
{
    /**
     * Create new ressource
     *
     * @return mixed
     * @throws Exception
     */
    public function create()
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot create the state list");
        throw $exception;
    }
    /**
     * Read a ressource
     *
     * @param int|string $famId
     *
     * @return mixed
     * @internal param int|string $resourceId Resource identifier
     *
     */
    public function read($famId)
    {
        $return = array();
        
        $term = isset($this->contentParameters["term"]) ? $this->contentParameters["term"] : "";
        $famId = $this->urlParameters["familyId"];
        
        $currentFam = new_Doc('', $famId);
        $wids[] = $currentFam->wid;
        
        $wDoc = new_Doc('', $currentFam->wid, true);
        /* @var \Anakeen\SmartStructures\Wdoc\WDocHooks $wDoc */
        if ($wDoc->isAlive()) {
            foreach ($wDoc->getStates() as $currentState) {
                $currentActivity = $wDoc->getActivity($currentState, false);
                $currentLabel = $currentActivity ? : _($currentState);
                if (empty($term) || mb_strpos(mb_strtolower($currentLabel) , mb_strtolower($term)) !== false) {
                    $return[] = array(
                        'key' => $currentState,
                        'label' => $currentLabel,
                        'value' => $currentLabel
                    );
                }
            }
        }
        return $return;
    }
    /**
     * Update the ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @return mixed
     * @throws Exception
     */
    public function update($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot update the state list");
        throw $exception;
    }
    /**
     * Delete ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @return mixed
     * @throws Exception
     */
    public function delete($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot delete the state list");
        throw $exception;
    }
    public function getEtagInfo()
    {
        if (isset($this->urlParameters["identifier"])) {
            $result[] = $this->urlParameters["identifier"];
            $result[] = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_LANG");
            $result[] = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
            return implode(",", $result);
        }
        return null;
    }
}
