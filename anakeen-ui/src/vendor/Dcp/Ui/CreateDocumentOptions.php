<?php

namespace Dcp\Ui;

class CreateDocumentOptions extends \Dcp\Ui\ButtonOptions
{
    public $familyName;
    public $createLabel;
    public $htmlCreateContent;
    public $htmlEditContent;
    public $formValues = [];

    public function __construct($familyName = null)
    {
        parent::__construct();
        $this->familyName = $familyName;
        $this->htmlCreateContent = '<i class="fa fa-plus-circle" />';
        $this->htmlEditContent = '<i class="fa fa-pencil" />';
        $this->createLabel = ___("Create and insert to \"{{label}}\"", "ddui");
        $this->updateLabel = ___("Save and update \"{{label}}\"", "ddui");
        $this->windowWidth = "479px";
        $this->windowHeight = "400px";
    }
}
