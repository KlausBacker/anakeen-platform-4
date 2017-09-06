<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class DocumentTemplateRender extends DocumentRender
{
    protected function getMainTemplate()
    {
        return '[[> templates]]';
    }
}
