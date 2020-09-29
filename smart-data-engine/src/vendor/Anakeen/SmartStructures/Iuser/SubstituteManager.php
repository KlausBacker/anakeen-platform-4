<?php

namespace Anakeen\SmartStructures\Iuser;

use SmartStructure\Fields\Iuser;

class SubstituteManager
{
    /**
     * @param \SmartStructure\Iuser $user
     * @return bool true if period is activated
     */
    public static function isActivePeriod(\SmartStructure\Iuser $user): bool
    {
        if (!$user->getRawValue(Iuser::us_substitute)) {
            return false;
        }
        $startDate = $user->getRawValue(Iuser::us_substitute_startdate);
        $endDate = $user->getRawValue(Iuser::us_substitute_enddate);
        $now = date("Y-m-d");

        if ($startDate && $endDate) {
            if ($startDate <= $now && $now <= $endDate) {
                $activateSubstitute = true;
            } else {
                $activateSubstitute = false;
            }
        } elseif ($startDate && !$endDate) {
            if ($startDate <= $now) {
                $activateSubstitute = true;
            } else {
                $activateSubstitute = false;
            }
        } elseif (!$startDate && $endDate) {
            if ($now <= $endDate) {
                $activateSubstitute = true;
            } else {
                $activateSubstitute = false;
            }
        } else {
            $activateSubstitute = true;
        }
        return $activateSubstitute;
    }


}
