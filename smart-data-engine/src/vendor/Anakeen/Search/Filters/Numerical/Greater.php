<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters\Numerical;

use Anakeen\Search;

class Greater extends Search\Filters\IsGreater
{
    protected $compatibleType = array(
        'int',
        'double',
        'money',
    );
}
