/*global define, console*/
define([
  "jquery",
  "underscore",
  "backbone",
  "mustache",
  "dcpDocument/views/document/attributeTemplate",
  "dcpDocument/widgets/attributes/label/wLabel",
  "dcpDocument/widgets/attributes/text/wText",
  "dcpDocument/widgets/attributes/int/wInt",
  "dcpDocument/widgets/attributes/longtext/wLongtext",
  "dcpDocument/widgets/attributes/htmltext/wHtmltext",
  "dcpDocument/widgets/attributes/timestamp/wTimestamp",
  "dcpDocument/widgets/attributes/time/wTime",
  "dcpDocument/widgets/attributes/image/wImage",
  "dcpDocument/widgets/attributes/money/wMoney",
  "dcpDocument/widgets/attributes/enum/wEnum",
  "dcpDocument/widgets/attributes/color/wColor",
  "dcpDocument/widgets/attributes/password/wPassword",
  "dcpDocument/widgets/attributes/file/wFile",
  "dcpDocument/widgets/attributes/double/wDouble",
  "dcpDocument/widgets/attributes/docid/wDocid"
], function vAttribute($, _, Backbone, Mustache, attributeTemplate) {
  "use strict";

  return Backbone.View.extend({
    className: "row dcpAttribute form-group",
    customView: false,
    displayLabel: true,
    //Don't use standard event to launch the event only when there is no template
    //********************************************************************************************
    // If you add an event here, you probably want to add it in vColumn.js and test it in an array
    //********************************************************************************************
    attributeEvents: {
      "dcpattributechange .dcpAttribute__content,[data-dcpattribute_content]":
        "updateValue",
      "dcpattributedelete .dcpAttribute__content": "deleteValue",
      "dcpattributechangeattrmenuvisibility .dcpAttribute__content":
        "changeMenuVisibility",
      "dcpattributechangeattrsvalue .dcpAttribute__content":
        "changeAttributesValue",
      "dcpattributefetchdocument .dcpAttribute__content": "loadDocument",
      "dcpattributeexternallinkselected .dcpAttribute__content":
        "externalLinkSelected",
      dcplabelexternallinkselected: "externalLinkSelected",
      "dcpattributedownloadfile  .dcpAttribute__content": "downloadFileSelect",
      "dcpattributeuploadfile  .dcpAttribute__content": "uploadFileSelect",
      "dcpattributeuploadfiledone  .dcpAttribute__content": "uploadFileDone",
      "dcpattributeanchorclick .dcpAttribute__content": "anchorClick",
      "dcpattributewidgetready .dcpAttribute__content": "setWidgetReady"
    },

    initialize: function vAttributeInitialize(options) {
      var events;
      this.listenTo(this.model, "change:label", this.refreshLabel);
      this.listenTo(this.model, "change:attributeValue", this.refreshValue);
      this.listenTo(this.model, "change:errorMessage", this.refreshError);
      this.listenTo(this.model, "moved", this.moveValueIndex);
      this.listenTo(this.model, "destroy", this.remove);
      this.listenTo(this.model, "showTab", this.afterShow);
      this.listenTo(this.model, "hide", this.hide);
      this.listenTo(this.model, "show", this.show);
      this.listenTo(this.model, "haveView", this._identifyView);
      this.listenTo(this.model, "closeWidget", this._closeWidget);
      this.templateWrapper = this.model.getTemplates().attribute.simpleWrapper;

      options = options || {};

      if (
        options.displayLabel === false ||
        this.model.getOption("labelPosition") === "none"
      ) {
        this.displayLabel = false;
      }

      if (options.originalView === undefined) {
        options.originalView = _.isEmpty(this.model.getOption("template"));
      }

      //Attribute without custom template so we bind event
      if (options.originalView === true) {
        events = this.attributeEvents;
        //For vColumn events
        if (_.isFunction(events)) {
          events = events.apply(this);
        }
        this.delegateEvents(events);
      }

      this.options = options;
    },

    /**
     * The Data are the source of data shared with widget and templates
     *
     * @param index
     * @returns {*}
     */
    getData: function vAttributeGetData(index) {
      var data;

      //Made to JSON for all the values, or to data for value indexed (in cas of multiple)
      data = this.model.toData(index, true);
      data.viewCid = this.cid + "-" + this.model.id;
      data.labels.deleteAttributeNames = this.getDeleteLabels();
      // autoComplete detected
      data.autocompleteRequest = _.bind(this.autocompleteRequestRead, this);

      return data;
    },

    render: function vAttributeRender() {
      var currentView = this;
      var renderPromise = new Promise(
        _.bind(function vAttributeRender_Promise(resolve, reject) {
          var data,
            event = { prevent: false },
            customRender;

          currentView.model.trigger("beforeRender", event, {
            model: currentView.model,
            $el: currentView.$el
          });
          if (event.prevent) {
            resolve();
            return currentView;
          }

          //We fetch data after beforeRender, if some data is modified by beforeRender we get it
          data = currentView.getData();

          currentView.$el.addClass(
            "dcpAttribute--type--" + currentView.model.get("type")
          );
          currentView.$el.addClass(
            "dcpAttribute--visibility--" + currentView.model.get("visibility")
          );
          currentView.$el.attr("data-attrid", currentView.model.get("id"));
          if (currentView.model.get("needed")) {
            currentView.$el.addClass("dcpAttribute--needed");
          }

          currentView.$el.append(
            $(Mustache.render(currentView.templateWrapper || "", data))
          );

          attributeTemplate.insertDescription(currentView);

          //analyze the display label and add display class
          if (currentView.displayLabel === false) {
            currentView.$el.find(".dcpAttribute__label").remove();
            // set to 100% width
            currentView.$el
              .find(".dcpAttribute__right")
              .addClass("dcpAttribute__right--full");
          } else {
            if (currentView.model.getOption("labelPosition") === "left") {
              currentView.$el.addClass("dcpAttribute__labelPosition--left");
              currentView.$el
                .find(".dcpAttribute__right")
                .not(".dcpAttribute__description")
                .addClass("dcpAttribute__labelPosition--left");
              currentView.$el
                .find(".dcpAttribute__left")
                .not(".dcpAttribute__description")
                .addClass("dcpAttribute__labelPosition--left");
            }
            if (currentView.model.getOption("labelPosition") === "up") {
              currentView.$el.addClass("dcpAttribute__labelPosition--up");
              currentView.$el
                .find(".dcpAttribute__right")
                .not(".dcpAttribute__description")
                .addClass("dcpAttribute__labelPosition--up");
              currentView.$el
                .find(".dcpAttribute__left")
                .not(".dcpAttribute__description")
                .addClass("dcpAttribute__labelPosition--up");
            }
            if (currentView.model.getOption("labelPosition") === "auto") {
              currentView.$el.addClass("dcpAttribute__labelPosition--auto");
              currentView.$el
                .find(".dcpAttribute__right")
                .addClass("dcpAttribute__labelPosition--auto");
              currentView.$el
                .find(".dcpAttribute__left")
                .addClass("dcpAttribute__labelPosition--auto");
            }
            currentView.$el.find(".dcpAttribute__label").dcpLabel(data);
          }

          //If there is a template render it
          if (
            currentView.options.originalView !== true &&
            currentView.model.getOption("template")
          ) {
            customRender = attributeTemplate.renderCustomView(
              currentView.model
            );
            currentView.customView = customRender.$el;
            currentView.$el
              .find(".dcpAttribute__content")
              .append(currentView.customView);
            customRender.promise.then(resolve);
            customRender.promise["catch"](reject);
          } else {
            //there is not template render (default)
            currentView.$el.one(
              "dcpattributewidgetready .dcpAttribute__content",
              function vattributeRender_widgetready() {
                resolve();
              }
            );
            currentView.currentDcpWidget = currentView.widgetInit(
              currentView.$el.find(".dcpAttribute__content"),
              data
            );
          }

          currentView.renderDone = true;
          if (currentView.customView) {
            currentView.widgetReady = true;
          }

          currentView.triggerRenderDone();
          return currentView;
        }, this)
      );
      return renderPromise;
    },

    refreshLabel: function vAttributeRefreshLabel() {
      var label = this.model.get("label"),
        labelDom = this.getDOMElements().find(".dcpAttribute__label");
      if (this.model.getOption("attributeLabel")) {
        label = this.model.getOption("attributeLabel");
      }
      if (labelDom.data("dcpDcpLabel")) {
        this.getDOMElements()
          .find(".dcpAttribute__label")
          .dcpLabel("setLabel", label);
      }
    },

    /**
     * Autorefresh value when model change
     */
    refreshValue: function vAttributeRefreshValue(model, values, options) {
      var scope = this,
        allWrapper,
        arrayWrapper;
      if (options.notUpdateArray) {
        return this;
      }

      allWrapper = this.getDOMElements();

      if (this.model.isInArray()) {
        // adjust line number to column length
        arrayWrapper = this.$el;
        arrayWrapper.dcpArray("setLines", values.length, options).then(
          _.bind(function vAttributeDrawValue() {
            values = _.toArray(values);

            if (_.isEqual(values, scope.model.getValue())) {
              _.each(values, function analyzeValues(currentValue, index) {
                if (_.isUndefined(currentValue)) {
                  return;
                }
                var cssIndex =
                  '.dcpAttribute__content--widget[data-attrid="' +
                  model.id +
                  '"]';
                $(allWrapper[index])
                  .find(cssIndex)
                  .addBack(cssIndex)
                  .each(function vAttributeRefreshOneValue(index, element) {
                    scope.widgetApply($(element), "setValue", currentValue);
                  });
              });
            }
          }, this)
        );
      } else {
        this.widgetApply(
          allWrapper.find(
            '.dcpAttribute__content--widget[data-attrid="' + model.id + '"]'
          ),
          "setValue",
          values
        );
      }
    },

    /**
     * Display error message around the widget if needed
     * @param event
     */
    refreshError: function vAttributeRefreshError(event) {
      this.$el
        .find(".dcpAttribute__label")
        .dcpLabel("setError", this.model.get("errorMessage"));
      // andSelf method was removed from jQuery 3.0.0+ use addBack instead
      var jqueryVersion = +$().jquery.split(".")[0];
      if (jqueryVersion >= 3) {
        this.widgetApply(
          this.getDOMElements()
            .find(".dcpAttribute__content--widget")
            .addBack()
            .filter(".dcpAttribute__content--widget"),
          "setError",
          this.model.get("errorMessage")
        );
      } else {
        this.widgetApply(
          this.getDOMElements()
            .find(".dcpAttribute__content--widget")
            .andSelf()
            .filter(".dcpAttribute__content--widget"),
          "setError",
          this.model.get("errorMessage")
        );
      }
    },

    /**
     * Modify several attribute
     * @param event event object
     * @param index the value rank in case of multiple
     * @param options object {dataItem :, valueIndex :}
     */
    changeAttributesValue: function vAttributeChangeAttributesValue(
      event,
      options,
      index
    ) {
      var externalEvent = { prevent: false },
        currentView = this,
        dataItem = options.dataItem,
        valueIndex = options.valueIndex,
        currentValue;
      this.model.trigger(
        "helperSelect",
        externalEvent,
        this.model.id,
        dataItem,
        index
      );
      if (externalEvent.prevent) {
        return this;
      }
      _.each(dataItem.values, function vAttributeChangeAttributeValue(
        attributeValue,
        attributeId
      ) {
        if (typeof attributeValue === "object") {
          if (attributeValue.value === null) {
            //Value not completed by helper so don't use it
            return;
          }

          var attrModel = currentView.model
            .getDocumentModel()
            .get("attributes")
            .get(attributeId);
          if (attrModel) {
            if (attrModel.hasMultipleOption()) {
              currentValue = attrModel.getValue();
              if (valueIndex >= 0) {
                currentValue = currentValue[valueIndex];
              }
              // No add same value twice
              if (
                !_.some(currentValue, function vAttributeNoDouble(itemValue) {
                  return itemValue.value === attributeValue.value;
                })
              ) {
                attrModel.addValue(
                  {
                    value: attributeValue.value,
                    displayValue: attributeValue.displayValue
                  },
                  valueIndex
                );
              }
            } else {
              attrModel.setValue(
                {
                  value: attributeValue.value,
                  displayValue: attributeValue.displayValue
                },
                valueIndex
              );
            }
          } else {
            console.error("Unable to find " + attributeId);
          }
        }
      });
    },

    /**
     * Modify view : triggered by wDocid
     * @param event
     * @param options
     * @returns {*}
     */
    loadDocument: function changeAttributesValueLoadDocument(event, options) {
      var index = options.index,
        initid = null,
        attributeValue = this.model.get("attributeValue"),
        documentModel = this.model.getDocumentModel(),
        revision = -1;
      if (_.isUndefined(index)) {
        initid = attributeValue.value;
        revision = attributeValue.revision;
      } else {
        initid = attributeValue[index].value;
        revision = attributeValue[index].revision;
      }

      this.model.trigger("internalLinkSelected", event, {
        eventId: "document.load",
        target: event.target,
        attrid: this.model.id,
        options: [initid, "!defaultConsultation"],
        index: options.index
      });

      if (event.prevent) {
        return this;
      }

      documentModel.trigger("loadDocument", {
        initid: initid,
        viewId: "!defaultConsultation",
        revision: revision
      });
    },

    /**
     * Create dialog window to create and insert document
     */
    displayFormDocument: function vAttributedisplayFormDocument(
      event,
      buttonConfig,
      index
    ) {
      var attrid = this.model.id;
      if (buttonConfig.createLabel) {
        var documentModel = this.model.getDocumentModel();

        require.ensure(
          ["dcpDocument/widgets/attributes/docid/wCreateDocument"],
          function vDocumentCreateDocument() {
            require("dcpDocument/widgets/attributes/docid/wCreateDocument");
            var $bdw = $('<div class="dcpDocid-create-window"/>');
            var $dcp = $bdw
              .dcpCreateDocument(
                _.extend(buttonConfig, {
                  originDocumentModel: documentModel,
                  attributeId: attrid,
                  index: index,
                  listener: function vDocumentCreateListener(
                    event,
                    triggerId,
                    options
                  ) {
                    options.dialogDocument = this;
                    options.triggerId = triggerId;
                    documentModel.trigger(
                      "createDialogListener",
                      event,
                      attrid,
                      options
                    );
                  }
                })
              )
              .data("dcpCreateDocument");
            $dcp.open();
          },
          "wCreateDocument"
        );
      }
    },

    externalLinkSelected: function vAttributeExternalLinkSelected(
      event,
      options
    ) {
      var documentModel = this.model.getDocumentModel();
      options.attrid = this.model.id;
      this.model.trigger("internalLinkSelected", event, options);
      if (event.prevent) {
        return this;
      }
      if (options.eventId === "attribute.createDocumentRelation") {
        this.displayFormDocument(event, options.buttonConfig, options.index);
      } else {
        documentModel.trigger("actionAttributeLink", event, options);
      }
    },
    downloadFileSelect: function vAttributedownloadFileSelect(
      widgetEvent,
      options
    ) {
      this.model.trigger("downloadFile", widgetEvent, this.model.id, options);
    },
    uploadFileSelect: function vAttributeuploadFileSelect(
      widgetEvent,
      options
    ) {
      this.model.trigger("uploadFile", widgetEvent, this.model.id, options);
    },
    uploadFileDone: function vAttributeuploadFileSEnd(widgetEvent, options) {
      var event = { prevent: false };

      this.model.trigger("uploadFileDone", widgetEvent, this.model.id, options);
    },

    anchorClick: function vAttributeAnchorClick(widgetEvent, options) {
      this.model.trigger("anchorClick", widgetEvent, this.model.id, options);
    },

    /**
     * Delete value,
     * If has help, clear also target attributes
     * @param event
     * @param data index info {index:"the index}
     */
    deleteValue: function changeAttributesValueDeleteValue(event, data) {
      if (data.id === this.model.id) {
        var attrToClear = this.model.get("helpOutputs"),
          docModel = this.model.getDocumentModel();
        if (!attrToClear || typeof attrToClear === "undefined") {
          attrToClear = [this.model.id];
        } else {
          attrToClear = _.toArray(attrToClear);
        }
        _.each(attrToClear, function vAttributeCleanAssociatedElement(aid) {
          var attr = docModel.get("attributes").get(aid);
          if (attr) {
            if (attr.hasMultipleOption()) {
              attr.setValue([], data.index);
            } else {
              attr.setValue({ value: null, displayValue: "" }, data.index);
            }
          }
        });
      }
    },

    /**
     * Return another attribute model
     *
     * @param attributeId
     * @returns {*}
     */
    getAttributeModel: function vAttributeGetAttributeModel(attributeId) {
      var docModel = this.model.getDocumentModel();
      return docModel.get("attributes").get(attributeId);
    },

    /**
     * Used for render attribute
     *
     * @returns {Array}
     */
    getDeleteLabels: function vAttributeGetDeleteLabels() {
      var attrToClear = this.model.get("helpOutputs"),
        scope = this,
        attrLabels;
      if (!attrToClear || typeof attrToClear === "undefined") {
        attrToClear = [this.model.id];
      } else {
        attrToClear = _.toArray(attrToClear);
      }
      attrLabels = _.map(attrToClear, function vAttributeGetAssociatedLabel(
        aid
      ) {
        var attr = scope.getAttributeModel(aid);
        if (attr) {
          return attr.attributes.label;
        }
        return "";
      });
      return attrLabels;
    },

    /**
     * Propagate move value event to widgets
     * @param eventData
     */
    moveValueIndex: function vAttributeMoveValueIndex(eventData) {
      this.getDOMElements().trigger("postMoved", eventData);
    },

    /**
     * method use for transport multiselect widget
     * @param index the row index of autocomplete when it is in array
     * @param options
     */
    autocompleteRequestRead: function vAttributeAutocompleteRequestRead(
      options,
      index
    ) {
      var currentView = this,
        documentModel = this.model.getDocumentModel(),
        success = options.success,
        externalOptions = {
          setResult: function vAttributeAutoCompleteSet(content) {
            _.each(content, function(item) {
              if (item.message) {
                item.message.contentText = item.message.contentText || "";
                item.message.contentHtml = item.message.contentHtml || "";
                item.message.type = item.message.type || "message";
              } else if (item.error) {
                item.message = {
                  contentHtml: "",
                  contentText: item.error,
                  type: "error"
                };
              }
              item.title = item.title || "";
            });
            success(content);
          },
          data: options.data
        },
        autocompleteUrl,
        event = { prevent: false };
      //Add helperResonse event (can be used to reprocess the content of the request)
      success = _.wrap(success, function vAttributeAutoCompleteSuccess(
        success,
        content
      ) {
        var options = {},
          event = { prevent: false };
        options.data = content;
        currentView.model.trigger(
          "helperResponse",
          event,
          currentView.model.id,
          options,
          index
        );
        if (event.prevent) {
          return success([]);
        }
        success(content);
      });

      //Add helperSearch event (can prevent default ajax request)
      options.data.attributes = documentModel.getValues();
      this.model.trigger(
        "helperSearch",
        event,
        this.model.id,
        externalOptions,
        index
      );
      if (event.prevent) {
        return this;
      }
      autocompleteUrl =
        "api/v2/documents/" +
        (documentModel.id || "0") +
        "/autocomplete/" +
        this.model.id;

      options.data.fromid = documentModel.get("properties").get("family").id;

      $.ajax({
        type: "POST",
        url: autocompleteUrl,
        data: options.data,

        dataType: "json" // "jsonp" is required for cross-domain requests; use "json" for same-domain requestsons.error(result);
      })
        .pipe(
          function vAttributeAutocompletehandleSuccessRequest(response) {
            if (response.success) {
              return response;
            } else {
              return $.Deferred().reject(response);
            }
          },
          function vAttributeAutocompletehandleErrorRequest(response) {
            if (response.status === 0) {
              return {
                success: false,
                error: "Your navigator seems offline, try later"
              };
            }
            if (
              response.responseJSON &&
              response.responseJSON.exceptionMessage
            ) {
              return {
                success: false,
                error: response.responseJSON.exceptionMessage
              };
            }
            return {
              success: false,
              error:
                "Unexpected error: " +
                response.status +
                " " +
                response.statusText
            };
          }
        )
        .then(
          function vAttributeAutocompleteSuccessResult(result) {
            // notify the data source that the request succeeded
            _.each(result.messages, function(message) {
              message.contentText = message.contentText || "";
              message.contentHtml = message.contentHtml || "";
              result.data.unshift({
                message: message,
                title: ""
              });
            });
            success(result.data);
          },
          function vAttributeAutocompleteErrorResult(result) {
            // notify the data source that the request failed
            if (_.isArray(result.error)) {
              result.error = result.error.join(" ");
            }
            //use the success callback because http error are handling by the pipe
            success([
              {
                title: "",
                message: {
                  type: "error",
                  contentHtml: "",
                  contentText: result.error
                }
              }
            ]);
          }
        );
    },

    /**
     * Modify visibility access of an item menu
     * @param event event object
     * @param data menu config {id: menuId, visibility: "disabled", "visible", "hidden"}
     */
    changeMenuVisibility: function vAttributeChangeMenuVisibility(event, data) {
      this.model.trigger("changeMenuVisibility", event, data);
    },

    getDOMElements: function vAttributeGetDOMElements() {
      if (this.options && this.options.els) {
        return this.options.els();
      } else {
        return this.$el;
      }
    },

    afterShow: function vAttributeAfterShow(/*event, data*/) {
      // propagate event to widgets
      this.getDOMElements().trigger("show");
    },
    /**
     *
     * @param event
     * @param data
     */
    updateValue: function vAttributeUpdateValue(event, data) {
      this.model.setValue(data.value, data.index);
    },

    widgetInit: function vAttributeWidgetInit($element, data) {
      $element.addClass("dcpAttribute__content--widget");
      return this.getWidgetClass($element).call($element, data);
    },

    widgetApply: function vAttributeWidgetApply($element, method, argument) {
      try {
        if (_.isString(method) && $element && this.getWidgetClass($element)) {
          this.getWidgetClass($element).call($element, method, argument);
        }
      } catch (e) {
        if (window.dcp.logger) {
          window.dcp.logger(e);
        } else {
          console.error(e);
        }
      }
      return this;
    },

    getWidgetClass: function vAttributeGetWidgetClass($element) {
      $element = $element || this.$el;
      if (!$element.data("currentWidgetClass")) {
        $element.data(
          "currentWidgetClass",
          this.getTypedWidgetClass(this.model.get("type"))
        );
      }
      return $element.data("currentWidgetClass");
    },

    getTypedWidgetClass: function vAttributeGetTypedWidgetClass(type) {
      var error = "",
        customWidgetClass = this.model.getOption(
          "customWidgetAttributeFunction"
        );
      if (customWidgetClass) {
        if (_.isFunction($.fn[customWidgetClass])) {
          return $.fn[customWidgetClass];
        }
        error =
          "Custom Widget Function : $.fn." +
          customWidgetClass +
          " is not a function. Attribute : " +
          this.model.id;
        console.error(error);
        throw new Error(error);
      }
      switch (type) {
        case "text":
          return $.fn.dcpText;
        case "int":
          return $.fn.dcpInt;
        case "double":
          return $.fn.dcpDouble;
        case "money":
          return $.fn.dcpMoney;
        case "longtext":
          return $.fn.dcpLongtext;
        case "htmltext":
          return $.fn.dcpHtmltext;
        case "date":
          return $.fn.dcpDate;
        case "timestamp":
          return $.fn.dcpTimestamp;
        case "time":
          return $.fn.dcpTime;
        case "image":
          return $.fn.dcpImage;
        case "color":
          return $.fn.dcpColor;
        case "file":
          return $.fn.dcpFile;
        case "enum":
          return $.fn.dcpEnum;
        case "password":
          return $.fn.dcpPassword;
        case "thesaurus":
        case "account":
        case "docid":
          return $.fn.dcpDocid;
        default:
          return $.fn.dcpText;
      }
    },

    setWidgetReady: function Vattribute_setWidgetReady() {
      this.widgetReady = true;
      this.triggerRenderDone();
    },

    triggerRenderDone: function vAttribute_triggerRenderDone() {
      if (
        this.noRenderEvent !== false &&
        this.renderDone &&
        this.widgetReady &&
        !this.triggerRender
      ) {
        this.model.trigger("renderDone", { model: this.model, $el: this.$el });
        this.triggerRender = true;
      }
    },

    remove: function vAttributeRemove() {
      try {
        if (
          this.currentDcpWidget &&
          this.getWidgetClass(this.currentDcpWidget) &&
          this._findWidgetName(this.$el)
        ) {
          this.getWidgetClass(this.currentDcpWidget).call(this.$el, "destroy");
        }
      } catch (e) {
        if (window.dcp.logger) {
          window.dcp.logger(e);
        } else {
          console.error(e);
        }
      }
      return Backbone.View.prototype.remove.call(this);
    },

    hide: function vAttributeHide() {
      this.$el.hide();
    },

    show: function vAttributeShow() {
      this.$el.show();
    },

    _closeWidget: function vAttribute__closeWidget() {
      try {
        if (
          this.currentDcpWidget &&
          this.getWidgetClass(this.currentDcpWidget) &&
          this._findWidgetName(this.currentDcpWidget)
        ) {
          this.getWidgetClass(this.currentDcpWidget).call(
            this.currentDcpWidget,
            "close"
          );
        }
      } catch (e) {
        if (window.dcp.logger) {
          window.dcp.logger(e);
        } else {
          console.error(e);
        }
      }
    },

    _findWidgetName: function vAttribute_findWidgetName($element) {
      return _.find(
        _.keys($element.data()),
        function vAttribute_findWidgetNameFind(currentKey) {
          return currentKey.indexOf("dcpDcp") !== -1;
        }
      );
    },

    _identifyView: function vAttribute_identifyView(event) {
      event.haveView = true;
      //Add the pointer to the current jquery element to a list passed by the event
      event.elements = event.elements.add(this.$el);
    }
  });
});
