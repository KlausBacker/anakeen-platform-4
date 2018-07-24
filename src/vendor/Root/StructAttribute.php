<?php

class StructAttribute
{
    public $id;
    public $setid;
    public $label;
    public $istitle;
    public $isabstract;
    public $type;
    public $format;
    public $order;
    public $access;
    public $isneeded;
    public $link;
    public $phpfile;
    public $phpfunc;
    public $elink;
    public $constraint;
    public $options;
    public $rawType;
    private $dataOrder = array(
        "id",
        "setid",
        "label",
        "istitle",
        "isabstract",
        "type",
        "order",
        "access",
        "isneeded",
        "link",
        "phpfile",
        "phpfunc",
        "elink",
        "constraint",
        "options"
    );

    public function __construct(array $data = array())
    {
        if (count($data) > 0) {
            $this->set($data);
        }
    }

    public function set(array $data)
    {
        $this->format="";
        $cid = 1;
        foreach ($this->dataOrder as $key) {
            if ($key == 'phpfunc') {
                $this->$key = isset($data[$cid]) ? $data[$cid] : '';
            } else {
                $this->$key = isset($data[$cid]) ? trim($data[$cid]) : '';
            }
            if ($key == 'type') {
                $this->rawType=$this->type;
                $this->type = strtok($this->type, "(");
                if (preg_match('/^([a-z]+)\(["\'](.+)["\']\)$/i', $this->rawType, $reg)) {
                    $this->format = $reg[2];
                }
            }


            $cid++;
        }
    }
}
