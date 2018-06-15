<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 14/06/18
 * Time: 15:03
 */

namespace Anakeen\SmartStructures\Iuser\Render;


trait IuserMessage
{

    /**
     * Compute messages to display
     *
     * @param \Anakeen\Core\Internal\SmartElement $smartElement
     * @return array
     */
    public function getUserMessage(\Anakeen\Core\Internal\SmartElement $smartElement) {
        $message = [];

        if ($smartElement->getPropertyValue("initid") === '') {
            return $message;
        }
        if ($smartElement->getRawValue("us_status") == 'D') {
            $message[] = _("user is deactivated");
        }

        $iduser = $smartElement->getRawValue("us_whatid");
        if ($iduser > 0) {
            $user = $smartElement->getAccount();
            if (!$user->isAffected()) {
                $message[] = sprintf(_("user #%d does not exist"), $iduser);
            }
        }

        if ($smartElement->getRawValue('us_status', 'A') == 'A') {
            $message[] = ___("User deactivated");
        }

        return $message;
    }

}