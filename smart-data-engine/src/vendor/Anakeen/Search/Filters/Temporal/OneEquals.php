<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters\Temporal;

use Anakeen\Search;

class OneEquals extends Search\Filters\OneEquals
{
    protected $compatibleType = array(
        'date',
        'timestamp',
        'time'
    );
}
