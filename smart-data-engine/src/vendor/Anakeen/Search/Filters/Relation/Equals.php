<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters\Relation;

use Anakeen\Search;

class Equals extends Search\Filters\IsEqual
{
    protected $compatibleType = array(
        'docid',
        'account',
    );
}
