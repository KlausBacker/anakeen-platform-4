<?php

class ErrorCodeRoutes
{



    /**
     * @errorCode The ressource is not found
     */
    const ROUTES0100 = 'Document "%s" not found';
    /**
     * @errorCode The ressource cannot be get
     */
    const ROUTES0101 = 'Document "%s" access deny : %s';

    /**
     * @errorCode Document "%s" deleted
     */
    const ROUTES0102 = 'Document "%s" deleted';
    /**
     * @errorCode The fieds partial response indicate a wrong key
     */
    const ROUTES0103 = 'Document fields "%s" not known';
    /**
     * @errorCode Search document in family colection
     */
    const ROUTES0104 = 'Document "%s" is not a document of the family "%s"';
    /**
     * @errorCode The ressource is not found
     */
    const ROUTES0105 = 'Family "%s" not found';
}