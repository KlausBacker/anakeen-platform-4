<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Core\Account;
use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Search\SearchElements;
use Anakeen\SmartElementManager;
use Anakeen\Ui\Exception;
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
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->structureName = $args["hubId"];

        $this->hubInstance = SmartElementManager::getDocument($this->structureName);

        if (!$this->hubInstance || !$this->hubInstance->isAlive()) {
            throw new Exception("Unable to find ".$this->structureName);
        }

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
            $config = array_merge($config, $this->hubInstance->getConfiguration());
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

        $performRoles= $hubConfig->getMultipleRawValues(Fields::hub_execution_roles);
        $displayRoles = $hubConfig->getMultipleRawValues(Fields::hub_visibility_roles);

        if (!$performRoles && !$displayRoles) {
            return true;
        }
        $performRoleRef = [];
        foreach ($performRoles as $mandatoryRole) {
            $performRoleRef[] = SEManager::getRawValue($mandatoryRole, RoleFields::us_whatid);
        }
        $displayRoleRef=[];
        foreach ($displayRoles as $mandatoryRole) {
            $displayRoleRef[] = SEManager::getRawValue($mandatoryRole, RoleFields::us_whatid);
        }

        $user = ContextManager::getCurrentUser();
        if ($user->id == Account::ADMIN_ID) {
            return true;
        }
        $uMembers = $user->getMemberOf();
        if (!array_diff($performRoleRef, $uMembers)) {
            if (!$displayRoles) {
                return true;
            } else {
                // Verify display Access
                if (array_intersect($displayRoleRef, $uMembers)) {
                    return true;
                }
            }
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
