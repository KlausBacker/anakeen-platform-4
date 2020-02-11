<?php

namespace Anakeen\Pu\Lib;

use Anakeen\Core\Internal\SmartElement;
use Dcp\Pu\TestCaseDcp;

class PuSeemMethod extends TestCaseDcp
{
    /**
     * Test Task definition import
     *
     * @dataProvider dataSeemsMethod
     * @param string $text
     * @param bool $expected
     */
    public function testSeemsMethod($text, bool $expected)
    {
        $this->assertEquals($expected, SmartElement::seemsMethod($text));
    }

    public function dataSeemsMethod()
    {
        return array(
            ["::aa aa()", false],
            ["aaaa()", false],
            ["::0aaaa()", false],
            ["aa::0aaaa()", false],
            ["aa::aaaaa()", true],
            ["aa::aa-aaa()", false],
            ["aa::aa_aaa()", true],
            ["aa::_aa_asz()", true],
            ["aa::aa()", true],
            ["a-a::aa()", false],
            ["a_a::aa()", true],
            ["_a_a::aa()", true],
            ["_a_a_::__aa()", true],
            ["::A()", true],
            ["::6()", false],
            ["A\\B::A()", true],
            ["My\\Company\\House::Testing()", true],
            ["M101\\C2\\H2::BigTesting()", true],
            ["M101\\C-2\\H2::BigTesting()", false]
        );
    }
}
