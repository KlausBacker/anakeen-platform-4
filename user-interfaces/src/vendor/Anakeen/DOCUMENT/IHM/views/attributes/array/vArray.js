import $ from "jquery";
import _ from "underscore";
import Backbone from "backbone";
import ViewColumn from "./vColumn";
import attributeTemplate from "../../document/attributeTemplate";
import * as EventPromiseUtils from "../../../widgets/globalController/utils/EventUtils";
import "../../../widgets/attributes/array/wArray";

export default Backbone.View.extend({
  className: "row dcpArray",
  displayLabel: true,
  customView: false,
  customRowView: false,
  absoluteTranspositionWith: null,
  events: {
    dcparraylineadded: "addLine",
    dcparraylineremoved: "removeLine",
    dcparraylinemoved: "moveLine",
    dcparrayexternallinkselected: "externalLinkSelected",
    "dcpattributechange .dcpArray__content__cell": "updateValue"
  },

  columnViews: {},

  initialize: function vArray_initialize(options) {
    if (options.displayLabel === false || this.model.getOption("labelPosition") === "none") {
      this.displayLabel = false;
    }
    this.listenTo(this.model, "change:label", this.updateLabel);
    this.listenTo(this.model, "destroy", this.remove);
    this.listenTo(this.model, "cleanView", this.remove);
    this.listenTo(this.model, "change:errorMessage", this.setArrayError);
    this.listenTo(this.model, "errorMessage", this.setError);
    this.listenTo(this.model, "hide", this.hide);
    this.listenTo(this.model, "show", this.show);
    this.listenTo(this.model, "removeWidgetLine", this.removeWidgetLine);
    this.listenTo(this.model, "addWidgetLine", this.addWidgetLine);
    this.listenTo(this.model, "haveView", this._identifyView);
    this.listenTo(this.model, "resize", this.setResponsiveClasse);
    this.options = options;
  },

  render: function vArray_render() {
    var currentView = this;
    return new Promise(
      _.bind(function vArray_renderPromise(resolve, reject) {
        try {
          var customRender,
            data = currentView.model.toData(null, true),
            event = { prevent: false },
            promisesColumn = [];

          //Trigger the beforeRender event, and cancel display if asked
          currentView.model.trigger("beforeRender", event, {
            model: currentView.model,
            $el: currentView.$el,
            options: { customTemplate: currentView.options.originalView !== true }
          });
          if (event.prevent) {
            resolve(currentView);
            return currentView;
          }
          const onDone = () => {
            if (currentView.customView) {
              data.customTemplate = currentView.customView;
              data.customLineCallback = function vArray_callCustomLine(index) {
                return attributeTemplate.customArrayRowView(index, currentView.model, currentView);
              };
            }

            try {
              promisesColumn.push(
                new Promise(
                  _.bind(function onArrayReady(resolve, reject) {
                    try {
                      currentView.$el.dcpArray(data).one("dcparraywidgetready", resolve);
                      attributeTemplate.insertDescription(currentView);
                    } catch (e) {
                      reject(e);
                    }
                  }, this)
                )
              );
            } catch (e) {
              if (window.dcp.logger) {
                window.dcp.logger(e);
              } else {
                console.error(e);
              }
            }

            Promise.all(promisesColumn).then(function renderDone() {
              currentView.$el.attr("data-attrid", currentView.model.id);
              currentView.model.trigger("renderDone", {
                model: currentView.model,
                $el: currentView.$el,
                options: {
                  customTemplate: !!currentView.customView
                }
              });
              if (
                currentView.$el.find(
                  '.dcpCustomTemplate[data-attrid="' +
                    currentView.model.id +
                    '"] .dcpCustomTemplate--content[data-attrid="' +
                    currentView.model.id +
                    '"]'
                ).length > 0
              ) {
                // it's a fake array view because template declare itself inside
                currentView.stopListening(currentView.model, "addWidgetLine");
                currentView.stopListening(currentView.model, "removeWidgetLine");
                currentView.undelegateEvents();
              }
              resolve(currentView);
            });
          };
          EventPromiseUtils.getBeforeEventPromise(event, () => {
            currentView.$el.addClass("dcpAttribute--visibility--" + currentView.model.get("visibility"));
            if (currentView.options.originalView !== true) {
              if (currentView.model.getOption("template")) {
                customRender = attributeTemplate.renderCustomView(currentView.model);
                currentView.customView = customRender.$el;
                customRender.promise.then(resolve);
                if (currentView.model.getOption("template").match("dcpArray__table")) {
                  // Two case of custom : custom line or global custom array
                  currentView.customRowView = true;
                }
              }
            }

            //Extract only the displayable lines
            data.content = _.filter(data.content, function vArray_filterCurrentElement(currentContent) {
              return currentContent.isDisplayable;
            });
            data.nbLines = currentView.getNbLines();
            currentView.padValues(data.nbLines);
            data.renderOptions = currentView.model.getOptions();
            data.templates = {};
            data.displayLabel = currentView.displayLabel;
            if (currentView.model.getTemplates().attribute[currentView.model.get("type")]) {
              data.templates = currentView.model.getTemplates().attribute[currentView.model.get("type")];
            }
            if (data.nbLines === 0 && data.mode === "read") {
              data.showEmpty = currentView.model.getOption("showEmptyContent");
            } else {
              if (!currentView.customView || currentView.customRowView) {
                currentView.columnViews = [];
                currentView.model.get("content").each(function vArray_analyzeContent(currentAttr) {
                  if (!currentAttr.isDisplayable()) {
                    return;
                  }
                  try {
                    if (currentAttr.getOption("attributeLabel")) {
                      data.content = _.map(data.content, function vArray_changeLabelCurrentElement(currentContent) {
                        if (currentContent.id === currentAttr.id) {
                          currentContent.label = currentAttr.getOption("attributeLabel");
                        }
                        return currentContent;
                      });
                    }
                    if (currentAttr.get("isValueAttribute")) {
                      currentView.columnViews[currentAttr.id] = new ViewColumn({
                        el: currentView.el,
                        els: function vArray_findScope() {
                          return currentView.$el.find(
                            '.dcpArray__cell[data-attrid="' +
                              currentAttr.id +
                              '"],' +
                              '.dcpCustomTemplate--row[data-attrid="' +
                              currentAttr.id +
                              '"]'
                          );
                        },
                        originalView: true,
                        model: currentAttr,
                        parentElement: currentView.$el
                      });
                      promisesColumn.push(currentView.columnViews[currentAttr.id].render());
                    }
                  } catch (e) {
                    if (window.dcp.logger) {
                      window.dcp.logger(e);
                    } else {
                      console.error(e);
                    }
                  }
                });
              }
            }
            $(window).on("resize.v" + this.model.cid, _.bind(this.setResponsiveClasse, this));
            _.defer(_.bind(this.setResponsiveClasse, this));
          })
            .then(onDone)
            .catch(onDone);
        } catch (e) {
          reject(e);
        }
      }, this)
    );
  },

  getNbLines: function vArraygetNbLines() {
    var nbLigne = this.nbLines || 0;
    this.model.get("content").each(function vArray_getCurrentLine(currentAttr) {
      if (currentAttr.get("attributeValue") && nbLigne < _.size(currentAttr.get("attributeValue"))) {
        nbLigne = _.size(currentAttr.get("attributeValue"));
      }
    });
    return nbLigne;
  },

  padValues: function vArrayPadValues(lineNumber) {
    if (lineNumber > 0) {
      this.model.get("content").each(function vArray_padCurrentLine(currentAttr) {
        var currentValue = currentAttr.get("attributeValue");
        if (currentValue !== null && currentValue !== undefined && _.size(currentValue) < lineNumber) {
          for (var idx = currentValue.length; idx < lineNumber; idx++) {
            currentValue.push({ value: null, displayValue: "" });
          }
        }
      });
    }
  },

  updateLabel: function vArrayupdateLabel() {
    this.$el.find(".dcpArray__label").text(this.model.get("label"));
  },

  updateValue: function vArray_UpdateValue(event, options) {
    var attributeModel = this.model.get("content").get(options.id);
    if (!attributeModel) {
      throw new Error("Unknown attribute " + options.id);
    }
    if (options.index >= 0) {
      var currentValues = attributeModel.get("attributeValue");
      var currentValue = currentValues[options.index];

      if (!_.isEqual(currentValue, options.value)) {
        // Update model only if a change is detected
        if (
          !currentValue.value ||
          !options.value ||
          !options.value.value ||
          currentValue.value !== options.value.value
        ) {
          attributeModel.setValue(options.value, options.index);
        }
      }
    } else {
      attributeModel.setValue(options.value, options.index);
    }
  },

  setResponsiveClasse: function vArray_setResponsiveClasse() {
    const arrayWidth = $(this.$el).width();
    const transposeWidth = this.model.getOption("transpositionWidth");

    if (this.absoluteTranspositionWith === null) {
      if (parseInt(transposeWidth) > 0) {
        const $fake = $("<div/>").css({
          position: "absolute",
          top: 0,
          overflow: "hidden"
        });
        $("body").append($fake);
        $fake.width(transposeWidth);
        this.absoluteTranspositionWith = $fake.width();
        $fake.remove();
      } else {
        this.absoluteTranspositionWith = 0;
      }
    }

    if (this.absoluteTranspositionWith > 0) {
      if (arrayWidth < this.absoluteTranspositionWith) {
        this.$el.find("table.dcpArray__table").addClass("transpose");
      } else {
        this.$el.find("table.dcpArray__table").removeClass("transpose");
      }
    }
  },
  refresh: function vArray_Refresh() {
    this.nbLines = this.$el.dcpArray("option", "nbLines");
    this.$el.dcpArray("destroy");
    this.render();
  },

  removeLine: function vArray_removeLine(event, options) {
    this.model.get("content").each(function vArray_removeLine(currentContent) {
      currentContent.removeIndexValue(options.line);
    });
    this.model.trigger("array", "removeLine", this.model, options.line);
  },

  removeWidgetLine: function vArray_RemoveWidgetLine(options) {
    this.$el.dcpArray("removeLine", options.index, { silent: true });
  },

  addWidgetLine: function vArray_addWidgetLine(options) {
    this.$el.dcpArray("addLine", options.index);
  },

  addLine: function vArray_AddLine(event, options) {
    var currentArrayView = this,
      customView = null;
    var allPromiseAttributes = [];
    this.model.get("content").each(function vArray_addLineGetContent(currentContent) {
      var currentViewColumn;
      if (options.needAddValue || options.copyValue) {
        currentContent.createIndexedValue(options.line, options.copyValue);
      }
      currentViewColumn = currentArrayView.columnViews[currentContent.id];
      if (currentViewColumn) {
        customView = null;
        if (currentContent.getOption("template")) {
          customView = attributeTemplate.renderCustomView(
            currentContent,
            function vArray_customViewInit() {
              var $this = $(this),
                currentWidgetOption = currentViewColumn.getData(options.line);
              //Check if the asked attribute is the current attribute
              if ($this.data("attrid") !== currentContent.id) {
                throw Error(
                  "An attribute template in an array cannot asked" +
                    " for another attribute than the current (" +
                    $this.data("attrid") +
                    " asked instead of " +
                    currentContent.id +
                    ")"
                );
              }

              currentWidgetOption.viewCid = _.uniqueId(currentWidgetOption.viewCid);
              currentViewColumn.widgetInit($this, currentWidgetOption);
              currentViewColumn.moveValueIndex({});
              currentViewColumn.model.trigger("renderDone", {
                model: currentViewColumn.model,
                $el: $this,
                options: { customTemplate: $this.data("originalView") === false },
                index: options.line
              });
            },
            { index: options.line }
          );
          allPromiseAttributes.push(customView.promise);
        }
        if (customView && customView.$el) {
          allPromiseAttributes.push(currentViewColumn.addNewWidget(options.line, customView.$el));
        } else {
          allPromiseAttributes.push(currentViewColumn.addNewWidget(options.line));
        }
      }
    });
    Promise.all(allPromiseAttributes)
      .then(
        _.bind(function addlinePromiseAllDone() {
          this.model.get("content").each(currentAttr => {
            currentAttr.trigger("renderHtmlText");
          });
          this.model.trigger("array", "addLine", this.model, options.line);
          options.resolve();
        }, this)
      )
      .catch(options.reject);
  },

  moveLine: function vArray_moveLine(event, options) {
    this.model.get("content").each(function vArray_getMoveLineContent(currentContent) {
      currentContent.moveIndexValue(options.fromLine, options.toLine);
    });
    this.model.trigger("array", "moveLine", this.model, options);
  },
  getAttributeModel: function vArray_getAttributeModel(attributeId) {
    var docModel = this.model.getDocumentModel();
    return docModel.get("attributes").get(attributeId);
  },

  setError: function vArray_setError(event, message) {
    var parentId = this.model.get("parent");

    if (message) {
      this.$el.find(".dcpArray__label").addClass("has-error");
    } else {
      this.$el.find(".dcpArray__label").removeClass("has-error");
    }

    if (parentId) {
      var parentModel = this.getAttributeModel(parentId);
      if (parentModel) {
        parentModel.trigger("errorMessage", event, message);
      }
    }
  },
  setArrayError: function vArray_setArrayError(event, message) {
    this.setError(event, message);
    // global error on array itself
    this.$el.dcpArray("setError", message);
  },

  hide: function vArray_hide() {
    this.$el.hide();
  },

  show: function vArray_show() {
    this.$el.show();
  },
  /**
   * Destroy the associated widget and suppress event listener before remov the dom
   *
   * @returns {*}
   */
  remove: function vArray_remove() {
    $(window).off(".v" + this.model.cid);

    return Backbone.View.prototype.remove.call(this);
  },
  _identifyView: function vArray_identifyView(event) {
    event.haveView = true;
    //Add the pointer to the current jquery element to a list passed by the event
    event.elements = event.elements.add(this.$el);
  },
  externalLinkSelected: function vArrayExternalLinkSelected(event, options) {
    var documentModel = this.model.getDocumentModel();
    options.attrid = this.model.id;
    this.model.trigger("internalLinkSelected", event, options);
    return EventPromiseUtils.getBeforeEventPromise(
      event,
      () => {
        documentModel.trigger("actionAttributeLink", event, options);
        return this;
      },
      () => {
        return this;
      }
    );
  }
});
