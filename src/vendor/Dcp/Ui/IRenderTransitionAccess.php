<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

use SmartStructure\Wdoc;

interface IRenderTransitionAccess
{
    /**
     * Get Transition Render object to configure transition render
     *
     * @param string $transitionId transition identifier
     * @param Wdoc   $workflow     workflow document
     *
     * @return TransitionRender
     */
    public function getTransitionRender($transitionId, Wdoc $workflow);
}
