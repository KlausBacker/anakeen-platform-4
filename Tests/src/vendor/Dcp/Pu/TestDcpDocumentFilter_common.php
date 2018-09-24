<?php

namespace Dcp\Pu;

class TestDcpDocumentFilter_common extends TestCaseDcpCommonFamily
{
    public function common_testFilter($fam, $filter, $expected)
    {
        $s = new \SearchDoc("", $fam);
        $s->addFilter($filter);
        $s->setObjectReturn(false);
        $res = $s->search();
        $err = $s->getError();
        $this->assertEmpty($err, sprintf("Search returned with error: %s (query=[%s])", $err, $s->getOriginalQuery()));
        
        $found = array();
        foreach ($res as & $r) {
            $found[] = $r["name"];
        }
        unset($r);
        
        $missing = array_diff($expected, $found);
        $this->assertEmpty($missing, sprintf("Missing elements in result: %s (query=[%s])", join(", ", $missing) , $s->getOriginalQuery()));
        
        $spurious = array_diff($found, $expected);
        $this->assertEmpty($spurious, sprintf("Spurious elements in result: %s (query=[%s])", join(", ", $spurious) , $s->getOriginalQuery()));
    }
}
