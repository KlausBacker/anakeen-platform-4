<?php

class ErrorCodeHUB
{
    /**
     * @errorCode The render is not found
     * The four error code are the same because it is used in four places.
     */
    const HUB0001 = 'The asset file at url : %s does not exist';
    /**
     * @errorCode The render is not found
     */
    const HUB0002 = 'The asset file at url : %s does not exist';
    /**
     * @errorCode The render is not found
     */
    const HUB0003 = 'The asset file at url : %s does not exist';
    /**
     * @errorCode The render is not found
     */
    const HUB0004 = 'The asset file at url : %s does not exist';
    /**
     * @errorCode The render is not found
     */
    const HUB0005 = 'Hub instance asset : the given static function "%s" is invalid';

    /**
     * @errorCode The render is not found
     */
    const HUB0006 = 'Hub entry asset : the given static function "%s" is invalid';

    /**
     * for beautifier
     */
    private function _bo()
    {
        if (true) {
            return;
        }
    }
}
