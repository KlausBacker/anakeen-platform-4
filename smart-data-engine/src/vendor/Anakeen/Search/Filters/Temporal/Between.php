<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters\Temporal;

use Anakeen\Search;

class Between extends Search\Filters\Between
{
    protected $compatibleType = array(
        'date',
        'timestamp',
        'time'
    );
}
