<?php


/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _TST_FAMIMP1 extends \Anakeen\Core\Internal\SmartElement
{
    /**
     * @end-method-ignore
     */
    public function registerHooks()
    {
        $this->getHooks()->addListener(\Anakeen\SmartHooks::PREIMPORT, function ($extra) {
            return $this->extraImport($extra);
        });
    }

    protected function extraImport(array $extra = array())
    {
        $tkey = $tval = array();
        foreach ($extra as $id => $val) {
            $tkey[] = $id;
            $tval[] = $val;
        }
        $this->setValue("tst_extrakey", $tkey);
        $this->setValue("tst_extraval", $tval);
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}

/**
 * @end-method-ignore
 */
?>
