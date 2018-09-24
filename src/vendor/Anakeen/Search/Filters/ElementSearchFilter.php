<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters;

interface ElementSearchFilter {
    /**
     * Generate sql part
     * @param \SearchDoc $search
     * @return string sql where condition
     */
    public function addFilter(\SearchDoc $search);
}
