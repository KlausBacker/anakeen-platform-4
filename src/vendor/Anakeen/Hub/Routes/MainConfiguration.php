<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Core\Account;
use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Search\SearchElements;
use Anakeen\SmartElementManager;
use SmartStructure\Fields\Hubconfiguration as Fields;
use SmartStructure\Fields\Role as RoleFields;
use SmartStructure\Fields\Hubinstanciation as InstanceFields;

/**
 * Class MainConfiguration
 *
 * @note used by route /hub/config/{hubId}
 */
class MainConfiguration extends \Anakeen\Components\Grid\Routes\GridContent
{
    protected $structureName = "";
    /**
     * @var $hubInstance \SmartStructure\Hubinstanciation
     */
    protected $hubInstance;
    protected $hasSuperRole;

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

        $this->hubInstance = SmartElementManager::getDocument($this->structureName);


        $search = new SearchElements("HUBCONFIGURATION");


        $search->overrideAccessControl();
        $search->addFilter("%s = '%s'", Fields::hub_station_id, $this->hubInstance->initid);
        $search->setOrder(Fields::hub_docker_position . ',' . Fields::hub_order);
        $search->search();
        $hubConfigurations = $search->getResults();
        $config = [
            "hubElements" => []
        ];
        if (!empty($this->hubInstance)) {
            $config = $this->hubInstance->getConfiguration();
        }
        /**
         * @var $hubConfig \SmartStructure\Hubconfiguration
         */
        foreach ($hubConfigurations as $hubConfig) {
            if ($this->userHasAccess($hubConfig)) {
                $config["hubElements"][] = $hubConfig->getConfiguration();
            }
        }
        return ApiV2Response::withData($response, $config);
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

        if ($this->hasSuperRole()) {
            return true;
        }
        return false;
    }
    protected function hasSuperRole()
    {
        if ($this->hasSuperRole === null) {
            $superRole= $this->hubInstance->getRawValue(InstanceFields::hub_super_role);

            $this->hasSuperRole= false;
            if ($superRole) {
                $user = ContextManager::getCurrentUser();
                $uMembers = $user->getMemberOf();
                $rid= SEManager::getRawValue($superRole, RoleFields::us_whatid);

                if (!array_diff([$rid], $uMembers)) {
                    $this->hasSuperRole= true;
                }
            }
        }
        return $this->hasSuperRole;
    }
}
