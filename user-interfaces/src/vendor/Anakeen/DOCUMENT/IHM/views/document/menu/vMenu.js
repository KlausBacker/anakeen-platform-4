import _ from "underscore";
import Backbone from "backbone";
import * as EventPromiseUtils from "../../../widgets/globalController/utils/EventUtils";
import "../../../widgets/menu/wMenu";

export default Backbone.View.extend({
  className: "dcpDocument",

  events: {
    dcpmenuredrawerrormessages: "redrawErrorMessages",
    dcpmenuexternallinkselected: "externalLinkSelected",
    dcpmenushowmessage: "showMessage",
    dcpmenureload: "reloadDocument"
  },

  /**
   * The current model is the document model
   * So menuModel reference the menu model
   */
  menuModel: null,

  initialize: function vMenuInitialize() {
    this.listenTo(this.model.get("properties"), "change", this.updateWidget);
    this.listenTo(this.model.get("menus"), "change", this.updateWidget);
    this.listenTo(this.model.get("menus"), "reload", this.updateWidget);
    this.listenTo(this.model.get("attributes"), "changeMenuVisibility", this.changeVisibility);
    this.listenTo(this.model, "destroy", this.remove);
    this.listenTo(this.model, "cleanView", this.remove);
    this.menuModel = this.model.get("menus");
  },

  render: function vMenuRender() {
    var currentView = this;
    return new Promise(
      _.bind(function vMenuRenderPromise(resolve, reject) {
        try {
          currentView.$el.dcpMenu(currentView.model.toData());
          currentView.refresh();
          return resolve(currentView);
        } catch (e) {
          reject(e);
        }
      }, this)
    );
  },

  externalLinkSelected: function vMenuExternalLinkSelected(event, options) {
    var internalEvent = {
      prevent: false
    };

    options.attrid = this.model.id;
    this.model.trigger("internalLinkSelected", internalEvent, options);
    return EventPromiseUtils.getBeforeEventPromise(
      internalEvent,
      () => {
        this.model.trigger("actionAttributeLink", internalEvent, options);
        return this;
      },
      () => {
        return this;
      }
    );
  },

  redrawErrorMessages: function vMenuRedrawMessageError() {
    this.model.trigger("redrawErrorMessages");
  },

  showMessage: function vMenushowMessage(event, options) {
    options.attrid = this.model.id;
    this.model.trigger("showMessage", options);

    return this;
  },
  reloadDocument: function vMenureloadDocument(event, options) {
    this.model.trigger("reload", options);

    return this;
  },
  changeVisibility: function vMenuchangeVisibility(event, data) {
    var menuItem = this.menuModel.get(data.id);
    var onlyIfVisible = !!data.onlyIfVisible;
    var visibility;
    if (menuItem) {
      visibility = menuItem.get("visibility");
      if (!onlyIfVisible || visibility !== "hidden") {
        menuItem.set("visibility", data.visibility);
      }
    }
  },

  updateWidget: function vMenuUpdateWidget() {
    if (this.$el.dcpMenu && this._findWidgetName(this.$el)) {
      this.$el.dcpMenu("destroy");
    }
    return this.render();
  },

  remove: function vMenuRemove() {
    if (this.$el.dcpMenu && this._findWidgetName(this.$el)) {
      this.$el.dcpMenu("destroy");
    }
    return Backbone.View.prototype.remove.call(this);
  },

  _findWidgetName: function vMenu_findWidgetName($element) {
    return _.find(_.keys($element.data()), function(currentKey) {
      return currentKey.indexOf("dcpDcp") !== -1;
    });
  },

  /**
   * Recompute responsive in case of scrollbar can appear
   */
  refresh: function vMenu_refresh() {
    if (this.$el.dcpMenu && this._findWidgetName(this.$el)) {
      this.$el.dcpMenu("updateResponsiveMenu");
    }
  }
});
