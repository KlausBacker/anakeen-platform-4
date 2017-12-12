<?php
/**
 * Close session
 *
 */

function logout(Action &$action)
{
    $action->session->close();
    AuthenticatorManager::closeAccess();
}
