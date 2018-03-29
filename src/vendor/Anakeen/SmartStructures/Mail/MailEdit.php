<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * INterface to send mail
 *
 */
namespace Anakeen\SmartStructures\Mail;

class MailEdit extends \Anakeen\SmartStructures\Document
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
