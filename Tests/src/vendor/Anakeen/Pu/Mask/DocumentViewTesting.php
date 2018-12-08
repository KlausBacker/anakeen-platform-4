<?php

namespace Anakeen\Pu\Mask;

use Anakeen\Routes\Ui\DocumentView;

class DocumentViewTesting extends DocumentView
{
    public function setUiParameter($document, $view)
    {
        $this->document = $document;
        $this->viewIdentifier = $view;
    }

    public function getVisibilities()
    {
        $this->requestFields = [
            self::fieldRenderOptions
        ];
        $data = $this->doRequest();

        return ($data["view"]["renderOptions"]["visibilities"]);
    }
}