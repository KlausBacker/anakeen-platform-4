(function umdRequire(root, factory) {
  "use strict";

  if (typeof define === "function" && define.amd) {
    define([
      "jquery",
      "underscore",
      "mustache",
      "dcpDocument/widgets/widget"
    ], factory);
  } else {
    //noinspection JSUnresolvedVariable
    factory(window.jQuery, window._, window.Mustache);
  }
})(window, function requireDcpLabel($, _, Mustache) {
  "use strict";

  $.widget("dcp.dcpLabel", {
    options: {
      renderOptions: {
        helpLinkIdentifier: 0
      },
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
      this.element.append(
        Mustache.render(this._getTemplate() || "", this.options)
      );
      if (
        this.options.renderOptions &&
        this.options.renderOptions.attributeLabel
      ) {
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

          action = $this.data("action") || $this.attr("href");
          options = action.substring(8).split(":");
          eventOptions = {
            target: event.target,
            index: -1,
            eventId: options.shift(),
            options: options
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

  return $.fn.dcpLabel;
});
