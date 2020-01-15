import $ from "jquery";
import Backbone from "backbone";
import Mustache from "mustache";
import attributeTemplate from "../../document/attributeTemplate";
import * as EventPromiseUtils from "../../../widgets/globalController/utils/EventUtils";

export default Backbone.View.extend({
  tagName: "li",

  className: "dcpTab__label dcpLabel",

  events: {
    'click a[href^="#action/"], a[data-action], button[data-action]': "externalLinkSelected"
  },

  displayLabel: true,

  initialize: function vTabLabel_initialize(options) {
    if (options.displayLabel === false || this.model.getOption("labelPosition") === "none") {
      this.displayLabel = false;
    }
    this.listenTo(this.model, "change:label", this.updateLabel);
    this.listenTo(this.model.get("content"), "add", this.render);
    this.listenTo(this.model.get("content"), "remove", this.render);
    this.listenTo(this.model.get("content"), "reset", this.render);
    this.listenTo(this.model, "errorMessage", this.setError);
    this.listenTo(this.model, "change:errorMessage", this.setError);
    this.listenTo(this.model, "destroy", this.remove);
    this.listenTo(this.model, "cleanView", this.remove);
    this.listenTo(this.model, "hide", this.hide);
    this.listenTo(this.model, "show", this.show);
  },

  render: function vTabLabel_render() {
    var label = this.model.get("label");
    var tooltipLabel = this.model.getOption("tooltipLabel");
    var attrData = this.model.attributes;

    this.$el.empty();
    if (this.displayLabel !== false) {
      if (this.model.getOption("attributeLabel")) {
        label = this.model.getOption("attributeLabel");
      }

      this.$el.html($('<span class="dcpLabel__text" />').text(label));

      attributeTemplate.insertDescription(this);
      if (tooltipLabel) {
        tooltipLabel = Mustache.render(tooltipLabel || "", attrData);
        if (!this.model.getOption("tooltipHtml")) {
          // Need encode itself because the dropselect tooltip also need
          tooltipLabel = $("<div/>")
            .text(tooltipLabel)
            .html();
        }
        this.$el.data("tooltipLabel", Mustache.render(tooltipLabel || "", attrData));
        this.$el.tooltip({
          placement: "top",
          container: ".dcpDocument",
          html: true,
          title: function vDocumentTooltipTitle() {
            if ($(this).find(".k-input.dcpTab__label__select").length > 0) {
              // It is a selected Tab
              return $(this).data("tooltipLabelSelect");
            }
            return $(this).data("tooltipLabel"); // set the element text as content of the tooltip
          }
        });
      }
    }

    this.$el.attr("data-attrid", this.model.id);

    return this;
  },

  setError: function vTabLabel_setError(event, data) {
    this.model.trigger("attributeTabChange", event, this.model.id, this.$el, {
      error: data
    });
    if (event.prevent) {
      return;
    }
    if (data) {
      this.$el.addClass("has-error");
    } else {
      this.$el.removeClass("has-error");
    }
  },

  updateLabel: function vTabLabel_updateLabel() {
    this.model.trigger("attributeTabChange", event, this.model.id, this.$el, {});
    this.$el.text(this.model.get("label"));
  },

  hide: function vTabLabel_hide() {
    this.$el.hide();
  },

  show: function vTabLabel_show() {
    this.$el.show();
  },

  externalLinkSelected: function vAttributeExternalLinkSelected(event) {
    var $target = $(event.currentTarget),
      action,
      options,
      eventOptions,
      documentModel,
      internalEvent = {
        prevent: false
      };

    event.preventDefault();
    if (event.stopPropagation) {
      event.stopPropagation();
    }

    action = $target.data("action") || $target.attr("href");
    const data = action.substring(8).split(/({(.+))/g);
    if (data[1]) {
      options = data[0].split(":").slice(0, -1);
      options.customClientData = JSON.parse(data[1]);
    } else {
      options = action.substring(8).split(":");
    }
    eventOptions = {
      target: event.target,
      index: -1,
      eventId: options.shift(),
      options: options
    };
    documentModel = this.model.getDocumentModel();

    this.model.trigger("internalLinkSelected", internalEvent, eventOptions);
    return EventPromiseUtils.getBeforeEventPromise(
      internalEvent,
      () => {
        documentModel.trigger("actionAttributeLink", internalEvent, options);
        return this;
      },
      () => {
        return this;
      }
    );
  }
});
