<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * INterface to send mail
 *
 */
namespace Dcp\Core;

class MailEdit extends \SmartStructure\Document
{
    public $defaultedit = "FDL:EDITMAILDOC";
    /**
     * @templateController
     */
    public function editmaildoc()
    {
        $this->editattr();
    }
}
