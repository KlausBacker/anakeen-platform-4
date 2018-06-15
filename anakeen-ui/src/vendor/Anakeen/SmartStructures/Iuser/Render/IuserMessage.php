<?php

namespace Anakeen\SmartStructures\Iuser\Render;

use Anakeen\Routes\Core\Lib\ApiMessage;

trait IuserMessage
{
    /**
     * Add warning messages to display
     *
     * @param \SmartStructure\Iuser $smartElement
     * @return array
     */
    public function getUserMessages(\Anakeen\Core\Internal\SmartElement $smartElement)
    {
        $message = parent::getMessages($smartElement);

        if ($smartElement->getPropertyValue("initid") === '') {
            return $message;
        }
        if ($smartElement->getRawValue("us_status") === 'D') {
            $message[] = new ApiMessage(___("User is deactivated", "smart iuser"));
        }


        $iduser = $smartElement->getRawValue("us_whatid");
        if ($iduser > 0) {
            $user = $smartElement->getAccount();
            if (!$user->isAffected()) {
                $message[] = new ApiMessage(sprintf(___("User #%d does not exist", "smart iuser"), $iduser), ApiMessage::ERROR);
            } else {
                if (!$user->password) {
                    $message[] = new ApiMessage(___("User has no record password. It cannot be connected", "smart iuser"));
                }
            }
        }
        return $message;
    }
}
