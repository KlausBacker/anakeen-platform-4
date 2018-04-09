<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * help document for family
 */

namespace Anakeen\SmartStructures\Helppage;

class HelpPageHooks extends \Anakeen\SmartStructures\Document
{
    /**
     *
     * @return string
     */
    public function getCustomTitle()
    {
        $titles = $this->getHelpByLang();
        $user_lang = $this->getUserLang();
        if (count($titles) == 0) {
            return $this->title;
        }
        if (array_key_exists($user_lang, $titles)) {
            if ($titles[$user_lang]['help_name']) {
                return $titles[$user_lang]['help_name'];
            }
        } else {
            $item = array_shift($titles);
            if ($item['help_name']) {
                return $item['help_name'];
            }
        }
        return $this->title;
    }

    /**
     *
     * @global  $action
     * @return string
     */
    protected function getUserLang()
    {
        global $action;
        $user_lang = $action->getParam('CORE_LANG');
        return $user_lang;
    }

    /**
     *
     * @return array
     */
    protected function getHelpByLang()
    {
        $rows = $this->getArrayRawValues('help_t_help');

        $helps = array();
        foreach ($rows as $row) {
            $helps[$row['help_lang']] = $row;
        }

        return $helps;
    }
}
