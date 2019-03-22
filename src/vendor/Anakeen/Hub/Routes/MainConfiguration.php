<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Core\Account;
use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Search\SearchElements;
use SmartStructure\Fields\Hubconfiguration as Fields;
use SmartStructure\Fields\Role as RoleFields;

/**
 * Class MainConfiguration
 *
 * @note used by route /hub/config/{hubId}
 */
class MainConfiguration extends \Anakeen\Components\Grid\Routes\GridContent
{
    protected $structureName = "";

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Search\Exception
     * @throws \Dcp\SearchDoc\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->structureName = $args["hubId"];
        $search = new SearchElements("HUBCONFIGURATION");

        if (!intval($this->structureName)) {
            $this->structureName = SEManager::getIdFromName($this->structureName);
        }

        $search->overrideAccessControl();
        $search->addFilter("%s = '%s'", Fields::hub_station_id, $this->structureName);
        $search->setOrder(Fields::hub_docker_position . ',' . Fields::hub_order);
        $search->search();
        $hubConfigurations = $search->getResults();
        $hubInstance = SEManager::getDocument($this->structureName);
        $return = [
            "hubElements" => []
        ];
        if (!empty($hubInstance)) {

            /**
             * @var $hubInstance \SmartStructure\Hubinstanciation
             */
            $return = $hubInstance->getConfiguration();
        }
        /**
         * @var $hubConfig \SmartStructure\Hubconfiguration
         */
        foreach ($hubConfigurations as $hubConfig) {
            if ($this->userHasAccess($hubConfig)) {
                $return["hubElements"][] = $hubConfig->getConfiguration();
            }
        }
        return ApiV2Response::withData($response, $return);
    }

    protected function userHasAccess(\SmartStructure\Hubconfiguration $hubConfig)
    {
        $mandatoryRoles = array_merge(
            $hubConfig->getMultipleRawValues(Fields::hub_execution_roles),
            $hubConfig->getMultipleRawValues(Fields::hub_visibility_roles)
        );
        if (!$mandatoryRoles) {
            return true;
        }
        $roles = [];
        foreach ($mandatoryRoles as $mandatoryRole) {
            $roles[] = SEManager::getRawValue($mandatoryRole, RoleFields::us_whatid);
        }

        $user = ContextManager::getCurrentUser();
        if ($user->id == Account::ADMIN_ID) {
            return true;
        }
        $uMembers = $user->getMemberOf();
        if (!array_diff($roles, $uMembers)) {
            return true;
        }
        return false;
    }
}
