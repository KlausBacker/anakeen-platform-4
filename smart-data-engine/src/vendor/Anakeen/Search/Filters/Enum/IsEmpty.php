<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters\Enum;

use Anakeen\Search;

class IsEmpty extends Search\Filters\IsEmpty
{
    protected $compatibleType = array(
        'enum',
    );
}
