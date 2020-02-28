<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters\Title;

use Anakeen\Search;

class IsEmpty extends Search\Filters\IsEmpty
{

    /**
     * IsEmpty constructor.
     * @param int $options
     */
    public function __construct($options = 0)
    {
        parent::__construct('title', $options);
    }
}
