<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Extra attribute test
 *
 * @author  Anakeen
 *
 * @package Dcp\Pu
 */

/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _TEST_EXTRA extends \Anakeen\Core\Internal\SmartElement

{
    /**
     * @end-method-ignore
     */

    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(\Anakeen\SmartHooks::PREIMPORT, function ($extra) {
            return $this->extraImport($extra);
        })->addListener(\Anakeen\SmartHooks::POSTIMPORT, function ($extra) {
            $err = parent::postImport($extra);
            if ($err == "") {
                $err = $this->SetValue("test_extra", json_encode($extra));
                if ($err == "") {
                    $err = $this->store();
                }
            }
            return $err;
        });
    }

    public function extraImport(array $extra = array())
    {
        if (empty($extra) || empty($extra["state"]) || ($extra["state"] != "alive" && $extra["num"] == "1") || ($extra["state"] != "sick" && $extra["num"] == "2")) {
            return _("TEST_EXTRA:Extra state not found");
        }

        return "";
    }

    public function postImport(array $extra = array())
    {
        $err = parent::postImport($extra);
        if ($err == "") {
            $err = $this->SetValue("test_extra", json_encode($extra));
            if ($err == "") {
                $err = $this->store();
            }
        }
        return $err;
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}
/**
 * @end-method-ignore
 */
