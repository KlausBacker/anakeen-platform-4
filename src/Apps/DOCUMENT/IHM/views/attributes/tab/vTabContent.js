/*global define, console*/
define([
  "jquery",
  "underscore",
  "backbone",
  "mustache",
  "dcpDocument/views/attributes/frame/vFrame",
  "dcpDocument/views/document/attributeTemplate",
  "dcpDocument/i18n/documentCatalog"
], function vTabContent(
  $,
  _,
  Backbone,
  Mustache,
  ViewAttributeFrame,
  attributeTemplate,
  i18n
) {
  "use strict";

  return Backbone.View.extend({
    tagName: "div",

    className: "dcpTab__content",
    customView: false,

    initialize: function vTabContentInitialize(options) {
      this.listenTo(this.model, "change:label", this.updateLabel);
      this.listenTo(this.model.get("content"), "add", this.render);
      this.listenTo(this.model.get("content"), "remove", this.render);
      this.listenTo(this.model.get("content"), "reset", this.render);
      this.listenTo(this.model, "cleanView", this.remove);
      this.listenTo(this.model, "destroy", this.remove);
      this.listenTo(this.model, "showTab", this.renderContent);
      this.listenTo(this.model, "hide", this.hide);
      this.listenTo(this.model, "show", this.show);
      this.listenTo(this.model, "haveView", this._identifyView);
      this.initializeContent = options.initializeContent;
      this.initializing = false;
      this.options = options;
    },

    render: function vTabContentRender() {
      var currentView = this;
      return new Promise(
        _.bind(function vTabContentRender_Promise(resolve, reject) {
          try {
            var hasOneContent;
            currentView.$el
              .empty()
              .append(
                $(
                  '<div class="dcpTab__content--loading"><span class="fa fa-spinner fa-spin"></span>' +
                    i18n.___("Displaying", "ddui") +
                    "</div>"
                )
              );
            currentView.$el.attr("id", currentView.model.id);
            currentView.$el.attr("data-attrid", currentView.model.id);

            hasOneContent = currentView.model
              .get("content")
              .some(function vTabContentIsDisplayable(value) {
                return value.isDisplayable();
              });

            if (!hasOneContent) {
              currentView.$el.append(
                currentView.model.getOption("showEmptyContent")
              );
              currentView.$el.removeClass("dcpTab__content--loading");
              currentView.model.trigger("renderDone", {
                model: currentView.model,
                $el: currentView.$el
              });
              currentView.propageShowTab();
              resolve(currentView);
            } else {
              if (currentView.initializeContent === true) {
                currentView
                  .renderContent()
                  .then(function vTabContentRender_renderContent() {
                    resolve(currentView);
                  })
                  .catch(reject);
              } else {
                resolve(currentView);
              }
            }
          } catch (e) {
            reject(e);
          }
        }, this)
      );
    },

    renderContent: function vTabContentRenderContent(event) {
      var currentView = this;
      var pTabRenderPromise = new Promise(
        _.bind(function vTabContentRenderContent_Promise(resolve, reject) {
          var customRender,
            $content = currentView.$el,
            model = currentView.model,
            promisesFrame = [];
          if (currentView.initializing === false) {
            currentView.initializing = true;
            currentView.$el.empty();
            if (currentView.originalView !== true) {
              if (currentView.model.getOption("template")) {
                customRender = attributeTemplate.renderCustomView(
                  currentView.model
                );
                currentView.customView = customRender.$el;
                promisesFrame.push(customRender.promise);
              }
            }
            if (currentView.customView) {
              $content.append(currentView.customView);
            } else {
              currentView.model
                .get("content")
                .each(function vTabContentRenderContent(currentAttr) {
                  var view;
                  try {
                    if (!currentAttr.isDisplayable()) {
                      return;
                    }
                    if (currentAttr.get("type") === "frame") {
                      view = new ViewAttributeFrame({ model: currentAttr });
                      promisesFrame.push(view.render());
                      $content.append(view.$el);
                    } else {
                      //noinspection ExceptionCaughtLocallyJS
                      throw new Error(
                        "unkown type " +
                          currentAttr.get("type") +
                          " for id " +
                          currentAttr.id +
                          " for tab " +
                          model.id
                      );
                    }
                  } catch (e) {
                    if (window.dcp.logger) {
                      window.dcp.logger(e);
                    } else {
                      console.error(e);
                    }
                  }
                });
              attributeTemplate.insertDescription(this);
              if (currentView.model.getOption("responsiveColumns")) {
                currentView.responsiveColumns();
              }
            }

            Promise.all(promisesFrame)
              .then(function tabAllFramesRenderDone() {
                currentView.$el.removeClass("dcpTab__content--loading");
                currentView.model.trigger("renderDone", {
                  model: currentView.model,
                  $el: currentView.$el
                });

                resolve();
              })
              .catch(reject);
          } else {
            resolve();
          }
          currentView.model.getDocumentModel().trigger("redrawErrorMessages");
          currentView.model.get("content").propageEvent("resize");
        }, this)
      );

      pTabRenderPromise.then(function() {
        if (currentView.model.isRealSelected) {
          currentView.model.trigger(
            "attributeAfterTabSelect",
            event,
            currentView.model.id
          );
          currentView.model.isRealSelected = false;
        }
      });

      return pTabRenderPromise;
    },

    /**
     * Add responsive column classes according to responsiveColumns render option
     */
    responsiveColumns: function vTab_responsiveColumns() {
      var responseColumnsDefs = this.model.getOption("responsiveColumns") || [];
      var _this = this;
      var $fake = $("<div/>").css({
        position: "absolute",
        top: 0,
        overflow: "hidden"
      });
      var $fakeWidth = $("<div/>");
      var setResponsiveClasse = function vTab_setResponsiveClasses() {
        var fWidth = $(_this.$el).width();
        var matchesResponsive = 0;

        _.each(responseColumnsDefs, function vTab_setResponsiveClasses(
          responseColumnsInfo
        ) {
          if (
            fWidth >= responseColumnsInfo.minAbsWidth &&
            fWidth < responseColumnsInfo.maxAbsWidth
          ) {
            _this.$el.addClass("dcp-column--" + responseColumnsInfo.number);
            matchesResponsive = responseColumnsInfo.number;
            if (responseColumnsInfo.grow === true) {
              _this.$el.addClass("dcp-column--grow");
            } else {
              _this.$el.removeClass("dcp-column--grow");
            }
          } else {
            _this.$el.removeClass("dcp-column--" + responseColumnsInfo.number);
          }
        });

        if (matchesResponsive > 1) {
          _this.$el.addClass("dcp-column");
        } else {
          _this.$el.removeClass("dcp-column");
        }
        if (matchesResponsive !== _this.frameIsResized) {
          _this.frameIsResized = matchesResponsive;
          // Send resize to frame in case they have also responsive.
          _this.model.get("content").propageEvent("resize");
        }
      };

      $("body").append($fake.append($fakeWidth));

      // Compute absolute width
      _.each(responseColumnsDefs, function vTab_computeResponsiveWidth(
        responseColumnsInfo
      ) {
        if (!responseColumnsInfo.minWidth) {
          responseColumnsInfo.minAbsWidth = 0;
        } else {
          $fakeWidth.width(responseColumnsInfo.minWidth);
          responseColumnsInfo.minAbsWidth = $fakeWidth.width();
        }

        if (!responseColumnsInfo.maxWidth) {
          responseColumnsInfo.maxAbsWidth = Infinity;
        } else {
          $fakeWidth.width(responseColumnsInfo.maxWidth);
          responseColumnsInfo.maxAbsWidth = $fakeWidth.width();
        }
      });

      $fake.remove();
      $(window).on("resize.v" + this.model.cid, setResponsiveClasse);
      _.defer(setResponsiveClasse);
    },
    propageShowTab: function vTabContentPropageShowTab() {
      this.model.get("content").propageEvent("showTab");
    },

    updateLabel: function vTabContentUpdateLabel() {
      this.$el.find(".dcpFrame__label").text(this.model.get("label"));
    },

    hide: function vTabContentHide() {
      this.$el.hide();
    },

    show: function vTabContentShow() {
      this.$el.show();
    },

    _identifyView: function vAttribute_identifyView(event) {
      event.haveView = true;
      //Add the pointer to the current jquery element to a list passed by the event
      event.elements = event.elements.add(this.$el);
    },
    remove: function vFrame_Remove() {
      $(window).off(".v" + this.model.cid);

      return Backbone.View.prototype.remove.call(this);
    }
  });
});
