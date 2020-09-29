<?php

namespace Anakeen\SmartStructures\Iuser\Render;

use Anakeen\Core\Account;
use Anakeen\Core\AccountManager;
use Anakeen\Routes\Core\Lib\ApiMessage;
use SmartStructure\Fields\Iuser as IuserFields;

trait IuserMessage
{
    /**
     * Add warning messages to display
     *
     * @param \SmartStructure\Iuser $smartElement
     * @return ApiMessage[]
     */
    public function getUserMessages(\Anakeen\Core\Internal\SmartElement $smartElement)
    {
        $message = parent::getMessages($smartElement);

        if ($smartElement->getPropertyValue("initid") === '') {
            return $message;
        }
        if ($smartElement->getRawValue(IuserFields::us_status) === 'D') {
            $message[] = new ApiMessage(___("User is deactivated", "smart iuser"));
        }


        $iduser = $smartElement->getRawValue(IuserFields::us_whatid);
        if ($iduser > 0) {
            $user = $smartElement->getAccount();
            if (!$user->isAffected()) {
                $message[] = new ApiMessage(
                    sprintf(___("User #%d does not exist", "smart iuser"), $iduser),
                    ApiMessage::ERROR
                );
            } else {
                if (!$user->password) {
                    $message[] = new ApiMessage(___(
                        "User has no record password. It cannot be connected",
                        "smart iuser"
                    ));
                }
            }
        }
        return $message;
    }

    /**
     * @param \SmartStructure\Iuser|\SmartStructure\Igroup|\SmartStructure\Role $smartElement
     * @return ApiMessage[]
     */
    public function getAccountMessages(\Anakeen\Core\Internal\SmartElement $smartElement)
    {
        $message = parent::getMessages($smartElement);

        if (!$smartElement->getRawValue(IuserFields::us_whatid)) {
            $messages[] = new ApiMessage(
                sprintf(___("This account has no system identifier", "smart iuser")),
                ApiMessage::WARNING
            );
        } else {
            $account = $smartElement->getAccount();
            if (!$account) {
                $messages[] = new ApiMessage(sprintf(
                    ___("Account #%d does not exist", "smart iuser"),
                    $smartElement->getRawValue("us_whatid")
                ), ApiMessage::WARNING);
            }
        }
        return $message;
    }

    /**
     * Add information about substitute status
     *
     * @param \SmartStructure\Iuser $smartElement
     * @return string html fragment
     */
    public function getSubstituteMessages(\Anakeen\Core\Internal\SmartElement $smartElement)
    {
        $account = $smartElement->getAccount();
        $msg = "";
        if ($account) {
            if ($account->substitute) {
                $msg = $this->formatHtmlMsg(
                    ___("Substitute \"%s\" is activated", "smart iuser"),
                    Account::getDisplayName($account->substitute)
                );


                $endDate = $smartElement->getRawValue(IuserFields::us_substitute_enddate);
                if ($endDate) {
                    $now = new \DateTime(date("Y-m-d"));
                    $target = new \DateTime($endDate);
                    $interval = $now->diff($target);

                    $msg .= "\n" . $this->formatHtmlMsg(
                            n___("Disabling substitute tomorrow", "Disabling substitute in %d days",
                                ($interval->days + 1), "smart iuser"),
                            $interval->days + 1
                        );
                }
            } else {
                $substitute = $smartElement->getRawValue(IuserFields::us_substitute);
                if ($substitute) {
                    $msg = $this->formatHtmlMsg(
                        ___("Substitute \"%s\" is NOT activated", "smart iuser"),
                        Account::getDisplayName(AccountManager::getIdFromSEId($substitute))
                    );
                    $startDate = $smartElement->getRawValue(IuserFields::us_substitute_startdate);

                    if ($startDate) {
                        $now = new \DateTime(date("Y-m-d"));
                        $target = new \DateTime($startDate);
                        $interval = $now->diff($target);

                        if (!$interval->invert) {
                            $msg .= "\n" . $this->formatHtmlMsg(
                                    n___("Activation expected tomorrow", "Activation expected in %d days",
                                        $interval->days, "smart iuser"),
                                    $interval->days
                                );
                        } else {

                            $endDate = $smartElement->getRawValue(IuserFields::us_substitute_enddate);
                            if ($endDate) {
                                $now = new \DateTime(date("Y-m-d"));
                                $target = new \DateTime($endDate);
                                $interval = $now->diff($target);

                                if ($interval->invert) {
                                    $msg .= "\n" . $this->formatHtmlMsg(
                                            ___("Deactivation date has expired",  "smart iuser"),
                                            $interval->days
                                        );
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($msg) {
            return sprintf("<p>%s</p>", nl2br(($msg)));
        }
        return "";
    }

    private function formatHtmlMsg($msg, ...$args)
    {

        $format = str_replace(["%s", "%d"], ["<b>%s</b>", "<b>%d</b>"], htmlspecialchars($msg));
        return sprintf($format, ...$args);
    }
}
