import _ from "underscore";
import Backbone from "backbone";

export default Backbone.Router.extend({
  initialize: function router_initialize(options) {
    var currentRouter = this;
    this.useHistory = options.useHistory;

    this.document = options.document;

    //  eslint-disable-next-line no-useless-escape
    this.route(/api\/v2\/smart-elements\/([^\/]+)\.html/, "viewDocument");
    this.route(
      //  eslint-disable-next-line no-useless-escape
      /api\/v2\/smart-elements\/(.+)\/revisions\/([^\/]+)\.html/,
      "viewRevision"
    );
    this.route(
      //  eslint-disable-next-line no-useless-escape
      /api\/v2\/smart-elements\/(.+)\/views\/([^\/]+)\.html/,
      "viewView"
    );
    this.route(
      //  eslint-disable-next-line no-useless-escape
      /api\/v2\/smart-elements\/(.+)\/revisions\/([^\/]+)\/views\/([^\/]+)\.html/,
      "viewRevisionView"
    );

    // Listen to document sync and update url
    this.document.listenTo(this.document, "sync", function sync() {
      var viewId = currentRouter.document.get("viewId"),
        options = {
          path: window.location.pathname,
          initid: currentRouter.document.get("initid"),
          revision: currentRouter.document.get("revision") >= 0 ? currentRouter.document.get("revision") : undefined,
          viewId: undefined
        };
      var docProperties = currentRouter.document.getServerProperties();

      if (!options.initid) {
        if (docProperties.renderMode === "edit") {
          options.initid = docProperties.family.name;
        }
      }
      options.viewId = viewId;
      if (docProperties && docProperties.status === "alive") {
        // No write revision if not a fixed one
        options.revision = -1;
      }
      // api url
      currentRouter.rewriteApiUrl(options);
    });
  },

  /**
   * Rewrite URL if mismatches detected between server information and url access
   * @param options
   */
  rewriteApiUrl: function router_rewriteApiUrl(options) {
    var parsePath = false,
      parseHash = false,
      beginPath = "",
      urlSecondPart = "",
      locationSearch = window.location.search,
      locationHash = window.location.hash;
    var noRecordHistory;
    if (options.initid) {
      parsePath = window.location.pathname.match("(.*)api\\/v2\\/smart-elements\\/(.*)");
      if (parsePath) {
        beginPath = parsePath[1];

        if (options.viewId !== "!defaultConsultation") {
          urlSecondPart = "/views/" + encodeURIComponent(options.viewId);
          if (options.revision >= 0) {
            urlSecondPart += "/revisions/" + encodeURIComponent(options.revision);
          }
        } else {
          if (options.revision >= 0) {
            urlSecondPart = "/revisions/" + encodeURIComponent(options.revision);
          }
        }

        noRecordHistory = /smart-elements\/0\.html$/.test(options.path);

        parseHash = /#widgetValue{(.*)}/.exec(locationHash);
        if (parseHash) {
          try {
            var hashData = JSON.parse("{" + parseHash[1] + "}");

            delete hashData.viewId;
            delete hashData.initid;
            delete hashData.revision;

            if (hashData.customClientData) {
              locationSearch += locationSearch ? "&" : "?";
              locationSearch += "customClientData=";
              locationSearch += encodeURIComponent(JSON.stringify(hashData.customClientData));
              delete hashData.customClientData;
            }
            if (noRecordHistory) {
              locationHash = "";
            } else {
              locationHash = "#widgetValue" + JSON.stringify(hashData);
            }
          } catch (e) {
            //no test here
          }
        }

        if (!this.useHistory) {
          noRecordHistory = true;
        }

        this.navigate(
          beginPath +
            "api/v2/smart-elements/" +
            options.initid +
            urlSecondPart +
            ".html" +
            locationSearch +
            locationHash,
          { replace: noRecordHistory }
        );
      }
    }
  },
  viewDocument: function router_viewDocument(initid) {
    this.document.fetchDocument({ initid: initid });
  },
  viewRevision: function router_viewRevision(initid, revision) {
    this.document.fetchDocument({
      initid: initid,
      revision: revision
    });
  },

  viewView: function router_viewView(initid, viewId) {
    this.document.fetchDocument({
      initid: initid,
      viewId: viewId
    });
  },

  viewRevisionView: function router_viewRevisionView(initid, revision, viewId) {
    this.document.fetchDocument({
      initid: initid,
      revision: revision,
      viewId: viewId
    });
  },
  fetch: function router_fetch(searchPart) {
    var searchObject,
      newValues = {};
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
  getUrlParameters: function router_getUrlParameters(searchPart) {
    var extract,
      getParameters,
      result = {};

    if (searchPart) {
      getParameters = searchPart.substr(1).split("&");
      for (var i = 0; i < getParameters.length; i++) {
        extract = getParameters[i].match(/^([^=]+)=(.*)$/);
        if (extract) {
          result[extract[1]] = decodeURIComponent(extract[2]);
        } else {
          result[getParameters[i]] = "";
        }
      }
    }
    return result;
  }
});
