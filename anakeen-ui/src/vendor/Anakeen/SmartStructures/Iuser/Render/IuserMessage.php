<?php

namespace Anakeen\SmartStructures\Iuser\Render;

trait IuserMessage
{
    /**
     * Add warning messages to display
     *
     * @param \Anakeen\Core\Internal\SmartElement $smartElement
     * @return array
     */
    public function getUserMessage(\Anakeen\Core\Internal\SmartElement $smartElement)
    {
        $message = [];

        if ($smartElement->getPropertyValue("initid") === '') {
            return $message;
        }
        if ($smartElement->getRawValue("us_status") === 'D') {
            $message[] = ___("User is deactivated", "smart iuser");
        }

        /**
         * @var \SmartStructure\Iuser $smartElement
         */
        $iduser = $smartElement->getRawValue("us_whatid");
        if ($iduser > 0) {
            $user = $smartElement->getAccount();
            if (!$user->isAffected()) {
                $message[] = sprintf(___("User #%d does not exist", "smart iuser"), $iduser);
            }
        }

        return $message;
    }
}
