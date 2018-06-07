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
    public $visibility;
    public $need;
    public $link;
    public $phpfile;
    public $phpfunc;
    public $elink;
    public $constraint;
    public $option;

    public function getData($key)
    {
        return [
            $key,
            $this->id,
            $this->idfield,
            $this->label,
            $this->isTitle,
            $this->isAbstract,
            $this->type,
            $this->order,
            $this->visibility,
            $this->need,
            $this->link,
            $this->phpfile,
            $this->phpfunc,
            $this->elink,
            $this->constraint,
            $this->option
        ];
    }
}
