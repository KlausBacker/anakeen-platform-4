<?php

namespace Anakeen\Core\SmartStructure;

class MenuAttribute extends BasicAttribute
{
    public $link; // hypertext link
    public $precond; // pre-condition to activate menu
    public function __construct($id, $docid, $label, $order, $link, $visibility = "", $precond = "", $options = "", $docname = "")
    {
        $this->id = $id;
        $this->structureId = $docid;
        $this->labelText = $label;
        $this->ordered = $order;
        $this->link = $link;
        $this->access = $visibility;
        $this->options = $options;
        $this->precond = $precond;
        $this->type = "menu";
        $this->docname = $docname;
    }
}

