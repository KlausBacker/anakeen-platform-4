import $ from "jquery";
import _ from "underscore";
import Backbone from "backbone";
import Mustache from "mustache";

export default Backbone.View.extend({
  className: "dcpDocument",

  /**
   * The current model is the document model
   * The header template comes from template "sections/header"
   */
  headerTemplate: null,

  initialize: function vHeaderInitialize() {
    this.listenTo(this.model, "destroy", this.remove);
    this.listenTo(this.model, "cleanView", this.remove);
    this.listenTo(this.model, "changeValue", this.documentHasChanged);
    this.headerTemplate = this.getTemplates("sections").header;
  },

  /**
   * apply mustache template to inner content
   * @returns {*}
   */
  render: function vheaderRender() {
    var currentView = this;
    return new Promise(
      _.bind(function vheaderRenderPromise(resolve, reject) {
        try {
          var data = currentView.model.toData(),
            properties = currentView.model.getModelProperties(),
            security = properties.security || false;

          data.document.properties = properties;

          data.document.properties.security = security || {
            lock: { lockedBy: null }
          };
          data.document.properties.security.lock.isLocked =
            data.document.properties.security.lock.lockedBy && data.document.properties.security.lock.lockedBy.id > 0;
          data.href = currentView.model.url() + ".html";
          var headerRender = $(Mustache.render(currentView.headerTemplate || "", data));
          var $header = currentView.$el;
          $header.empty();
          _.each(headerRender.children(), function eachChildren(elt) {
            $header.append(elt);
          });

          $header
            .find(".dcpDocument__header__lock, .dcpDocument__header__readonly, .dcpDocument__header__modified")
            .tooltip({
              placement: "bottom",
              html: true
            });

          return resolve(currentView);
        } catch (e) {
          reject(e);
        }
      }, this)
    );
  },

  /**
   * reset mustache template
   * @returns {*}
   */
  updateHeader: function vheaderUpdateHeader() {
    return this.render();
  },

  /**
   * Get the the template of the header
   * @param key
   * @returns {*}
   */
  getTemplates: function vheadergetTemplates(key) {
    var templates = {};
    if (this.model && this.model.get("templates")) {
      templates = this.model.get("templates");
    }
    if (templates[key]) {
      return templates[key];
    }
    if (window.dcp && window.dcp.templates && window.dcp.templates[key]) {
      return window.dcp.templates[key];
    }
    throw new Error("Unknown template  " + key);
  },
  /**
   * Display has changed on the header
   */
  documentHasChanged: function vheaderdocumentHasChanged() {
    if (this.model.hasAttributesChanged()) {
      this.$el.find(".dcpDocument__header__modified").show();
    } else {
      this.$el.find(".dcpDocument__header__modified").hide();
    }
    if (this.model.hasUploadingFile()) {
      this.$el.find(".dcpDocument__header__modified").addClass("fa-spin");
    } else {
      this.$el.find(".dcpDocument__header__modified").removeClass("fa-spin");
    }
  }
});
