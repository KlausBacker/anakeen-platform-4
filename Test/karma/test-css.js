define(function(require) {
    "use strict";

    require(['jquery'], function injectCss($) {
        var bust = "bust=" + (new Date()).getTime();
        //Inject CSS in the current page
        $('body')
            .append('<link rel="stylesheet" href="/dynacase/css/dcp/document/bootstrap.css?'+ bust+'" />')
            .append('<link rel="stylesheet" href="/dynacase/css/dcp/document/kendo.css?' + bust + '" />')
            .append('<link rel="stylesheet" href="/dynacase/css/dcp/document/document.css?' + bust + '" />');
    });

});