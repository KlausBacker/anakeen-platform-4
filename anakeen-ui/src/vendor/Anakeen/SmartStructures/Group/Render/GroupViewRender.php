<?php

namespace Anakeen\SmartStructures\Group\Render;

use Anakeen\Ui\DefaultConfigViewRender;
use \SmartStructure\Fields\Group as myAttributes;

class GroupViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);

        $options->enum(myAttributes::grp_hasmail)->setInputTooltip(
            sprintf(
                "<p>%s</p>",
                xml_entity_encode(___("To avoid compute mail addresses when group has lot of members", " smart group"))
            )
        );
        return $options;
    }
}
