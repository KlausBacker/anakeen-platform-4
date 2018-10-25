<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @package Dcp\Pu
 */

//require_once 'PU_testcase_dcp_commonfamily.php';
use Anakeen\Core\SEManager;

/**
 * test some SearchDoc option like generalFilter
 */
class TestSearchDirective extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FULLSERACHFAM1 family and some documents
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_fullsearchfamily1.ods";
    }
    
    protected $famName = 'TST_FULLSEARCHFAM1';


    /**
     * test "usefor" system search
     * @param string $dirId The identifer of the collection to use
     * @param array $existsNameList List of documents name that must be returned by the search
     * @param array $notExistsNameList List of documents name that must NOT be returned by the search
     * @return void
     * @dataProvider dataUseforSystemSearchDocWithCollection
     */
    public function testUseforSystemSearchDocWithCollection($dirId, $existsNameList, $notExistsNameList)
    {

        $dir = SEManager::getDocument($dirId);
        $this->assertTrue($dir->isAlive() , sprintf("Could not get search with id '%s'.", $dirId));
        
        $search = new \SearchDoc(self::$dbaccess, 0);
        $search->setObjectReturn();
        $search->useCollection($dirId);
        $search->addFilter("name ~ '^TST_USEFOR'");
        $search->search();
        
        $res = array();
        while ($doc = $search->getNextDoc()) {
            $res[] = $doc->name;
        }
        
        if (count($existsNameList) > 0) {
            foreach ($existsNameList as $name) {
                $this->assertTrue(in_array($name, $res) , sprintf("Missing document with name '%s' in search with collection '%s': returned documents name = {%s}", $name, $dir->name, join(', ', $res)));
            }
        }
        
        if (count($notExistsNameList) > 0) {
            foreach ($notExistsNameList as $name) {
                $this->assertTrue(!in_array($name, $res) , sprintf("Found unexpected document with name '%s' in search with collection '%s': returned documents name = {%s}", $name, $dir->name, join(', ', $res)));
            }
        }
    }
    
    public function dataErrorGeneralFilter()
    {
        return array(
            array(
                "test ()",
                "SD0004"
            ) ,
            array(
                "(test) or (test",
                "SD0004"
            ) ,
            array(
                "(test) or )test(",
                "SD0004"
            ) ,
            array(
                '(coucou) OR " (DF"Oio)',
                "SD0003"
            )
        );
    }
    
    public function dataUseforSystemSearchDocWithCollection()
    {
        return array(
            array(
                "TST_USEFOR_SYSTEM_SEARCH_NO",
                array(
                    "TST_USEFOR_N_1",
                    "TST_USEFOR_N_2",
                    "TST_USEFOR_N_3"
                ) ,
                array(
                    "TST_USEFOR_S_1",
                    "TST_USEFOR_S_2",
                    "TST_USEFOR_S_3"
                )
            ) ,
            array(
                "TST_USEFOR_SYSTEM_SEARCH_YES",
                array(
                    "TST_USEFOR_S_1",
                    "TST_USEFOR_S_2",
                    "TST_USEFOR_S_3",
                    "TST_USEFOR_N_1",
                    "TST_USEFOR_N_2",
                    "TST_USEFOR_N_3"
                ) ,
                array()
            ) ,
            array(
                "TST_USEFOR_SYSTEM_SEARCH_EMPTY",
                array(
                    "TST_USEFOR_S_1",
                    "TST_USEFOR_S_2",
                    "TST_USEFOR_S_3",
                    "TST_USEFOR_N_1",
                    "TST_USEFOR_N_2",
                    "TST_USEFOR_N_3"
                ) ,
                array()
            ) ,
            array(
                "TST_USEFOR_SYSTEM_DSEARCH_NO",
                array(
                    "TST_USEFOR_N_1",
                    "TST_USEFOR_N_2",
                    "TST_USEFOR_N_3"
                ) ,
                array(
                    "TST_USEFOR_S_1",
                    "TST_USEFOR_S_2",
                    "TST_USEFOR_S_3"
                )
            ) ,
            array(
                "TST_USEFOR_SYSTEM_DSEARCH_YES",
                array(
                    "TST_USEFOR_S_1",
                    "TST_USEFOR_S_2",
                    "TST_USEFOR_S_3",
                    "TST_USEFOR_N_1",
                    "TST_USEFOR_N_2",
                    "TST_USEFOR_N_3"
                ) ,
                array()
            ) ,
            array(
                "TST_USEFOR_SYSTEM_DSEARCH_EMPTY",
                array(
                    "TST_USEFOR_S_1",
                    "TST_USEFOR_S_2",
                    "TST_USEFOR_S_3",
                    "TST_USEFOR_N_1",
                    "TST_USEFOR_N_2",
                    "TST_USEFOR_N_3"
                ) ,
                array()
            )
        );
    }


    /**
     * Test SearchDoc->onlyCount() method
     * @param string $fam family id or name
     * @param array $properties list of ($propertyName => $propertyValue) to be set on the SearchDoc object  (e.g. array("only" => true))
     * @param array $methods list of ($methodName) to be called on the SearchDoc object (e.g. array("noViewControl") to call $search->noViewControl())
     * @param array $filters list of SQL conditions/filters to be added with the $search->addFilter() method (e.g. array("foo <> 'bar'"))
     * @param int $expectedCount expected documents count
     * @return void
     * @dataProvider dataSearchDocOnlyCount
     */
    public function testSearchDocOnlyCount($fam, $properties, $methods, $filters, $expectedCount)
    {
        $search = new \SearchDoc(self::$dbaccess, $fam);
        if (is_array($properties)) {
            foreach ($properties as $prop => $value) {
                $search->$prop = $value;
            }
        }
        if (is_array($methods)) {
            foreach ($methods as $method) {
                $search->$method();
            }
        }
        if (is_array($filters)) {
            foreach ($filters as $filter) {
                $call = array(
                    $search,
                    "addFilter"
                );
                if (is_array($filter)) {
                    $args = $filter;
                } else {
                    $args = array(
                        $filter
                    );
                }
                call_user_func_array($call, $args);
            }
        }
        $count = $search->onlyCount();
        
        $this->assertTrue(($count == $expectedCount) , sprintf("onlyCount() returned '%s' while expecting '%s' (query = [%s]).", $count, $expectedCount, $search->getOriginalQuery()));
    }
    
    public function dataSearchDocOnlyCount()
    {
        return array(
            array(
                "TST_ONLYCOUNT_0",
                array(
                    "only" => false
                ) ,
                array(
                    "overrideViewControl"
                ) ,
                array() ,
                3 + 4
            ) ,
            array(
                "TST_ONLYCOUNT_0",
                array(
                    "only" => true
                ) ,
                array(
                    "overrideViewControl"
                ) ,
                array() ,
                3
            ) ,
            array(
                "TST_ONLYCOUNT_0",
                array(
                    "only" => false
                ) ,
                array(
                    "overrideViewControl"
                ) ,
                array(
                    "title <> 'Just to add some SQL conditions in the query...'",
                    "title <> '... blah blah blah'"
                ) ,
                3 + 4
            ) ,
            array(
                "TST_ONLYCOUNT_0",
                array(
                    "only" => true
                ) ,
                array(
                    "overrideViewControl"
                ) ,
                array(
                    "title <> 'Just to add some SQL conditions in the query...'",
                    "title <> '... blah blah blah'"
                ) ,
                3
            ) ,
            array(
                "TST_ONLYCOUNT_0",
                array(
                    "only" => false
                ) ,
                array(
                    "overrideViewControl"
                ) ,
                array(
                    "a_title <> 'Just to add some SQL conditions in the query...'",
                    "a_title <> '... blah blah blah'"
                ) ,
                3 + 4
            ) ,
            array(
                "TST_ONLYCOUNT_0",
                array(
                    "only" => true
                ) ,
                array(
                    "overrideViewControl"
                ) ,
                array(
                    "a_title <> 'Just to add some SQL conditions in the query...'",
                    "a_title <> '... blah blah blah'"
                ) ,
                3
            )
        );
    }
    /**
     * Test setOrder by label on enum attributes
     *
     * @dataProvider dataSearchDocSetOrder
     */
    function testSearchDocSetOrder($fam, $orderby, $orderbyLabel, $expectedCount, $expectedTitles = array())
    {
        $search = new \SearchDoc(self::$dbaccess, $fam);
        $search->setObjectReturn(true);
        $search->setOrder($orderby, $orderbyLabel);
        $search->search();
        
        $count = $search->count();
        $this->assertTrue($count == $expectedCount, sprintf("search with setOrder(%s, %s) returned '%s' elements while expecting '%s'.", var_export($orderby, true) , var_export($orderbyLabel, true) , $count, $expectedCount));
        
        $titles = array();
        while ($doc = $search->getNextDoc()) {
            $titles[] = $doc->title;
        }
        
        $s1 = join(', ', $titles);
        $s2 = join(', ', $expectedTitles);
        $this->assertTrue($s1 == $s2, sprintf("Expected titles not found: titles = [%s] / expected titles = [%s] / sql = [%s]", $s1, $s2, $search->getOriginalQuery()));
    }
    
    function dataSearchDocSetOrder()
    {
        return array(
            array(
                'TST_ORDERBY_LABEL',
                'a_enum',
                'a_enum',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                'a_enum asc',
                'a_enum',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                '-a_enum',
                'a_enum',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                'a_enum desc',
                'a_enum',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                'a_docid_0',
                'a_docid_0',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                '-a_docid_0',
                'a_docid_0',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                'a_docid_1',
                'a_docid_1',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                '-a_docid_1',
                'a_docid_1',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                'a_docid_2',
                'a_docid_2',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                '-a_docid_2',
                'a_docid_2',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            )
        );
    }
    /**
     * Test setOrder by label on enum attributes
     *
     * @dataProvider dataSearchDocSetOrderWithCollection
     */
    function testSearchDocSetOrderWithCollection($collectionId, $orderby, $orderbyLabel, $expectedCount, $expectedTitles = array())
    {
        $search = new \SearchDoc(self::$dbaccess);
        $search->useCollection($collectionId);
        $search->setObjectReturn(true);
        $search->setOrder($orderby, $orderbyLabel);
        $search->search();
        
        $count = $search->count();
        $this->assertTrue($count == $expectedCount, sprintf("search with setOrder(%s, %s) returned '%s' elements while expecting '%s'.", var_export($orderby, true) , var_export($orderbyLabel, true) , $count, $expectedCount));
        
        $titles = array();
        while ($doc = $search->getNextDoc()) {
            $titles[] = $doc->title;
        }
        
        $s1 = join(', ', $titles);
        $s2 = join(', ', $expectedTitles);
        $this->assertTrue($s1 == $s2, sprintf("Expected titles not found: titles = [%s] / expected titles = [%s] / sql = [%s]", $s1, $s2, $search->getOriginalQuery()));
    }
    
    function dataSearchDocSetOrderWithCollection()
    {
        return array(
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                'a_enum',
                'a_enum',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                'a_enum asc',
                'a_enum',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                '-a_enum',
                'a_enum',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                'a_enum desc',
                'a_enum',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                'a_docid_0',
                'a_docid_0',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                '-a_docid_0',
                'a_docid_0',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                'a_docid_1',
                'a_docid_1',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                '-a_docid_1',
                'a_docid_1',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                'a_docid_2',
                'a_docid_2',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                '-a_docid_2',
                'a_docid_2',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            )
        );
    }
    

    
    public function dataSpellGeneralFilter()
    {
        return array(
            array(
                "téléfone",
                array(
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            
            array(
                "téléfone maizon",
                array(
                    "TST_FULL2"
                )
            ) ,
            array(
                "téléfone méson",
                array(
                    "TST_FULL2"
                )
            ) ,
            array(
                '"fixe" mésons',
                array(
                    "TST_FULL2"
                )
            )
        );
    }


}