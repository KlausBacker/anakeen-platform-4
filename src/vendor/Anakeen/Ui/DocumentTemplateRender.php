<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class DocumentTemplateRender extends DocumentRender
{
    protected function getMainTemplate()
    {
        return '[[> templates]]';
    }
}
