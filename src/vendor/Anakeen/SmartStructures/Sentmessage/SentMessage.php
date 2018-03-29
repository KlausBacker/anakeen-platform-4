<?php
/**
 * Sent email document
 */
namespace Anakeen\SmartStructures\Sentmessage;

class SentMessage extends \Anakeen\SmartStructures\Document
{

    /**
     * force no edition
     */
    public function control($aclname, $strict = false)
    {
        if (($this->id > 0) && ($this->doctype !== 'C') && ($aclname === "edit") && ($this->getFamilyParameterValue("emsg_editcontrol") != "freeedit")) {
            return _("electronic messages cannot be modified");
        } else {
            return parent::control($aclname, $strict);
        }
    }
}
