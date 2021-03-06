<?php
/*
 * @author Anakeen
 * @package FDL
*/

class ErrorCodeCRUDUI
{
    /**
     * @errorCode To access to a view, document must have an associated view control
     */
    const CRUDUI0001 = 'Cannot access view "%s". Document "%s" has no view control';
    /**
     * @errorCode To access to a view,  associated view control must reference the view id
     */
    const CRUDUI0002 = 'Cannot access view "%s". View is not declared to "%s" view cnntrol associated to "%s" document';
    /**
     * @errorCode Access deny must have profil view control to access this view
     */
    const CRUDUI0003 = 'Access deny view "%s". From "%s" view cnntrol associated to "%s" document';
    /**
     * @errorCode restricted field for GET is not know
     */
    const CRUDUI0004 = 'Field "%s" is not available. Only "%s" are available';
    /**
     * @errorCode The editipn view can be use only with latest revision
     */
    const CRUDUI0005 = 'Cannot use edition view "%s" for a fixed revision';
    /**
     * @errorCode The view control asscoiated to document
     */
    const CRUDUI0006 = 'Cannot access to view control "%s" associated to the document "%s"';
    /**
     * @errorCode The return of getJsReferences method must be an array of descriptive js file
     * @see \Anakeen\Ui\IRenderConfig::getJsReferences
     */
    const CRUDUI0007 = 'Wrong js reference : list must be an array';
    /**
     * @errorCode Access deny to update with this view
     */
    const CRUDUI0008 = 'Access deny view "%s". From "%s" view control associated to "%s" document';
    /**
     * @errorCode Translation catalog not found
     */
    const CRUDUI0009 = 'Catalog "%s" not found';
    /**
     * @errorCode Cannot access user lock
     */
    const CRUDUI0010 = 'Cannot access user lock : %s';
    /**
     * @errorCode Cannot create view lock
     */
    const CRUDUI0011 = 'Cannot lock a special view "%s" : no render control is associated)';
    /**
     * @errorCode Cannot create view lock
     */
    const CRUDUI0012 = 'Cannot lock a special view "%s" : view not defined in "%s" render control)';
    /**
     * @errorCode Cannot access structure of no family documents
     */
    const CRUDUI0013 = 'Cannot access structure : resource "%s" is not a family)';
}
