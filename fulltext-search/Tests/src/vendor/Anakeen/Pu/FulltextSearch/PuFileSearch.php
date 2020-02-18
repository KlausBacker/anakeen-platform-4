<?php

namespace Anakeen\Pu\FulltextSearch;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Exception;
use Anakeen\Fullsearch\FilterContains;
use Anakeen\Fullsearch\IndexFile;
use Anakeen\Fullsearch\SearchDomainDatabase;
use Anakeen\Search\SearchElements;
use Anakeen\TransformationEngine\Client;
use Anakeen\TransformationEngine\ClientException;

class PuFileSearch extends FulltextSearchConfig
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        try {
           new \Anakeen\TransformationEngine\Client();
        } catch (ClientException $e) {
            return;
        }


        self::importConfiguration(__DIR__ . "/Config/tst_file001.struct.xml");
        self::importDocument(__DIR__ . "/Config/tst_file001.data.xml");
        self::importSearchConfiguration(__DIR__ . "/Config/fileSearchDomainConfig.xml");

        $d = SEManager::getDocument("TST_EFILE_001", true, false);
        $d->setFile("tst_file", __DIR__ . "/Config/Files/modalitedeconfinement.odt");
        $d->store();


        $d = SEManager::getDocument("TST_EFILE_002", true, false);
        $d->setFile("tst_file", __DIR__ . "/Config/Files/cheval-sauvage.pdf");
        $d->store();



        $d = SEManager::getDocument("TST_EFILE_003", true, false);
        $d->setFile("tst_files", __DIR__ . "/Config/Files/pangolin.pdf", "", 0);
        $d->setFile("tst_files", __DIR__ . "/Config/Files/tortue.pdf", "", 1);
        $d->store();


        $d = SEManager::getDocument("TST_EFILE_004", true, false);
        $d->setFile("tst_file", __DIR__ . "/Config/Files/vikings.docx");
        $d->setFile("tst_files", __DIR__ . "/Config/Files/pangolin.pdf", "", 0);
        $d->setFile("tst_files", __DIR__ . "/Config/Files/tortue.pdf", "", 1);
        $d->store();


        self::waitForDomain("testDomainFile");


    }


    protected static function waitForDomain($domain)
    {
        $waitings = IndexFile::getWaitingRequest();
        $ot = new \Anakeen\TransformationEngine\Client();

        foreach ($waitings as $waiting) {
            $info = [];
            do {
                sleep(1);
                $err = $ot->getInfo($waiting->taskid, $info);
                if ($err) {
                    throw new Exception($err);
                }
            } while ($info["status"] !== Client::TASK_STATE_ERROR && $info["status"] !== Client::TASK_STATE_SUCCESS);

            if ($info["status"] !== Client::TASK_STATE_SUCCESS) {
                throw new Exception($info["comment"]);
            }
            IndexFile::recordTeFileresult($info["tid"]);

            $se = SEManager::getDocument($waiting->docid, false);
            $d = new SearchDomainDatabase($domain);
            $d->updateSmartWithFiles($se);

        }
    }

    /**
     * Test Text Get Document
     * Order by default : title
     * @dataProvider dataGetDocument
     * @param string $domain
     * @param string $searchPatten
     * @param array $expectedResults
     * @throws \Anakeen\Search\Exception
     */
    public function testContains($domain, $searchPatten, $expectedResults)
    {
        try {
            new \Anakeen\TransformationEngine\Client();
        } catch (ClientException $e) {
            $this->markTestSkipped("NO TE configured: ".$e->getMessage());
            return;
        }

        $s = new SearchElements();

        $filter = new FilterContains($domain, $searchPatten);
        $s->setSlice(10);
        $s->addFilter($filter);
        $results = $s->getResults();
        $names = [];
        foreach ($results as $smartElement) {
            $names[] = $smartElement->name;
        }

        $this->assertEquals($expectedResults, $names, print_r($s->getSearchInfo(), true));
    }


    public function dataGetDocument()
    {
        return array(
            ["testDomainFile", "ours massif", ["TST_EFILE_001"]],
            ["testDomainFile", "vétérinaire sanitaire", ["TST_EFILE_001"]],
            ["testDomainFile", "Le cheval de Przewalski", ["TST_EFILE_002"]],
            ["testDomainFile", "Liste Rouge des espèces menacées.", ["TST_EFILE_003", "TST_EFILE_004"]],
            ["testDomainFile", "La pêche de Thor", ["TST_EFILE_004"]],
        );
    }
}
