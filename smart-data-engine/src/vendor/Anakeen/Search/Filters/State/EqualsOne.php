<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters\State;

use Anakeen\Search;

class EqualsOne extends Search\Filters\EqualsOne
{

    /**
     * EqualsOne constructor for state.
     * @param array $value
     * @param int $options <p>
     * Bitmask consisting of
     * <b>\Anakeen\Search\Filters\EqualsOne::$NOT</b>,
     * </p>
     */
    public function __construct($value, $options = 0)
    {
        parent::__construct('state', $value, $options);
    }
}
