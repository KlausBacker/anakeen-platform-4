<?php

namespace Dcp\Ui;

class MenuConfirmOptions extends MenuTargetOptions
{
    public $confirmButton = null;
    public $cancelButton = null;

    public function __construct()
    {
        $this->cancelButton = ___("Cancel", "UiMenu");
        $this->confirmButton = ___("Confirm", "UiMenu");
    }
}
