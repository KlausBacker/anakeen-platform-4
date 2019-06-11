<?php
/*
 * @author Anakeen
 * @package FDL
 */

/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _TSTCOMMONINHERIT extends \Anakeen\Core\Internal\SmartElement

{
    /*
     * @end-method-ignore
    */
    /**
     * all document's folder are archieved
     * @apiExpose
     * @return string error message empty message if no error
     */

    protected function tstA()
    {
        return "Z";
    }

    protected function getANumber()
    {
        return "456";
    }

    public function registerHooks()
    {
        parent::registerHooks();
        // Need delete first because is used twice due to multiple inheritance
        $this->getHooks()->removeListeners(\Anakeen\SmartHooks::PREREFRESH);
        $this->getHooks()->addListener(\Anakeen\SmartHooks::PREREFRESH, function () {
            return $this->preRefresh();
        });
    }

    public function preRefresh()
    {
        return $this->tstA();
    }

    public function getAReference()
    {
        return $this->title . '/' . $this->getANumber();
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}

/*
 * @end-method-ignore
*/
?>