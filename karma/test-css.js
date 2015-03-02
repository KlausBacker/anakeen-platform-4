define(function(require) {
    "use strict";

    require('jquery');

    //Inject CSS in the current page
    $('body').append('<link rel="stylesheet" href="/dynacase/css/dcp/document/bootstrap.css" />').
        append('<link rel="stylesheet" href="/dynacase/css/dcp/document/kendo.css" />');

});