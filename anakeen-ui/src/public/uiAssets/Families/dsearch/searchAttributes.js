/*global define*/

/*
Return attributes list information from a family which can be used as criteria
 */


(function umdRequire(root, requireFunction)
{
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([], requireFunction);
    }
    root.dcp.translatorFactory = requireFunction([]);
}(window, function require_getSearchAttribute()
{
    "use strict";

    var searchAttributes={};
    var $ir={};

    return function getSearchAttributes(famid)
    {
        var $r=$.Deferred();

        if (searchAttributes[famid]==="processing") {
            $ir[famid].done(function () {
                $r.resolve(searchAttributes[famid]);
            });
        } else if (searchAttributes[famid]) {
            $r.resolve(searchAttributes[famid]);
        } else {
            $ir[famid]=$.Deferred();
            searchAttributes[famid]="processing";
            $.getJSON("api/v1/search_UI_HTML5/attributes/" + famid).done(function (data) {
                searchAttributes[famid]=data;
                $ir[famid].resolve();
                $r.resolve(searchAttributes[famid]);
            }).fail(function (response) {
                $r.reject(response);
            });
        }

        return $r;
    };
}));