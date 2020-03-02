import $ from "jquery";
import Mustache from "mustache";
import "../../widget";

$.widget("dcp.dcpLabel", {
  options: {
    renderOptions: {},
    labels: {
      helpTitle: "Info"
    }
  },
  _create: function wLabel_create() {
    this._initDom();
    this._initActionClickEvent();
  },

  _initDom: function wLabel_initDom() {
    this.element.addClass("dcpAttribute__label control-label dcpLabel");
    this.element.append(Mustache.render(this._getTemplate() || "", this.options));
    if (this.options.renderOptions && this.options.renderOptions.attributeLabel) {
      this.setLabel(this.options.renderOptions.attributeLabel);
    }
  },

  /**
   * Init event for #action/ links
   *
   * @protected
   */
  _initActionClickEvent: function wAttributeInitActionClickEvent() {
    var scopeWidget = this;

    this.element.on(
      "click." + this.eventNamespace,
      'a[href^="#action/"], a[data-action], button[data-action]',
      function wAttributeActionClick(event) {
        var $this = $(this),
          action,
          options,
          eventOptions;

        event.preventDefault();
        if (event.stopPropagation) {
          event.stopPropagation();
        }

        // action = $target.data("action") || $target.attr("href");
        let data;
        if ($this.data("action")) {
          const action = $this.data("action");
          data = action.split(/({(.+))/g);
          if (data[1]) {
            options = data[0].split(":").slice(0, -1);
            options.customClientData = JSON.parse(data[1]);
          } else {
            options = action.split(":");
          }
        } else {
          action = $this.attr("href");
          data = action.substring(8).split(/({(.+))/g);
          if (data[1]) {
            options = data[0].split(":").slice(0, -1);
            options.customClientData = JSON.parse(data[1]);
          } else {
            options = action.substring(8).split(":");
          }
        }
        eventOptions = {
          target: event.target,
          eventId: options.shift(),
          options: options,
          index: -1
        };
        scopeWidget._trigger("externalLinkSelected", event, eventOptions);
        return this;
      }
    );
  },

  setLabel: function wLabelSetLabel(label) {
    this.element.find("label").text(label);
  },

  setError: function wLabelSetError(message) {
    if (message) {
      this.element.addClass("has-error");
    } else {
      this.element.removeClass("has-error");
    }
  },

  _getTemplate: function wLabel_getTemplate() {
    if (this.options.templates && this.options.templates.label) {
      return this.options.templates.label;
    }
    if (window.dcp && window.dcp.templates && window.dcp.templates.label) {
      return window.dcp.templates.label;
    }
    throw new Error("Unknown label template ");
  }
});
