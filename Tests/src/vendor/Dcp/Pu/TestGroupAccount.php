<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;

use Anakeen\Core\SEManager;
use Anakeen\SmartStructures\Group\GroupHooks;
use Anakeen\SmartStructures\Iuser\IUserHooks;

/**
 * @author  Anakeen
 * @package Dcp\Pu
 */
//require_once 'PU_testcase_dcp_commonfamily.php';

class TestGroupAccount extends TestCaseDcpCommonFamily
{
    protected static function getCommonImportFile()
    {
        return array(
            'PU_data_dcp_groupaccount.ods'
        );
    }

    /**
     * @dataProvider dataClearGroup
     *
     * @param $groupId
     * @param $expectedContents
     */
    public function testClearGroup($groupId, $expectedContents)
    {
        /**
         * @var GroupHooks $group
         */
        $group = SEManager::getDocument($groupId, true);
        $this->assertTrue($group->isAlive(), sprintf("Could not get group with id '%s'.", $groupId));
        $err = $group->Clear();
        $this->assertEmpty($err, sprintf("Clear() on group with id '%s' returned unexpected error message: %s", $groupId, $err));
        foreach ($expectedContents as $expectedContent) {
            $subjectId = $expectedContent['subject'];
            $subject = SEManager::getDocument($subjectId, true);
            $this->assertTrue($subject && $subject->isAlive(), sprintf("Expected subject with id '%s' not found.", $subjectId));
            $check = $expectedContent['check'];
            $argv = isset($expectedContent['argv']) ? $expectedContent['argv'] : null;
            switch ($check) {
                case 'is-empty':
                    /**
                     * @var GroupHooks $subject
                     */
                    $this->assertTrue(is_a($subject, GroupHooks::class), sprintf("Subject with id '%s' is not of expected class '\\Dcp\\Core\\GroupAccount'.", $subjectId));
                    $content = $subject->getContent(false);
                    $this->assertCount(0, $content, sprintf("Unexpected content's count (%s) for subject with id '%s'.", count($content), $subjectId));
                    break;

                case 'has-no-parent':
                    /**
                     * @var IUserHooks $subject
                     */
                    $this->assertTrue(is_a($subject, IUserHooks::class), sprintf("Subject with id '%s' is not of expected class 'Dcp\\Core\\UserAccount'.", $subjectId));
                    $parents = $subject->getAllUserGroups();
                    $this->assertCount(0, $parents, sprintf("Unexpected parent's count (%s) for subject with id '%s'.", count($parents), $subjectId));
                    break;

                case 'has-not-parent':
                    /**
                     * @var IUserHooks $subject
                     */
                    $this->assertTrue(is_a($subject, IUserHooks::class), sprintf("Subject with id '%s' is not of expected class 'Dcp\\Core\\UserAccount'.", $subjectId));
                    $parents = $subject->getAllUserGroups();
                    $hasNotParent = true;
                    foreach ($parents as $sysId => $docId) {
                        $group = SEManager::getDocument($docId, true);
                        if (!$group) {
                            continue;
                        }
                        if ($group->name == $argv) {
                            $hasNotParent = false;
                        }
                    }
                    $this->assertTrue($hasNotParent, sprintf("User with id '%s' has unexpected parent '%s'.", $subjectId, $argv));
                    break;
            }
        }
    }

    public function dataClearGroup()
    {
        return array(
            array(
                'G_FOO',
                array(
                    array(
                        'subject' => 'G_FOO',
                        'check' => 'is-empty',
                    ),
                    array(
                        'subject' => 'U_FOO',
                        'check' => 'has-no-parents'
                    ),
                    array(
                        'subject' => 'G_BAR',
                        'check' => 'has-no-parents'
                    ),
                    array(
                        'subject' => 'U_BAR',
                        'check' => 'has-not-parent',
                        'argv' => 'G_FOO'
                    )
                )
            )
        );
    }
}
