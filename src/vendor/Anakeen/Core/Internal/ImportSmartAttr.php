<?php

namespace Anakeen\Core\Internal;

class ImportSmartAttr
{
    public $id;
    public $idfield;
    public $label;
    public $isTitle;
    public $isAbstract;
    public $type;
    public $order;
    public $access;
    public $need;
    public $link;
    public $phpfile;
    public $phpfunc;
    public $elink;
    public $constraint;
    public $option;
    public $autocomplete;

    public function getData($key)
    {
        $props=[];
        if ($this->autocomplete) {
            $props["autocomplete"] = $this->autocomplete;
        }
        return [
            0 => $key,
            1 => $this->id,
            2 => $this->idfield,
            3 => $this->label,
            4 => $this->isTitle,
            5 => $this->isAbstract,
            6 => $this->type,
            7 => $this->order,
            8 => $this->access,
            9 => $this->need,
            10 => $this->link,
            11 => $this->phpfile,
            12 => $this->phpfunc,
            13 => $this->elink,
            14 => $this->constraint,
            15 => $this->option,
            "props" => $props
        ];
    }
}
