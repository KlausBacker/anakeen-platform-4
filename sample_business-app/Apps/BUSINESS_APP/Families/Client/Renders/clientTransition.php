<?php
namespace Sample\BusinessApp\Renders;

use Dcp\AttributeIdentifiers\Ba_Prospect as MyAttr;
use Dcp\Ui\IRenderTransitionAccess;
use Dcp\Ui\TransitionRender;

class ClientTransitionsAccess implements IRenderTransitionAccess
{

    /**
     * Get Transition Render object to configure transition render
     *
     * @param string $transitionId transition identifier
     * @param \WDoc $workflow workflow document
     *
     * @return TransitionRender
     */
    public function getTransitionRender($transitionId, \WDoc $workflow)
    {
        return new ClientTransitions();
    }
}


class ClientTransitions extends \Dcp\Ui\TransitionRender
{
    /**
     * @param string $transitionId
     *
     * @return \Dcp\Ui\RenderOptions
     * @throws \Dcp\Ui\Exception
     */
    public function getRenderOptions($transitionId)
    {
        $options = parent::getRenderOptions($transitionId);
        // Change label of parameter frame
        $options->enum("wcli_checklist")->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay);
        return $options;
    }
}