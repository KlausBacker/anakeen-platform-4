<?php

namespace Anakeen\Components\Grid;

use Anakeen\Core\Internal\FormatCollection;
use Anakeen\Search\ElementList;

class GridFormatCollection extends FormatCollection
{
    public function useElementList(ElementList &$l)
    {
        $this->dl = $l;
        return $this;
    }
}
