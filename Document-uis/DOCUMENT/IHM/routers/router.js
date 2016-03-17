/*global define*/
/*global define*/
define([
    'underscore',
    'backbone'
], function define_router(_, Backbone)
{
    'use strict';

    return Backbone.Router.extend({

        initialize: function router_initialize(options)
        {
            var currentRouter = this;

            this.document = options.document;

            this.route(/[^?]*\?app=DOCUMENT([^#]+)/, "fetch");
            // Listen to document sync and update url
            this.document.listenTo(this.document, "sync", function sync()
            {
                var searchPart = [], searchArguments = {};
                var useInitid = false, useViewid = false, useRevision = false;
                var viewId = currentRouter.document.get("viewId"),
                    options = {
                        "path": window.location.pathname,
                        "initid": currentRouter.document.get("initid"),
                        "revision": currentRouter.document.get("revision") >= 0 ? currentRouter.document.get("revision") : undefined,
                        "viewId": undefined
                    };
                var docProperties = currentRouter.document.getServerProperties();
                options.viewId = viewId;
                if (docProperties && docProperties.status === "alive") {
                    // No write revision if not a fixed one
                    options.revision = -1;
                }

                searchArguments = currentRouter.getUrlParameters(window.location.search);

                if (searchArguments.initid === options.initid.toString() &&
                    (searchArguments.revision === options.revision.toString() || (!searchArguments.revision && options.revision === -1)) &&
                    (searchArguments.viewId === options.viewId.toString() || (!searchArguments.viewId && options.viewId === "!defaultConsultation")) && !searchArguments.id) {
                    // The url not need to be rewrite : all arguments are correct with document server properties
                    return;
                }
                if (options.initid) {
                    // Extract all GET parameters and rewrite if needed
                    _.each(searchArguments, function routerGetUrl(getParameter, getKey)
                    {
                        if (getKey === "id") {
                            searchArguments[getKey] = null;
                            return;
                        }
                        if (getKey === "initid") {
                            useInitid = true;
                            searchArguments[getKey] = options.initid;
                            return;
                        }
                        if (getKey === "viewId") {
                            useViewid = true;
                            if (options.viewId === "!defaultConsultation") {
                                searchArguments[getKey] = null;
                            } else {
                                searchArguments[getKey] = options.viewId;
                            }
                            return;
                        }
                        if (getKey === "revision") {
                            useRevision = true;
                            if (options.revision === -1) {
                                searchArguments[getKey] = null;
                            } else {
                                searchArguments[getKey] = options.revision;
                            }
                        }
                    });

                    searchPart = _.compact(_.map(searchArguments, function router_composeSearchLocation(GETValue, GETKey)
                    {
                        if (GETValue === null) {
                            return null;
                        }
                        return GETKey + "=" + encodeURIComponent(GETValue);

                    }));
                    if (!useInitid) {
                        searchPart.push("initid=" + encodeURIComponent(options.initid));
                    }
                    if (!useViewid) {
                        if (options.viewId !== '!defaultConsultation') {
                            searchPart.push("viewId=" + encodeURIComponent(options.viewId));
                        }
                    }
                    if (!useRevision) {
                        if (options.revision >= 0) {
                            searchPart.push("revision=" + encodeURIComponent(options.revision));
                        }
                    }

                    currentRouter.navigate(window.location.pathname + '?' + searchPart.join('&') + window.location.hash);
                }
            });
        },

        fetch: function router_fetch(searchPart)
        {
            var searchObject, newValues = {};
            searchObject = this.getUrlParameters(searchPart);

            if (!_.isUndefined(searchObject.id)) {
                newValues.initid = searchObject.id;
            }
            if (!_.isUndefined(searchObject.initid)) {
                newValues.initid = searchObject.initid;
            }
            if (!_.isUndefined(searchObject.revision)) {
                newValues.revision = parseInt(searchObject.revision, 10);
            } else {
                newValues.revision = -1;
            }
            if (!_.isUndefined(searchObject.viewId)) {
                newValues.viewId = searchObject.viewId;
            } else {
                newValues.viewId = undefined;
            }
            this.document.fetchDocument(newValues);
        },

        /**
         * extract url parameters and return an simple object indexed by parameters keys
         * @param searchPart
         * @returns {{}}
         */
        getUrlParameters: function router_getUrlParameters(searchPart)
        {
            var extract,
                getParameters = searchPart.substr(1).split('&'),
                result = {};

            for (var i = 0; i < getParameters.length; i++) {
                extract = getParameters[i].match(/^([^=]+)=(.*)$/);
                if (extract) {
                    result[extract[1]] = decodeURIComponent(extract[2]);
                } else {
                    result[getParameters[i]] = "";
                }
            }
            return result;
        }

    });

});
