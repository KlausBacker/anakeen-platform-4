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
    /**
     * @errorCode Content-type said json and content must be contains {document:{attributes:[]}
     */
    const ROUTES0106 = 'Record fail. Json object not contains attributes // example : {"document":{"attributes":{"attributeId" : {"value" : "newValue"}}}}';

    /**
     * @errorCode An attribute cannot be set
     */
    const ROUTES0107 = 'Update document "%s" fail - attribute "%s": "%s"';

    /**
     * @errorCode Content-type said json and content must be contains {document:{attributes:[]}
     */
    const ROUTES0108 = 'Record fail. Attribute "%s" has not "value" property in json';
    /**
     * @errorCode The document cannot be recorded
     */
    const ROUTES0109 = 'Update document "%s" fail  : "%s"';
}
