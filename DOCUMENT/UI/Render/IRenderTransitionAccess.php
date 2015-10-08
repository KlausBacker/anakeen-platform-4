<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

interface IRenderTransitionAccess {
    /**
     * Get Transition Render object to configure transition render
     *
     * @param string $transitionId transition identifier
     * @param \WDoc  $workflow workflow document
     *
     * @return TransitionRender
     */
    public function getTransitionRender($transitionId, \WDoc $workflow);
}
