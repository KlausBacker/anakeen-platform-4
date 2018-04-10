<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

interface IRenderTransitionAccess {
    /**
     * Get Transition Render object to configure transition render
     *
     * @param string $transitionId transition identifier
     * @param \Anakeen\SmartStructures\Wdoc\WDocHooks  $workflow workflow document
     *
     * @return TransitionRender
     */
    public function getTransitionRender($transitionId, \WDoc $workflow);
}
