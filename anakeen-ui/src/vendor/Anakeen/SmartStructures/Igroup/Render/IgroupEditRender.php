<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Igroup\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use \SmartStructure\Attributes\Igroup as myAttributes;
use Dcp\Ui\ButtonOptions;
use Dcp\Ui\CreateDocumentOptions;

class IgroupEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        $options->enum(myAttributes::grp_hasmail)->setDisplay('bool');
        $options->enum(myAttributes::grp_hasmail)->displayDeleteButton(false);
        $roleButton = new ButtonOptions();
        $roleButton->url = "?app=FDL&action=OPENDOC&famid=ROLE&updateAttrid=grp_roles&autoclose=yes";
        $roleButton->target= "_dialog";
        $roleButton->class= "add-doc";
        $roleButton->title= "Créer un document Rôle";
        $roleButton->htmlContent= '<i class="fa fa-plus"></i>';
        $options->docid(myAttributes::grp_roles)->addButton($roleButton);
        return $options;
    }
}