import _ from "underscore";
import $ from "jquery";
import Mustache from "mustache";

export default {
  customLineTemplate: "",

  /**
   * Get some data to complete custom attribute template
   * @param documentModel document model
   * @returns {{properties: *, attributes: {}}}
   * @public
   */
  getTemplateModelInfo: function attributeTemplateGetTemplateModelInfo(documentModel) {
    var documentData = documentModel.getDocumentData();
    var templateInfo = {
      properties: documentData.properties,
      attributes: {}
    };
    var currentTemplate = this;

    templateInfo.properties.isWriteMode = templateInfo.properties.renderMode === "edit";
    templateInfo.properties.isReadMode = templateInfo.properties.renderMode === "view";

    _.each(documentData.attributeValues, function associateValue(attributeValue, attributeId) {
      templateInfo.attributes[attributeId] = {
        attributeValue: attributeValue
      };
    });
    _.each(documentData.attributeLabels, function attributeTemplategetTemplateModelInfoEach(
      attributeLabel,
      attributeId
    ) {
      var currentAttributeModel = documentModel.get("attributes").get(attributeId);
      if (currentAttributeModel.getOption("attributeLabel")) {
        attributeLabel = currentAttributeModel.getOption("attributeLabel");
      }

      if (templateInfo.attributes[attributeId]) {
        templateInfo.attributes[attributeId].label = attributeLabel;
      } else {
        templateInfo.attributes[attributeId] = { label: attributeLabel };
      }

      templateInfo.attributes[attributeId].id = attributeId;
      templateInfo.attributes[attributeId].isEmpty = currentTemplate._isEmptyAttribute(currentAttributeModel);

      templateInfo.attributes[attributeId].htmlContent = _.bind(
        currentTemplate.getCustomTemplate,
        currentTemplate,
        currentAttributeModel,
        false,
        false
      );
      templateInfo.attributes[attributeId].htmlView = _.bind(
        currentTemplate.getCustomTemplate,
        currentTemplate,
        currentAttributeModel,
        true,
        false
      );
      templateInfo.attributes[attributeId].htmlDefaultContent = _.bind(
        currentTemplate.getCustomTemplate,
        currentTemplate,
        currentAttributeModel,
        false,
        true
      );
      templateInfo.attributes[attributeId].htmlDefaultView = _.bind(
        currentTemplate.getCustomTemplate,
        currentTemplate,
        currentAttributeModel,
        true,
        true
      );
      templateInfo.attributes[attributeId].isReadMode = currentAttributeModel.get("mode") === "read";
      templateInfo.attributes[attributeId].isWriteMode = currentAttributeModel.get("mode") === "write";
      templateInfo.attributes[attributeId].isVisible = currentAttributeModel.get("mode") !== "hidden";
      templateInfo.attributes[attributeId].renderOptions = currentAttributeModel.getOptions();
      if (currentAttributeModel.get("type") === "array") {
        templateInfo.attributes[attributeId].toolsEnabled =
          templateInfo.attributes[attributeId].isWriteMode &&
          currentAttributeModel.get("visibility") !== "U" &&
          (currentAttributeModel.getOption("rowAddDisable") !== true ||
            currentAttributeModel.getOption("rowDelDisable") !== true ||
            currentAttributeModel.getOption("rowMoveDisable") !== true);
        templateInfo.attributes[attributeId].rows = _.bind(
          currentTemplate.getArrayRowInfo,
          currentTemplate,
          currentAttributeModel
        );
        templateInfo.attributes[attributeId].tableTools = _.bind(
          currentTemplate.getArrayTools,
          currentTemplate,
          currentAttributeModel
        );
      }
    });
    return templateInfo;
  },

  _isEmptyAttribute: function attributeTemplate_isEmptyAttribute(attributeModel) {
    var currentTemplate = this;
    if (attributeModel.get("isValueAttribute")) {
      var attrValue = attributeModel.get("attributeValue");
      return _.isEmpty(attrValue) || attrValue.value === "" || attrValue.value === null;
    }
    if (!attributeModel.get("content") || attributeModel.get("content").length === 0) {
      return true;
    }
    if (attributeModel.get("content").some) {
      return !attributeModel.get("content").some(function attributeTemplate_isEmptyAttribute_checkEmpty(value) {
        return !currentTemplate._isEmptyAttribute(value);
      });
    }
  },

  /**
   * Get some data to complete custom attribute template
   * @returns {{properties: *, attributes: {}}}
   * @private
   */
  getTemplateInfo: function attributeTemplateGetTemplateInfo(attributeModel) {
    var templateInfo = this.getTemplateModelInfo(attributeModel.getDocumentModel());
    var attributeId = attributeModel.id;
    var extraKeys = attributeModel.getOption("templateKeys");

    templateInfo.attribute = templateInfo.attributes[attributeId];
    if (extraKeys) {
      var copyextraKeys = _.clone(extraKeys);
      if (copyextraKeys.attribute && copyextraKeys.attribute.rows) {
        copyextraKeys.attribute = _.clone(extraKeys.attribute);
        delete copyextraKeys.attribute.rows;
      }
      templateInfo = this._deepExtend(templateInfo, copyextraKeys);
    }
    return templateInfo;
  },

  /**
   * Recursively extend data
   *
   * @param target
   * @param source
   * @returns {*}
   */
  _deepExtend: function attributeTemplate_deepExtend(target, source) {
    for (var prop in source) {
      if (source.hasOwnProperty(prop)) {
        if (prop in target) {
          this._deepExtend(target[prop], source[prop]);
        } else {
          target[prop] = source[prop];
        }
      }
    }
    return target;
  },

  /**
   * Construct custom view based on template options
   *
   * @param attrModel Attribute model
   * @param callBackView Callback to call after
   * @returns {*|HTMLElement}
   * @param config
   */
  renderCustomView: function attributeTemplateCustomView(attrModel, callBackView, config) {
    var customTemplate =
      '<div class="dcpCustomTemplate" data-attrid="' + attrModel.id + '">' + attrModel.getOption("template") + "</div>";
    var templateInfo = this.getTemplateInfo(attrModel);
    var $render;
    var completePromise;

    if (config && !_.isUndefined(config.index) && config.index >= 0) {
      templateInfo.attribute.attributeValue = templateInfo.attribute.attributeValue[config.index];
    }
    $render = $(Mustache.render(customTemplate || "", templateInfo));
    completePromise = this.completeCustomContent($render, attrModel.getDocumentModel(), callBackView, config);
    return { $el: $render, promise: completePromise };
  },

  /**
   *
   * Render the custom element of the template
   *
   * @param $el
   * @param documentModel
   * @param callBackView
   * @param config
   */
  completeCustomContent: function attributeTemplateCompleteCustomContent($el, documentModel, callBackView, config) {
    var renderElementPromises = [];
    $el.find(".dcpCustomTemplate--content").each(function attributeTemplatecompleteCustomContentEach() {
      var attrId = $(this).data("attrid"),
        displayLabel = $(this).data("displaylabel") === true,
        currentAttributeModel = documentModel.get("attributes").get(attrId),
        attrContent = "",
        viewImport = null,
        currentElement = $(this),
        // guess if the template is the true version (without template, only the widget)
        originalView = currentElement.data("originalview") === true;

      if (currentAttributeModel) {
        try {
          if (_.isFunction(callBackView)) {
            // When called from vColumn to render widget in a cell
            callBackView.apply(currentElement);
            attrContent = "";
          } else {
            switch (currentAttributeModel.get("type")) {
              case "array":
                viewImport = import("../../views/attributes/array/vArray");
                break;
              case "tab":
                viewImport = import("../../views/attributes/tab/vTabContent");
                break;
              case "frame":
                viewImport = import("../../views/attributes/frame/vFrame");
                break;
              default:
                viewImport = import("../../views/attributes/vAttribute");
            }

            //If the attribute has no template, so it's the view with the widget, we annotate it to bind the events
            if (!currentAttributeModel.getOption("template")) {
              originalView = true;
            }

            if (originalView === false && currentAttributeModel.customViewRended === true) {
              // noinspection ExceptionCaughtLocallyJS
              throw new Error(
                'Cannot use "htmlView" / "htmlContent" for itself on own custom view for ' +
                  currentAttributeModel.id +
                  '. Use "htmlDefaultView" / "htmlDefaultContent" instead'
              );
            }
            if (originalView === false) {
              currentAttributeModel.customViewRended = true;
            }

            renderElementPromises.push(
              viewImport.then(BackView => {
                const view = new BackView.default({
                  model: currentAttributeModel,
                  originalView: originalView,
                  initializeContent: (config && config.initializeContent) || false,
                  displayLabel: displayLabel
                });

                attrContent = view.$el;
                currentElement.append(attrContent);
                return view.render();
              })
            );
          }
        } catch (e) {
          attrContent = $("<div/>")
            .addClass("bg-danger")
            .text(e.message);
        }
      }
      $(this).append(attrContent);
    });
    return Promise.all(renderElementPromises);
  },

  /**
   * Information used when add new line
   * @param attributeModel
   * @param index line index
   * @private
   * @returns {{properties: *, attributes: {}}}
   */
  getLineInfo: function attributeTemplategetLineInfo(attributeModel, index) {
    var documentData = attributeModel.getDocumentModel().getDocumentData();
    var templateInfo = this.getTemplateInfo(attributeModel);
    var extraKeys = attributeModel.getOption("templateKeys");

    _.each(documentData.attributeLabels, function attributeTemplate_eachLabel(attributeLabel, attributeId) {
      var currentAttributeModel = attributeModel
        .getDocumentModel()
        .get("attributes")
        .get(attributeId);
      // Reset some special keys which are not allowed here
      if (currentAttributeModel.get("type") === "array") {
        templateInfo.attributes[attributeId].rows = [];
        templateInfo.attributes[attributeId].tableTools = "NO TABLE TOOL ALLOWED HERE";
      }
    });

    templateInfo.rowTools = this.getRowTool(attributeModel);
    templateInfo.content = {};
    attributeModel.get("content").each(function attributeTemplate_eachContent(currentAttribute) {
      var attributeId = currentAttribute.id;
      var attributeLabel = currentAttribute.get("label");

      if (!currentAttribute.isDisplayable()) {
        return;
      }

      if (currentAttribute.getOption("attributeLabel")) {
        attributeLabel = currentAttribute.getOption("attributeLabel");
      }
      templateInfo.content[attributeId] = {};
      templateInfo.content[attributeId].attributeValue = currentAttribute.get("attributeValue")[index] || {
        value: null, // No value for the moment. Value will be set by array view with default values
        displayValue: ""
      };
      templateInfo.content[attributeId].label = attributeLabel;
      templateInfo.content[attributeId].htmlContent =
        '<div class="dcpCustomTemplate--row dcpArray__content__cell dcpAttribute__content dcpAttribute__content--' +
        currentAttribute.get("type") +
        '" data-displaylabel="false" data-attrid="' +
        attributeId +
        '"/>';
    });

    if (extraKeys && extraKeys.attribute && extraKeys.attribute.rows) {
      _.each(extraKeys.attribute.rows, function attributeTemplategetLineInfoEach(extraValues, extraKey) {
        templateInfo[extraKey] = extraValues[index];
      });
    }

    return templateInfo;
  },

  /**
   * Extract rows template line to customLineTemplate
   * @param attrModel
   * @private
   * @returns {{attribute: {rows: Function}}}
   */
  extractRow: function attributeTemplateExtractRow(attrModel) {
    var scope = this;
    var info;

    info = {
      attribute: {
        rows: function attributeTemplateExtractRowContent() {
          return function attributeTemplate_getRowContent(text) {
            scope.customLineTemplate = text.trim();
          };
        }
      }
    };
    info.attributes = {};
    info.attributes[attrModel.id] = info.attributes;
    return info;
  },

  /**
   * Construct custom line (declared in vArray::render and used in wArray::_getLineContent)
   * @param index
   * @param attrModel
   * @returns {*|HTMLElement}
   */
  customArrayRowView: function attributeTemplateCustomArrayRowView(index, attrModel) {
    var $render;

    // Extract line to customLineTemplate variable
    Mustache.render(attrModel.getOption("template") || "", this.extractRow(attrModel));
    $render = $(Mustache.render(this.customLineTemplate || "", this.getLineInfo(attrModel, index)));

    return $render;
  },

  /**
   * Get element where custom template will be inserted (htmlContent and htmlView)
   * @param attributeModel
   * @param displayLabel
   * @param originalView
   * @private
   * @returns {string}
   */
  getCustomTemplate: function attributeTemplategetCustomTemplate(attributeModel, displayLabel, originalView) {
    if (attributeModel.get("mode") === "hidden") {
      return "";
    }
    return (
      '<div class="dcpCustomTemplate--content ' +
      (displayLabel ? "dcpCustomTemplate--content--view" : "dcpCustomTemplate--content--value") +
      '" data-displaylabel="' +
      (displayLabel ? "true" : "false") +
      '" data-originalview="' +
      (originalView ? "true" : "false") +
      '" data-attrid="' +
      attributeModel.id +
      '"/>'
    );
  },

  /**
   * Extract dcpArray__tools from content array template
   * @param attributeModel
   * @private
   * @returns {*}
   */
  getArrayTools: function attributeTemplateGetArrayTools(attributeModel) {
    var tpls = attributeModel.getTemplates().attribute[attributeModel.get("type")];
    if (tpls && tpls.content) {
      return $(Mustache.render(tpls.content || "", { tools: true }))
        .find(".dcpArray__tools")
        .get(0).outerHTML;
    }
    return "no tools";
  },

  /**
   * Extract dcpArray__content__toolCell from line array template
   * @param attributeModel
   * @private
   * @returns {*}
   */
  getRowTool: function attributeTemplateGetRowTool(attributeModel) {
    var templates = attributeModel.getTemplates().attribute[attributeModel.get("type")];
    var tool = "";
    if (templates && templates.line) {
      tool = $(
        Mustache.render(templates.line || "", {
          tools: true,
          lineCid: _.uniqueId(attributeModel.id)
        })
      )
        .find(".dcpArray__toolCell")
        .html();
    }
    return tool;
  },

  /**
   * Get data for mustache "rows" variable
   * @param attributeModel
   * @private
   * @returns {Array}
   */
  getArrayRowInfo: function attributeTemplategetArrayRowInfo(attributeModel) {
    var rows = [];
    var line = this.getRowTool(attributeModel);

    attributeModel.get("content").each(function attributeTemplate_eachContent(currentAttribute) {
      var values;
      var attributeId = currentAttribute.id;
      var attributeLabel = currentAttribute.get("label");
      if (!currentAttribute.isDisplayable()) {
        return;
      }

      if (currentAttribute.getOption("attributeLabel")) {
        attributeLabel = currentAttribute.getOption("attributeLabel");
      }
      values = currentAttribute.get("attributeValue");
      _.each(values, function attributeTemplategetArrayRowInfoEach(singleValue, index) {
        if (_.isUndefined(rows[index])) {
          rows[index] = { content: {} };
        }
        rows[index].index = index;
        rows[index].content[attributeId] = {
          label: attributeLabel,
          attributeValue: singleValue,
          htmlContent:
            '<div class="dcpCustomTemplate--row dcpArray__content__cell dcpAttribute__content dcpAttribute__content--' +
            currentAttribute.get("type") +
            '"  data-attrid="' +
            currentAttribute.id +
            '"/>'
        };

        rows[index].rowTools = line;
      });
    });

    return rows;
  },

  renderClickDesc: function($tip, nsOn) {
    $tip
      .on("click" + nsOn, function vAttributeShowDesc(event) {
        event.stopPropagation();
        $(this).tooltip("toggle");
      })
      .one("show.bs.tooltip", function wDescTooltip() {
        var tipElement = $(this).data("bs.tooltip").tip;
        if (tipElement) {
          $(tipElement).addClass("dcpAttribute__description-info");
        }
      })
      .one("shown.bs.tooltip", function wDescTooltip() {
        var tipElement = $(this).data("bs.tooltip").tip;
        if (tipElement) {
          $(tipElement)
            .find(".tooltip-inner")
            .prepend('<span class="btn btn-secondary button-close-error"><span class="fa fa-times" />&nbsp;</span>');

          $(tipElement).on("click" + nsOn, ".button-close-error", function vAttributeCloseDesc(event) {
            event.stopPropagation();
            $(tipElement).tooltip("hide");
          });
        }
      });
  },

  /**
   * Insert attribute description
   * @param attributeView Backbone view
   * @param $customElement specific other DOM element instead of default element view
   */
  insertDescription: function attributeTemplateInsertDescription(attributeView, $customElement) {
    var data = attributeView.model.toData(null, true);

    if (!data.renderOptions || !data.renderOptions.description) {
      return;
    }
    var descriptionTemplate;
    var $tip;
    var nsOn = ".v" + attributeView.model.cid;
    var $viewElement = $customElement ? $customElement : attributeView.$el;
    var isFrame = $viewElement.hasClass("dcpFrame");
    var isArray = $viewElement.hasClass("dcpArray");
    var isArrayHead = $viewElement.hasClass("dcpArray__head__cell");
    var isArrayCell = $viewElement.hasClass("dcpArray__cell");
    var isAttribute = $viewElement.hasClass("dcpAttribute");
    var isTabContent = $viewElement.hasClass("dcpTab__content");
    var isTabLabel = $viewElement.hasClass("dcpTab__label");
    var $descriptionElement;

    // Insert description once for this smart field
    // Identity selector
    const classSelector = `.${$viewElement
      .attr("class")
      .trim()
      .replace(/ /g, ".")}[data-attrid=${data.id}]`;
    const descriptionSelector = `.dcpAttribute__description[data-attrid=${data.id}]`;
    // Search description in the parent tree
    const $descriptionInserted = $viewElement
      .parent()
      .closest(classSelector)
      .find(descriptionSelector);
    if ($descriptionInserted.length) {
      return;
    }
    descriptionTemplate = attributeView.model.getTemplates().attribute.description;
    data.renderOptions.description.htmlContentRender = Mustache.render(
      data.renderOptions.description.htmlContent || "",
      data
    );
    data.renderOptions.description.htmlTitleRender = Mustache.render(
      data.renderOptions.description.htmlTitle || "",
      data
    );

    $descriptionElement = $(Mustache.render(descriptionTemplate || "", data));
    if (!data.renderOptions.description.position) {
      data.renderOptions.description.position = "top";
    }
    if (isFrame) {
      switch (data.renderOptions.description.position) {
        case "bottom":
          $viewElement.append($descriptionElement);
          break;
        case "top":
        case "topLabel":
          $viewElement.prepend($descriptionElement);
          break;
        case "left":
          $viewElement.find(".dcpFrame__content").addClass("dcpFrame__content--left-description");
          $descriptionElement.insertAfter($viewElement.find(".dcpFrame__label"));
          break;
        case "right":
          // Need to add class because no have css selector
          $viewElement.append($descriptionElement);
          $viewElement.find(".dcpFrame__content").addClass("dcpFrame__content--right-description");
          break;
        case "bottomLabel":
          $descriptionElement.insertAfter($viewElement.find(".dcpFrame__label"));
          break;
        case "topValue":
          $viewElement.find(".dcpFrame__content").prepend($descriptionElement);
          break;
        case "bottomValue":
          $viewElement.find(".dcpFrame__content").append($descriptionElement);
          break;
        case "click":
          $viewElement.append($descriptionElement);
          $viewElement
            .find(".dcpFrame__label")
            .prepend('<a class="dcpAttribute__label_description"><span class="fa fa-info-circle"></span></a>');
          $tip = $viewElement.find(".dcpFrame__label > .dcpAttribute__label_description").tooltip({
            html: true,
            container: $viewElement,
            title: $descriptionElement,
            placement: "auto",
            trigger: "manual"
          });

          this.renderClickDesc($tip, nsOn);
      }
    }
    if (isTabContent || isTabLabel) {
      switch (data.renderOptions.description.position) {
        case "top":
        case "topValue":
          if (isTabContent) {
            $viewElement.prepend($descriptionElement);
          }
          break;
        case "bottom":
        case "bottomValue":
          if (isTabContent) {
            $viewElement.append($descriptionElement);
          }
          break;
        case "click":
          if (isTabLabel) {
            $viewElement.append($descriptionElement);
            $viewElement
              .find(".dcpLabel__text")
              .prepend('<a class="dcpAttribute__label_description"><span class="fa fa-info-circle"></span></a>');

            $tip = $viewElement.find(".dcpAttribute__label_description").tooltip({
              html: true,
              container: ".dcpDocument",
              title: $descriptionElement,
              placement: "auto",
              trigger: "manual"
            });

            this.renderClickDesc($tip, nsOn);
          }
          break;

        case "bottomLabel":
        case "topLabel":
        case "left":
        case "right":
          console.error(
            'Cannot use "' +
              data.renderOptions.description.position +
              '" description position in tab attribute : ' +
              data.id
          );
      }
    }
    if (isArray) {
      switch (data.renderOptions.description.position) {
        case "top":
          $viewElement.prepend($descriptionElement);
          break;
        case "topLabel":
          $viewElement.find(".dcpArray__label").prepend($descriptionElement);
          break;
        case "bottomLabel":
          $viewElement.find(".dcpArray__label").append($descriptionElement);
          break;
        case "topValue":
          $viewElement.find(".dcpArray__content").prepend($descriptionElement);
          break;
        case "bottomValue":
          $descriptionElement.insertAfter($viewElement.find(".dcpArray__table"));
          break;
        case "bottom":
          $viewElement.append($descriptionElement);
          break;
        case "click":
          $viewElement.append($descriptionElement);
          $viewElement
            .find(".dcpArray__label")
            .prepend('<a class="dcpAttribute__label_description"><span class="fa fa-info-circle"></span></a>');

          $tip = $viewElement.find(".dcpArray__label > .dcpAttribute__label_description").tooltip({
            html: true,
            container: $viewElement,
            title: $descriptionElement,
            placement: "auto",
            trigger: "manual"
          });

          this.renderClickDesc($tip, nsOn);

          break;

        case "left":
        case "right":
          console.error(
            'Cannot use "' +
              data.renderOptions.description.position +
              '" description position in array attribute : ' +
              data.id
          );
          break;
      }
    }
    if (isAttribute) {
      const isLeft = attributeView.model.getOption("labelPosition") === "left";
      switch (data.renderOptions.description.position) {
        case "bottom":
          $viewElement.append($descriptionElement);
          break;
        case "top":
          $viewElement.prepend($descriptionElement);
          break;
        case "left":
          $viewElement.find(".dcpAttribute__label").append($descriptionElement);
          break;
        case "right":
          $viewElement.find(".dcpAttribute__content").append($descriptionElement);
          break;
        case "topValue":
          if (isLeft) {
            $viewElement.prepend($descriptionElement);
            $descriptionElement.addClass("dcpAttribute__right");
          } else {
            $viewElement.find(".dcpAttribute__content").prepend($descriptionElement);
          }
          break;
        case "topLabel":
          $viewElement.prepend($descriptionElement);
          $descriptionElement.addClass("dcpAttribute__left");
          break;
        case "bottomValue":
          $viewElement.append($descriptionElement);
          $descriptionElement.addClass("dcpAttribute__right");
          break;
        case "bottomLabel":
          $viewElement.append($descriptionElement);
          $descriptionElement.addClass("dcpAttribute__left");
          break;
        case "click":
          $viewElement.append($descriptionElement);
          $viewElement
            .find(".dcpAttribute__label")
            .append('<a class="dcpAttribute__label_description"><span class="fa fa-info-circle"></span></a>');

          $tip = $viewElement.find(".dcpAttribute__label_description").tooltip({
            html: true,
            container: $viewElement,
            placement: "auto",
            title: $descriptionElement,
            trigger: "manual"
          });

          this.renderClickDesc($tip, nsOn);
      }
    }

    if (isArrayCell || isArrayHead) {
      switch (data.renderOptions.description.position) {
        case "topLabel":
        case "top":
          if (isArrayHead) {
            $viewElement.prepend($descriptionElement);
          }
          break;
        case "topValue":
          if (isArrayCell) {
            $viewElement.prepend($descriptionElement);
          }
          break;
        case "bottomValue":
          if (isArrayCell) {
            $viewElement.append($descriptionElement);
          }
          break;
        case "bottom":
        case "bottomLabel":
          if (isArrayHead) {
            $viewElement.append($descriptionElement);
          }
          break;
        case "click":
          if (isArrayHead) {
            $viewElement.append($descriptionElement);
            $viewElement.prepend(
              '<a class="dcpAttribute__label_description"><span class="fa fa-info-circle"></span></a>'
            );

            $tip = $viewElement.find(".dcpAttribute__label_description").tooltip({
              html: true,
              container: $viewElement,
              placement: "auto",
              title: $viewElement.find("> .dcpAttribute__description"),
              trigger: "manual"
            });

            this.renderClickDesc($tip, nsOn);
          }
          break;
        case "left":
        case "right":
          // No use in column context
          console.error(
            'Cannot use "' +
              data.renderOptions.description.position +
              '" description position in column attribute : ' +
              data.id
          );
          break;
      }
    }
    if (data.renderOptions.description.htmlContent) {
      $viewElement.on("click" + nsOn, ".dcpAttribute__description__title", function vAttribute_descToggle() {
        var $contentElement = $(this)
          .closest(".dcpAttribute__description")
          .find(".dcpAttribute__description__content");
        $(this)
          .find(".dcpAttribute__description__title__expand")
          .toggleClass("fa-caret-right fa-caret-down");
        $contentElement.slideToggle(200);
        return false;
      });
      if (data.renderOptions.description.collapsed === true) {
        $viewElement.find(".dcpAttribute__description__title__expand").toggleClass("fa-caret-right fa-caret-down");
        $viewElement.find(".dcpAttribute__description__content").hide();
      }
    }
  }
};
