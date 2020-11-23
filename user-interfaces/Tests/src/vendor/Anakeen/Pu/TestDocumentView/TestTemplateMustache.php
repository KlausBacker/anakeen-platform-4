<?php

namespace Anakeen\Pu\TestDocumentView;

use Anakeen\Exception;
use Anakeen\Core\SEManager;

require_once __DIR__ . '/../Routes/TestCaseRoutes.php';
require DEFAULT_PUBDIR . '/vendor/Anakeen/lib/vendor/autoload.php';


class TestTemplateMustache extends \Anakeen\Pu\Routes\TestCaseRoutes
{

    protected static function getConfigFile()
    {
        return [__DIR__ . "/Data/tst_struct_to_document_view.xml"];
    }

    /**
     * Test Special Char Or Word On Footer Template
     *
     * @dataProvider dataFooterSection
     *
     * @param $uri
     * @param $customData
     * @param $expectedFooter
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testFooterSection($uri, $customData, $expectedFooter)
    {
        $elt = SEManager::createDocument("TST_DOCUMENT_VIEW");
        $err = $elt->store();
        if ($err) {
            throw new Exception($err);
        }
        $uri .= sprintf("?customClientData=%s", urlencode(json_encode(["charTestKey" => $customData])));
        $app = $this->setApiUriEnv($uri);
        $response = $app->run(true);
        $rawBody = (string)$response->getBody();
        $json = json_decode($rawBody);

        $this->assertEquals($expectedFooter, $json->data->view->templates->sections->footer, "Error, the char \"$customData\" on footer template is not in the correct place");
    }

    public function dataFooterSection()
    {
        $allCharTest = [
            "a",
            "b",
            "\b",
            "\t",
            "\n",
            "undefined",
            "undef",
            "null",
            "NULL",
            "(null)",
            "nil",
            "NIL",
            "true",
            "false",
            "True",
            "False",
            "TRUE",
            "FALSE",
            "None",
            "hasOwnProperty",
            "then",
            "constructor",
            ",",
            ".",
            "/",
            ";",
            "'",
            "[",
            "]",
            "\\",
            "-",
            "_",
            "=",
            "<",
            ">",
            "?",
            ":",
            "\"",
            "{",
            "}",
            "|",
            "+",
            "!",
            "@",
            "#",
            "$",
            "%",
            "^",
            "&",
            "*",
            "(",
            ")",
            "`",
            "~"
        ];
        $arrayTest = [];
        foreach ($allCharTest as $charTest) {
            $arrayTmp = array(
                'GET /api/v2/smart-elements/TST_DOCUMENT_VIEW/views/!defaultCreation',
                $charTest,
                "<footer class=\"smart-element-footer\">" . htmlspecialchars($charTest). "</footer>"
            );
            $arrayTest[] = $arrayTmp;
        }
        return $arrayTest;
    }

    /**
     * Test Special Char Or Word On Menu Template
     *
     * @dataProvider dataMenuSection
     *
     * @param $uri
     * @param $customData
     * @param $expectedMenu
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testMenuSection($uri, $customData, $expectedMenu)
    {
        $elt = SEManager::createDocument("TST_DOCUMENT_VIEW");
        $err = $elt->store();
        if ($err) {
            throw new Exception($err);
        }
        $uri .= sprintf("?customClientData=%s", urlencode(json_encode(["charTestKey" => $customData])));
        $app = $this->setApiUriEnv($uri);
        $response = $app->run(true);
        $rawBody = (string)$response->getBody();
        $json = json_decode($rawBody);

        $this->assertEquals($expectedMenu, $json->data->view->templates->sections->menu, "Error, the char \"$customData\" on menu template is not in the correct place");
    }

    public function dataMenuSection()
    {
        $allCharTest = [
            "a",
            "b",
            "\b",
            "\t",
            "\n",
            "undefined",
            "undef",
            "null",
            "NULL",
            "(null)",
            "nil",
            "NIL",
            "true",
            "false",
            "True",
            "False",
            "TRUE",
            "FALSE",
            "None",
            "hasOwnProperty",
            "then",
            "constructor",
            ",",
            ".",
            "/",
            ";",
            "'",
            "[",
            "]",
            "\\",
            "-",
            "_",
            "=",
            '<',
            ">",
            "?",
            ":",
            "\"",
            "{",
            "}",
            "|",
            "+",
            "!",
            "@",
            "#",
            "$",
            "%",
            "^",
            "&",
            "*",
            "(",
            ")",
            "`",
            "~"
        ];
        $arrayTest = [];
        foreach ($allCharTest as $charTest) {
            $arrayTmp = array(
                'GET /api/v2/smart-elements/TST_DOCUMENT_VIEW/views/!defaultCreation',
                $charTest,
                "<nav class=\"dcpDocument__menu\">" . htmlspecialchars($charTest). "</nav>"
            );
            $arrayTest[] = $arrayTmp;
        }
        return $arrayTest;
    }
}