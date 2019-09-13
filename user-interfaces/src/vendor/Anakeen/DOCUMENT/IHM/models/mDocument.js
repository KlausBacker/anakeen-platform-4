/*global define, console */

define([
  "jquery",
  "underscore",
  "backbone",
  "./libSmartForm",
  "little-loader",
  "dcpDocument/models/mDocumentProperties",
  "dcpDocument/models/mDocumentLock",
  "dcpDocument/models/mFamilyStructure",
  "dcpDocument/collections/attributes",
  "dcpDocument/collections/menus",
  "dcpDocument/i18n/documentCatalog",
  "dcpDocument/widgets/globalController/utils/EventUtils"
], function mDocument(
  $,
  _,
  Backbone,
  LibSmartForm,
  load,
  DocumentProperties,
  DocumentLock,
  FamilyStructure,
  CollectionAttributes,
  CollectionMenus,
  i18n,
  EventPromiseUtils
) {
  "use strict";

  var flattenAttributes = function mDocumentflattenAttributes(currentAttributes, attributes, parent) {
    if (!_.isArray(attributes)) {
      attributes = _.values(attributes);
    }
    if (parent) {
      _.each(attributes, function mDocumentEachflattenParentAttributes(value) {
        value.parent = parent;
      });
    }
    currentAttributes = _.union(currentAttributes, attributes);
    _.each(attributes, function mDocumentEachflattenAttributes(currentAttr) {
      if (currentAttr.content) {
        currentAttributes = _.union(
          currentAttributes,
          flattenAttributes(currentAttributes, currentAttr.content, currentAttr.id)
        );
      }
    });
    return currentAttributes;
  };

  //noinspection JSValidateJSDoc
  return Backbone.Model.extend({
    typeModel: "ddui:document",
    idAttribute: "initid",

    defaults: {
      revision: -1,
      viewId: undefined,
      renderMode: "view",
      properties: undefined,
      menus: undefined,
      attributes: undefined
    },
    // Record custom data in model directly - not in model property because must not be reset by clear method
    _customClientData: {},
    _uploadingFile: 0,
    /**
     * Compute the REST URL for the current document
     *
     * Used internaly by backbone in fetch, save, destroy
     *
     * @returns {string}
     */
    url: function mDocumenturl() {
      var urlData = "/api/v2/",
        viewId = this.get("viewId");
      var customClientData = this._customClientData;
      var currentMethod = this.get("currentHttpMethod");
      var revision = this.get("revision");

      if (this.get("creationFamid") && this.id === null) {
        urlData += "smart-structures/" + encodeURIComponent(this.get("creationFamid")) + "/documentsViews/";
      } else {
        urlData += "smart-elements/" + encodeURIComponent(this.id);
        //Don't add revision for the deletion of a alive document
        if (revision !== null && currentMethod !== "delete") {
          if (_.isObject(revision) && revision.state) {
            urlData += "/revisions/" + encodeURIComponent("state:" + revision.state);
          } else if (revision >= 0) {
            urlData += "/revisions/" + encodeURIComponent(revision);
          }
        }
        if (viewId === undefined) {
          if (this.get("renderMode") === "view" || currentMethod === "delete") {
            viewId = "!defaultConsultation";
          } else if (this.get("renderMode") === "edit") {
            viewId = "!defaultEdition";
          } else {
            viewId = "!defaultConsultation";
          }
        }
        if (currentMethod === "delete" && this.get("renderMode") === "edit") {
          viewId = "!defaultConsultation";
        }
        urlData += "/views/" + encodeURIComponent(viewId);
      }

      if (!_.isEmpty(customClientData) && (currentMethod === "read" || currentMethod === "delete")) {
        urlData += "?customClientData=" + encodeURIComponent(JSON.stringify(customClientData));
      }
      return urlData;
    },

    /**
     * override Backbone sync to record method
     * @param method
     * @param model
     * @param options
     * @returns {*}
     */
    sync: function mDocumentSync(method, model, options) {
      if (this._formConfiguration) {
        LibSmartForm.smartFormSync(method, model, options);
      } else {
        this.set("currentHttpMethod", method); // record for url method
        options.attrs = this._customRequestData;
        return Backbone.Model.prototype.sync.apply(this, arguments);
      }
    },
    /**
     * Initialize event handling
     *
     */
    initialize: function mDocumentinitialize() {
      var theModel = this;

      this.listenTo(this, "dduiDocumentFail", this.propagateSynchroError);
      this.listenTo(this, "destroy", this.destroySubcollection);
      this.listenTo(this, "destroy", this.unbindLoadEvent);
      $(window).on("beforeunload." + this.cid, function mDocumentBeforeUnload() {
        var security = theModel.get("properties") ? theModel.get("properties").get("security") : null,
          event = { prevent: false };
        if (theModel.hasAttributesChanged()) {
          return i18n.___("The form has been modified and is is not saved", "ddui");
        }

        theModel.trigger("beforeClose", event, theModel.getServerProperties(), this._customClientData);

        if (event.prevent) {
          return i18n.___("Unable to close the document", "ddui");
        }

        if (theModel.get("renderMode") === "edit" && security && security.lock && security.lock.temporary) {
          // No use model destroy : page is destroyed before request is some case
          $.ajax({
            url: "/api/v2/smart-elements/" + theModel.get("initid") + "/locks/temporary",
            type: "DELETE",
            async: false
          });
          theModel.set("unlocking", true);
        }
      });

      $(window).on("pagehide." + this.cid, function mDocumentPageHide(event) {
        var security = theModel.get("properties") ? theModel.get("properties").get("security") : null;
        var unlocking = theModel.get("unlocking");

        theModel.trigger("close", event, theModel.getServerProperties(), this._customClientData);

        if (
          !unlocking &&
          theModel.get("renderMode") === "edit" &&
          security &&
          security.lock &&
          security.lock.temporary
        ) {
          $.ajax({
            url: "/api/v2/smart-elements/" + theModel.get("initid") + "/locks/temporary",
            type: "DELETE",
            async: false
          });
        }
      });
    },

    /**
     * Return a plain object of the current document for an usage in the view
     *
     * @returns {{document: {}}}
     */
    toData: function mDocumenttoData() {
      var returnObject = {
        document: {}
      };
      returnObject.document.properties = this.getServerProperties();
      returnObject.menus = this.get("menus").toJSON();
      returnObject.templates = this.get("templates");
      return returnObject;
    },

    /**
     * Return all the values of the current document
     * @var onlyModified
     * @returns {{}}
     */
    getValues: function mDocumentdocumentGetValues(onlyModified) {
      var values = {};
      if (!this.get("attributes")) {
        return values;
      }
      this.get("attributes").each(function mDocumentGetValue(currentAttribute) {
        var currentValue = currentAttribute.get("attributeValue"),
          i,
          arrayValues = [];
        if (currentValue === null || !currentAttribute.get("isValueAttribute")) {
          return;
        }
        if (onlyModified === true && currentAttribute.hasValueChanged() === false) {
          return;
        }
        if (currentAttribute.get("multiple")) {
          currentValue = _.toArray(currentValue);
          if (currentValue.length > 0) {
            for (i = 0; i < currentValue.length; i += 1) {
              arrayValues.push(currentValue[i] || { value: null });
            }
          } else {
            arrayValues = []; //{value: null};
          }
          values[currentAttribute.id] = arrayValues;
        } else {
          values[currentAttribute.id] = currentValue;
        }
      });
      return values;
    },

    /**
     * reset all values with a new set of values
     */
    setValues: function mDocumentdocumentSetValues(values) {
      this.get("attributes").each(function mDocumentSetValue(currentAttribute) {
        var newValue = values[currentAttribute.id];
        if (!newValue || !newValue.value || !currentAttribute.get("isValueAttribute")) {
          return;
        }
        currentAttribute.set("attributeValue", newValue);
        // reset change also
        currentAttribute.changed = {};
      });
    },

    /**
     * reset all properties with a new set of properties
     */
    setProperties: function mDocumentdocumentSetProperties(values) {
      var model = this;
      _.each(values, function mDocumentSetProperties(value, key) {
        model.get("properties").set(key, value);
      });
    },

    lockDocument: function mDocumentLockDocument() {
      var docModel = this;
      var lockModel = new DocumentLock({
        initid: this.get("initid"),
        type: "permanent"
      });
      var security = this.get("properties").get("security");
      lockModel.save(
        {},
        {
          success: function mDocumentLockDocumentSuccess(theModel, data) {
            var menu = docModel.get("menus");
            security.lock = data.data.lock;
            docModel.get("properties").set("security,", security);

            menu.setMenu("lock", "visibility", "hidden");
            menu.setMenu("unlock", "visibility", "visible");

            docModel.get("properties").trigger("change");
          },
          error: function mDocumentLockDocumentError(theModel, HttpResponse) {
            var response = JSON.parse(HttpResponse.responseText);

            docModel.trigger("showError", {
              title: response.exceptionMessage
            });
          }
        }
      );
    },

    unlockDocument: function mDocumentUnLockDocument() {
      var docModel = this;
      //  type = empty means Delete all locks
      var lockModel = new DocumentLock({
        initid: this.get("initid"),
        type: ""
      });
      var security = this.get("properties").get("security");
      lockModel.destroy({
        success: function mDocumentUnLockDocumentSuccess() {
          var menu = docModel.get("menus");
          security.lock = {
            lockedBy: {
              id: 0
            }
          };
          docModel.get("properties").set("security,", security);

          menu.setMenu("lock", "visibility", "visible");
          menu.setMenu("unlock", "visibility", "hidden");
          docModel.get("properties").trigger("change");
        },
        error: function mDocumentUnLockDocumentError(theModel, HttpResponse) {
          var response = JSON.parse(HttpResponse.responseText);

          docModel.trigger("showError", {
            title: response.exceptionMessage
          });
        }
      });
    },
    /**
     * Get a plain object with properties of the document
     *
     * @returns {*}
     */
    getModelProperties: function mDocumentdocumentGetProperties() {
      var properties = {};
      if (this.get("properties")) {
        properties = this.get("properties").toJSON();
      }
      properties.initid = this.get("initid");
      properties.revision = this.get("revision");
      properties.viewId = this.get("viewId");
      properties.renderMode = this.get("renderMode");

      return properties;
    },

    /**
     * Get the initial properties as transfered by the server
     * @returns {*|{revision, viewId, renderMode, properties, menus, attributes}|{resizeMarginHeight, resizeMarginWidth, resizeDebounceTime, withoutResize, eventPrefix}|{content}|{title, isValueAttribute, parent, mode, errorMessage}|{documentId, documentModel, state, attributes}}
     */
    getServerProperties: function mDocument_getCurrentProperties() {
      var properties;
      properties = this.initialProperties;
      if (properties) {
        properties.hasUploadingFiles = this.hasUploadingFile();
      }
      return properties;
    },

    /**
     * Indicate if one attribute of the document is modified
     *
     * @returns {*|boolean}
     */
    isModified: function mDocument_isModified() {
      return this.hasAttributesChanged();
    },

    /**
     * Get document properties, values and labels of attributes
     */
    getDocumentData: function mDocumentGetDocumentData() {
      var documentData = {
        properties: this.getModelProperties(),
        attributeValues: this.getValues(false),
        attributeLabels: {},
        createAttributeView: function mDocumentGetDocumentDataCreate() {
          return this.id;
        }
      };
      this.get("attributes").each(function mDocumentGetDocumentDataEach(currentAttribute) {
        documentData.attributeLabels[currentAttribute.id] = currentAttribute.get("label");
      });
      return documentData;
    },
    /**
     * Return true if one the attribute of the document hasChanged
     *
     * @returns {boolean|*}
     */
    hasAttributesChanged: function mDocumenthasAttributesChanged() {
      if (!this.get("attributes")) {
        return false;
      }
      return this.get("attributes").some(function mDocumenthasAttributesChangedSome(currentAttr) {
        return currentAttr.hasValueChanged();
      });
    },

    hasUploadingFile: function mdocumenthasUploadingFile() {
      return this._uploadingFile > 0;
    },

    /**
     * Analyze return in case of sync uncomplete and trigger event error
     *
     * @param model
     * @param xhr
     */
    propagateSynchroError: function mDocumentpropagateSynchroError(model, xhr) {
      var attrModel,
        currentModel = this,
        parsedReturn,
        errorCode = null,
        title = "",
        properties;
      //Analyze XHR
      var messages = [];
      var result;
      try {
        if (!xhr) {
          if (model && model.message) {
            messages.push({ type: "error", contentText: model.message });
          }
          xhr = { status: 500, statusText: "Internal - No HTTP response" };
        } else {
          if (!xhr.message) {
            result = JSON.parse(xhr.responseText);
            if (result.message) {
              messages.push(result);
            } else if (result.messages) {
              messages = result.messages;
            }
          }
        }
      } catch (e) {
        //Unable to parse responseText (error is not in JSON)
        this.cleanErrorMessages();
        if (window.dcp.logger) {
          window.dcp.logger(e);
        } else {
          console.error(e);
        }
        properties = currentModel.getServerProperties();
        if (!properties || !properties.initid) {
          //First loading, unable to load display reload iframe panel
          currentModel.trigger("displayNetworkError");
          return;
        }
        //There is an initd, so there is a document
        //We display a message and let the user try again
        //Status 0 indicate offline browser
        if (xhr && xhr.status === 0) {
          currentModel.trigger("showError", {
            errorCode: "offline",
            title: i18n.___("Your navigator seems offline, try later", "ddui")
          });
        } else {
          currentModel.trigger("showError", {
            errorCode: "unableToParseJson",
            title: i18n.___("Server return unreadable", "ddui")
          });
        }
        currentModel.setProperties(properties);
        this.trigger("dduiDocumentDisplayView");
        return;
      }

      parsedReturn = {
        messages: messages || [],
        responseText: xhr.message || "Unexpected error: " + xhr.status + " " + xhr.statusText
      };

      this.cleanErrorMessages();
      if (parsedReturn.messages.length === 0) {
        if (result && result.exceptionMessage) {
          parsedReturn.responseText = result.exceptionMessage;
        }

        if (currentModel.get("properties")) {
          title = currentModel.get("properties").get("title");
        }
        currentModel.trigger("showError", {
          errorCode: errorCode,
          title: i18n.___("Unexpected error ", "ddui") + " : " + title,
          message: parsedReturn.messages.length === 0 ? parsedReturn.responseText : parsedReturn.messages.join(" ")
        });
      }
      _.each(parsedReturn.messages, function mDocumentpropagateSynchroErrorMessages(message) {
        switch (message.code) {
          case "ROUTES0107": // Syntax Error
            if (message.data && message.data.id) {
              attrModel = currentModel.get("attributes").get(message.data.id);
              if (attrModel) {
                attrModel.setErrorMessage(message.data.err, message.data.index);
                currentModel.trigger("showError", {
                  title: message.contentText,
                  htmlMessage: message.contentHtml,
                  message: attrModel.attributes.label + " : " + message.data.err,
                  errorCode: message.code
                });
              } else {
                currentModel.trigger("showError", {
                  title: message.contentText,
                  htmlMessage: message.contentHtml,
                  message: message.data.err,
                  errorCode: message.code
                });
              }
            }
            break;
          case "ROUTES0109": // Constraint Error
            if (message.data && message.data.constraint) {
              _.each(message.data.constraint, function mDocumentpropagateSynchroError0212(constraint) {
                attrModel = currentModel.get("attributes").get(constraint.id);
                if (attrModel) {
                  attrModel.setErrorMessage(constraint.err, constraint.index);
                  currentModel.trigger("showError", {
                    title: message.contentText,
                    htmlMessage: message.contentHtml,
                    message: attrModel.attributes.label + " : " + constraint.err,
                    errorCode: message.code
                  });
                } else {
                  currentModel.trigger("showError", {
                    title: message.contentText,
                    htmlMessage: message.contentHtml,
                    message: constraint.err,
                    errorCode: message.code
                  });
                }
              });
            }
            if (message.data && message.data.preStore) {
              currentModel.trigger("showError", {
                title: message.contentText,
                htmlMessage: message.contentHtml,
                message: message.data.preStore,
                errorCode: message.code
              });
            }
            break;

          default:
            if (message.exceptionMessage) {
              currentModel.trigger("showError", {
                title: message.message + " " + (message.code ? message.code : ""),
                errorCode: message.code
              });
            } else {
              if (message.type === "error" && message.contentText) {
                currentModel.trigger("showError", {
                  title: message.contentText + " " + (message.code ? message.code : ""),
                  type: message.type,
                  message: message.contentText,
                  htmlMessage: message.contentHtml,
                  errorCode: message.code
                });
              } else {
                if (message.type && message.contentText) {
                  currentModel.trigger("showMessage", {
                    title: message.contentText + " " + (message.code ? message.code : ""),
                    type: message.type,
                    message: message.contentText,
                    htmlMessage: message.contentHtml,
                    errorCode: message.code
                  });
                } else {
                  console.error("Error", message);
                }
              }
            }
        }
      });
      this.trigger("dduiDocumentDisplayView");
    },

    /**
     * Validate the content of the model before synchro
     */
    validate: function mDocumentvalidate(documentElements, options) {
      var success = true,
        currentDocument = this,
        errorMessage = [],
        templateMessage,
        error;
      try {
        this.get("attributes").each(function mDocumentvalidateClearErrorEach(currentAttribute) {
          currentAttribute.setErrorMessage(null);
        });

        this.get("attributes").each(function mDocumentvalidateEach(currentAttribute) {
          var parentAttribute = currentDocument.get("attributes").get(currentAttribute.get("parent"));

          if (currentAttribute.get("needed") === true) {
            var currentValue = currentAttribute.get("attributeValue"),
              oneSuccess = true;

            if (currentAttribute.get("multiple")) {
              if (parentAttribute.get("type") === "array") {
                // Verify each index
                _.each(currentValue, function mDocumentvalidateArray(attributeValue, index) {
                  //If the attribute is multiple we check if the array has a size superior to 0
                  if (_.isArray(attributeValue) && attributeValue.length > 0) {
                    return;
                  }
                  if ((!attributeValue || !attributeValue.value) && attributeValue.value !== 0) {
                    currentAttribute.setErrorMessage(i18n.___("Empty value not allowed", "ddui"), index);

                    templateMessage = _.template(
                      i18n.___("{{parentLabel}} / {{label}} (row # {{index}}) is needed", "ddui"),
                      { escape: /\{\{(.+?)\}\}/g }
                    );
                    errorMessage.push(
                      templateMessage({
                        parentLabel: parentAttribute.get("label"),
                        label: currentAttribute.get("label"),
                        index: index + 1
                      })
                    );
                    success = false;
                  }
                });
              } else {
                if (!currentValue || currentValue.length === 0) {
                  oneSuccess = false;
                }
              }
            } else {
              if (!currentValue || (!currentValue.value && currentValue.value !== 0)) {
                oneSuccess = false;
              }
            }
            if (!oneSuccess) {
              templateMessage = _.template(i18n.___("{{parentLabel}} / {{label}} is needed", "ddui"), {
                escape: /\{\{(.+?)\}\}/g
              });
              errorMessage.push(
                templateMessage({
                  parentLabel: parentAttribute.get("label"),
                  label: currentAttribute.get("label")
                })
              );
              currentAttribute.setErrorMessage(i18n.___("The field must not be empty", "ddui"));
              success = false;
            }
          }

          if (!currentAttribute.checkConstraint({ clearError: false })) {
            success = false;
            if (_.isArray(currentAttribute.get("errorMessage"))) {
              templateMessage = _.template(
                "<%= parentLabel %> / <%= label %> " +
                  "<% for(var msg in errorMessage) { %>" +
                  "\n<%- rowText %> <%= errorMessage[msg].index + 1 %> : <%= errorMessage[msg].message %>\n <% } %> "
              );
            } else {
              templateMessage = _.template("<%= parentLabel %> / <%= label %> <%= errorMessage %>");
            }
            errorMessage.push(
              templateMessage({
                parentLabel: parentAttribute.get("label"),
                label: currentAttribute.get("label"),
                rowText: i18n.___("Row #", "ddui"),
                errorMessage: currentAttribute.get("errorMessage")
              })
            );
          }
        });
        if (!success) {
          error = {
            title: i18n.___("Unable to save", "ddui"),
            message: errorMessage.join(", " + "\n"),
            errorCode: "attributeNeeded"
          };
          if (options && options.error) {
            options.error(_.extend({ eventPrevented: true }, error));
          }
          return error;
        }
      } catch (e) {
        console.error("Unable to validate");
        console.error(e);
      }

      return undefined;
    },

    /**
     * Propagate to attributes a clear message for the error displayed
     */
    cleanErrorMessages: function mDocumentCleanErrorMessages() {
      var attrModels = this.get("attributes") || [];
      _.each(attrModels.models, function mDocumentCleanErrorMessagesEach(attrModel) {
        attrModel.setErrorMessage(null);
      });
    },
    /**
     * Parse the return of the REST API
     * @param response
     * @returns {{properties: (*|properties|exports.defaults.properties|exports.parse.properties|.createObjectExpression.properties), menus: (app.views.shared.menu|*), locale: *, renderMode: string, attributes: Array, templates: *, renderOptions: *}}
     */
    parse: function mDocumentParse(response) {
      var values,
        renderMode = "view",
        view = response.data.view;

      if (response.success === false) {
        throw new Error("Unable to get the data from documents");
      }
      this.trigger("beforeParse");
      if (view.renderOptions.mode) {
        if (view.renderOptions.mode === "edit") {
          renderMode = "edit";
        } else if (view.renderOptions.mode === "view") {
          renderMode = "view";
        } else {
          throw new Error("Unkown render mode " + view.renderOptions.mode);
        }
      }

      this.initialProperties = _.defaults(
        {
          initid: response.data.properties.creationView === true ? null : view.documentData.document.properties.initid,
          renderMode: renderMode || "view",
          viewId: response.data.properties.requestIdentifier
        },
        view.documentData.document.properties
      );

      //Clean attributes
      if (this.get("attributes") instanceof CollectionAttributes) {
        this.get("attributes").destroy();
      }

      values = {
        initid: response.data.properties.creationView === true ? null : view.documentData.document.properties.initid,
        properties: view.documentData.document.properties,
        menus: view.menu,
        viewId: response.data.properties.requestIdentifier,
        revision: view.documentData.document.properties.revision,
        locale: view.locale.culture,
        renderMode: renderMode || "view",
        attributes: undefined,
        templates: view.templates,
        renderOptions: view.renderOptions,
        customCSS: view.style.css,
        customJS: view.script.js,
        customServerData: view.customServerData,
        messages: response.messages,
        originalValues: view.documentData.document.attributes
      };
      this._customClientData = {};
      this._customRequestData = null;
      if (response.data.properties.creationView === true) {
        values.creationFamid = view.documentData.document.properties.family.name;
      } else {
        values.creationFamid = false;
      }
      return values;
    },

    /**
     * Generate the collection of the current model and bind events on the new collection
     *
     * @param keyOrValues string|object of properties or key of the current property
     * @param value
     * @returns {*}
     */
    set: function mDocumentsetValues(keyOrValues, value) {
      var currentModel = this;
      if (keyOrValues.properties !== undefined) {
        if (currentModel.get("properties") instanceof DocumentProperties) {
          currentModel.get("properties").trigger("destroy");
        }
        keyOrValues.properties = new DocumentProperties(keyOrValues.properties);
      }

      if (keyOrValues.menus !== undefined) {
        if (currentModel.get("menus") instanceof CollectionMenus) {
          currentModel.get("menus").destroy();
        }
        keyOrValues.menus = new CollectionMenus(keyOrValues.menus);
      }
      if (keyOrValues === "attributes") {
        value = new CollectionAttributes(value, {
          documentModel: currentModel,
          renderOptions: currentModel.get("renderOptions"),
          renderMode: currentModel.get("renderMode")
        });

        //Set the internal content collection (for structure attributes)
        value.each(function mDocumentsetValuesEachAttributes(currentAttributeModel) {
          if (currentAttributeModel.get("isValueAttribute")) {
            return;
          }
          var childAttributes = value.filter(function mDocumentsetValuesEachAttributesFilter(candidateChildModel) {
            return candidateChildModel.get("parent") === currentAttributeModel.id;
          });
          if (childAttributes.length > 0) {
            currentAttributeModel.setContentCollection(childAttributes);
          }
        });
        //Propagate the change event to the model
        currentModel.listenTo(value, "change:attributeValue", function mDocumentsetValuesListenChange(model) {
          _.defer(function mDocumentAttributeChangerTrigger() {
            currentModel.trigger("changeValue", {
              attributeId: model.id
            });
          });
        });
        //Propagate the validate event to the model
        currentModel.listenTo(value, "constraint", function mDocumentsetValuesListenConstraint(options) {
          currentModel.trigger("constraint", options.model.id, options.response);
        });
        //Propagate the renderDone event of the attributes to the model
        currentModel.listenTo(value, "renderDone", function mDocumentsetValuesListenRenderDone(options) {
          currentModel.trigger("attributeRender", options.model.id, options.$el, options.index);
        });
        //Propagate the beforeRender event of the attributes to the model
        currentModel.listenTo(value, "beforeRender", function mDocumentsetValuesListenBeforeRender(event, options) {
          currentModel.trigger("beforeAttributeRender", event, options.model.id, options.$el, options.index);
        });
        //Propagate the array event modified to the model
        currentModel.listenTo(value, "array", function mDocumentsetValuesListenArray(type, model, options) {
          currentModel.trigger("arrayModified", {
            attributeId: model.id,
            type: type,
            options: options
          });
        });
        //Propagate the event externalLinkSelected to the model
        currentModel.listenTo(value, "internalLinkSelected", function mDocumentsetValuesListenLinkSelected(
          event,
          options
        ) {
          currentModel.trigger("internalLinkSelected", event, options);
        });
        //Propagate the event downloadFile to the model
        currentModel.listenTo(value, "downloadFile", function mDocumentsetValuesListenDownloadfile(
          event,
          attrid,
          options
        ) {
          currentModel.trigger("downloadFile", event, attrid, options);
        });

        //Propagate the event uploadFile to the model
        currentModel.listenTo(value, "uploadFile", function mDocumentListenUploadfileStart(event, attrid, options) {
          var attr, attrValue;
          currentModel.trigger("uploadFile", event, attrid, options);

          if (!event.prevent) {
            attr = currentModel.get("attributes").get(attrid);

            currentModel._uploadingFile++;

            if (attr) {
              attrValue = attr.get("attributeValue");
              if (options.index >= 0) {
                attrValue = _.clone(attrValue);
                attrValue[options.index] = {
                  value: "-^-",
                  displayValue: "Uploading"
                };
              } else {
                attrValue = { value: "--", displayValue: "Uploading" };
              }
              // Use Silent to not redraw widget - it will be redraw at the end of uploading
              attr.set("attributeValue", attrValue, { silent: true });
              currentModel.trigger("changeValue", {
                attributeId: attrid
              });
            }
          }
        });

        //Propagate the event uploadFile to the model
        currentModel.listenTo(value, "uploadFileDone", function mDocumentListenuploadFileDone(event, attrid, options) {
          currentModel._uploadingFile--;
          currentModel.trigger("uploadFileDone", event, attrid, options);

          if (currentModel._uploadingFile <= 0) {
            currentModel.trigger("uploadFileFinished");
          }
        });

        //Propagate the event helperSearch to the model
        currentModel.listenTo(value, "helperSearch", function mDocumentsetValuesListenHelperSearch(
          event,
          attrid,
          options,
          index
        ) {
          currentModel.trigger("helperSearch", event, attrid, options, index);
        });
        //Propagate the event helperResponse to the model
        currentModel.listenTo(value, "helperResponse", function mDocumentsetValuesListenHelperResponse(
          event,
          attrid,
          options,
          index
        ) {
          currentModel.trigger("helperResponse", event, attrid, options, index);
        });
        //Propagate the event helperResponse to the model
        currentModel.listenTo(value, "helperSelect", function mDocumentsetValuesListenHelperSelect(
          event,
          attrid,
          options,
          index
        ) {
          currentModel.trigger("helperSelect", event, attrid, options, index);
        });
        //Propagate the click on an anchor to the model
        currentModel.listenTo(value, "anchorClick", function mDocumentsetValuesListenAnchorClicked(
          event,
          attrid,
          options
        ) {
          currentModel.trigger("anchorClick", event, attrid, options);
        });
        //Propagate attributeBeforeTabSelect
        currentModel.listenTo(value, "attributeBeforeTabSelect", function mDocumentattributeBeforeTabSelect(
          event,
          attrid
        ) {
          currentModel.trigger("attributeBeforeTabSelect", event, attrid);
        });
        //Propagate attributeAfterTabSelect
        currentModel.listenTo(value, "attributeAfterTabSelect", function mDocumentattributeAfterTabSelect(
          event,
          attrid
        ) {
          currentModel.trigger("attributeAfterTabSelect", event, attrid);
        });
        currentModel.listenTo(value, "attributeTabChange", function mDocumentattributeTabChange(
          event,
          attrid,
          $el,
          data
        ) {
          currentModel.trigger("attributeTabChange", event, attrid, $el, data);
        });
      }
      return Backbone.Model.prototype.set.call(this, keyOrValues, value);
    },

    unbindLoadEvent: function mDocumentUnbindLoadEvent() {
      $(window).off("." + this.cid);
    },

    /**
     * Destroy the collection associated to the document (used in the destroy part of the view)
     *
     */
    destroySubcollection: function mDocumentdestroySubcollection() {
      if (this.get("menus") instanceof CollectionMenus) {
        this.get("menus").destroy();
      }
      if (this.get("properties") instanceof DocumentProperties) {
        this.get("properties").trigger("destroy");
      }
      if (this.get("attributes") instanceof CollectionAttributes) {
        this.get("attributes").destroy();
      }
    },

    /**
     * Inject JS in the main page before render view
     * To launch beforeRender and beforeRenderAttribute
     */
    injectCurrentDocJS: function mDocumentInjectCurrentDocJS() {
      const event = {
        injectPromise: Promise.resolve(),
        js: this.get("customJS")
      };
      this.trigger("injectCurrentSmartElementJS", event);
      return event.injectPromise;
    },

    /**
     * Inject an array of js in the current page
     *
     * @param jsToInject Array of string to inject
     * @return Promise
     */
    injectJS: function mDocumentInjectJs(jsToInject) {
      var injectPromise = Promise.resolve();
      if (!_.isArray(jsToInject)) {
        throw new Error("The js to inject must be an array of string path");
      }
      _.each(jsToInject, function injectElement(currentPath) {
        if ($('script[src="' + currentPath + '"]').length === 0) {
          injectPromise = injectPromise.then(function() {
            return new Promise(function addJs(resolve, reject) {
              load(currentPath, function addJsDone(err) {
                if (err) {
                  reject(err);
                } else {
                  resolve();
                }
              });
            });
          });
        }
      });

      return injectPromise;
    },

    /**
     * Inject an array of css path in the current page
     *
     * @param customCss
     */
    injectCSS: function mDocumentInjectCSS(customCss) {
      // add custom css style
      var $head = $("head"),
        cssLinkTemplate = _.template(
          '<link rel="stylesheet" type="text/css" ' + 'href="<%= path %>" data-injected="true">'
        );
      if (!_.isArray(customCss)) {
        throw new Error("The css to inject must be an array of string path");
      }

      // Inject new CSS
      _.each(customCss, function vDocumentInjectNewCSS(cssItem) {
        if (document.createStyleSheet) {
          document.createStyleSheet(cssItem);
        }
        $head.append(cssLinkTemplate({ path: cssItem }));
      });
    },

    /**
     * Used by backbone for the save part
     * @var backboneOptions  ((false by default) : if creation request return also unmodified values
     * @returns {{document: {attributes: *, properties : *}}}
     */
    toJSON: function mDocumenttoJSON(backboneOptions) {
      let getAllFieldValues = false;
      if (this.changed && this.changed.currentHttpMethod === "create") {
        getAllFieldValues = true;
      }
      if (backboneOptions && backboneOptions.getAllFieldValues) {
        getAllFieldValues = true;
      }
      return {
        document: {
          properties: this.getModelProperties(),
          attributes: this.getValues(getAllFieldValues !== true)
        },
        customClientData: this._customClientData
      };
    },

    /**
     * Get render option for document widget
     * @param optId
     * @returns {*}
     */
    getOption: function mDocumentGetOption(optId) {
      var renderOptions = this.get("renderOptions");
      if (renderOptions && renderOptions.document) {
        return renderOptions.document[optId];
      }
      return undefined;
    },

    _promiseCallback: function mDocument_promiseCallback() {
      var promise,
        success,
        error,
        properties = this.getServerProperties();

      promise = new Promise(function mDocument_promiseInternObject(resolve, reject) {
        success = function onSuccess(values) {
          var successArguments = values;
          if (values && successArguments["promiseArguments"]) {
            successArguments = values["promiseArguments"];
          } else {
            successArguments = arguments;
          }
          if (values && successArguments.documentProperties) {
            properties = successArguments.documentProperties;
          }
          resolve({
            documentProperties: properties,
            promiseArguments: successArguments
          });
        };
        error = function onError(values) {
          var errorArguments = values;
          if (values && errorArguments["promiseArguments"]) {
            errorArguments = values["promiseArguments"];
          } else {
            errorArguments = arguments;
          }
          if (values && errorArguments.documentProperties) {
            properties = errorArguments.documentProperties;
          }
          reject({
            documentProperties: properties,
            promiseArguments: errorArguments
          });
        };
      });

      //noinspection JSUnusedAssignment
      return {
        promise: promise,
        success: success,
        error: error
      };
    },

    /**
     * Complete the loading of the document
     * Fetch the structure and external js deps
     *
     * @private
     */
    _loadDocument: function mDocumentLoadDocument(currentModel) {
      var properties = this.getServerProperties();

      return new Promise(function mDocument_promiseLoadDocument(resolve, reject) {
        //Complete the structure after
        currentModel._completeStructure().then(
          function onGetStructureDone() {
            currentModel.injectCurrentDocJS().then(
              function mDocument_injectJSDone(/*values*/) {
                resolve({
                  documentProperties: properties,
                  promiseArguments: arguments
                });
              },
              function mDocument_injectJSFail() {
                currentModel.message = i18n.___("JS resources could not be loaded", "ddui");
                reject({
                  documentProperties: properties,
                  promiseArguments: arguments
                });
              }
            );
          },
          function mDocument_onGetStructureFail(values) {
            reject({
              documentProperties: properties,
              promiseArguments: arguments
            });
            currentModel.trigger.apply(currentModel, _.union(["dduiDocumentFail"], values.promiseArguments));
          }
        );
      });
    },

    fetchDocument: function mDocumentFetchDocument(values, options) {
      var globalCallback = this._promiseCallback(),
        documentCallback = this._promiseCallback(),
        serverProperties = this.getServerProperties(),
        changeDocument,
        currentModel = this,
        needToUnlock = {},
        beforeCloseReturn = { prevent: false },
        lockModel = null,
        nextView = false,
        security,
        previousMode,
        lockCallback = this._promiseCallback();

      changeDocument = function mDocumentfetchUserOK() {
        currentModel.trigger("displayLoading");

        //***********Lock Part*********************************************************************************

        // Verify if current document need to be unlocked before fetch another
        security = currentModel.get("properties") ? currentModel.get("properties").get("security") : null;
        previousMode = currentModel.get("renderMode");

        if (previousMode === "edit" && security && security.lock && security.lock.temporary) {
          needToUnlock = {
            initid: serverProperties.initid
          };
        }
        //Compute the next view
        nextView = values.viewId;

        if (!nextView) {
          nextView = currentModel.get("renderMode") === "edit" ? "!defaultEdition" : "!defaultConsultation";
        }

        if (
          nextView !== "!defaultConsultation" &&
          nextView !== "!coreCreation" &&
          nextView !== "!defaultCreation" &&
          currentModel.get("renderMode") !== "create"
        ) {
          //if the document is locked and the next view doesn't need the same lock delete it
          if (needToUnlock.initid && needToUnlock.initid !== values.initid) {
            lockModel = new DocumentLock({
              initid: needToUnlock.initid,
              type: "temporary"
            });
            lockModel.destroy();
          }
          // The next view needs a lock, ask for it and fetch the document after
          lockModel = new DocumentLock({
            initid: values.initid,
            viewId: nextView,
            type: "temporary"
          });
          lockModel.save({}, lockCallback);
        } else {
          if (needToUnlock) {
            if (needToUnlock.initid === values.initid) {
              // If same document "get" must be perform after unlock
              lockModel = new DocumentLock({
                initid: needToUnlock.initid,
                type: "temporary"
              });
              lockModel.destroy({
                success: function() {
                  lockCallback.success();
                },
                error: function() {
                  currentModel.trigger("showMessage", {
                    title: i18n.___("Document has been locked by someone else.", "ddui"),
                    type: "info"
                  });
                  lockCallback.success();
                }
              });

              lockCallback.success();
            } else {
              lockModel = new DocumentLock({
                initid: needToUnlock.initid,
                type: "temporary"
              });
              lockModel.destroy();
              lockCallback.success();
            }
          } else {
            lockCallback.success();
          }
        }

        lockCallback.promise.then(
          function mdocument_lockSucess() {
            //save the new options in the currentDocument for the fetch
            _.each(_.pick(values, "initid", "revision", "viewId"), function mDocument_SetNewOptions(value, key) {
              currentModel.set(key, value);
            });
            currentModel.fetch(documentCallback);
          },
          function mDocument_lockFail() {
            globalCallback.error.apply(currentModel, arguments);
          }
        );
      };

      options = options || {};
      values = values || {};

      if (_.isEmpty(this._customClientData)) {
        this.trigger("getCustomClientData");
      }

      //Register promise events
      documentCallback.promise
        .then(currentModelProperties => {
          currentModel.trigger("close", serverProperties);
          currentModel
            ._loadDocument(currentModel)
            .then(values => {
              globalCallback.success.apply(currentModelProperties, values);
            })
            .catch(values => {
              globalCallback.error.call(
                currentModelProperties,
                values && values.promiseArguments ? values.promiseArguments : values
              );
            });
        })
        .catch(values => {
          globalCallback.error.call(
            serverProperties,
            values && values.promiseArguments ? values.promiseArguments : values
          );
        });

      globalCallback.promise.then(
        function onPrepareDocumentDone(values) {
          if (_.isFunction(options.success)) {
            options.success(values);
          }
          currentModel.trigger.apply(currentModel, _.union(["dduiDocumentReady"], values.promiseArguments));
        },
        function onPrepareDocumentFail(values) {
          if (_.isFunction(options.error)) {
            options.error(values);
          }
          if (values.promiseArguments && values.promiseArguments[0].message) {
            currentModel.message = values.promiseArguments[0].message;
            currentModel.trigger.apply(
              currentModel,
              _.union(["dduiDocumentFail", currentModel], values.promiseArguments)
            );
          } else if (
            !(
              values.promiseArguments &&
              values.promiseArguments[0] &&
              values.promiseArguments[0].eventPrevented === true
            )
          ) {
            currentModel.trigger.apply(
              currentModel,
              _.union(["dduiDocumentFail", currentModel], values.promiseArguments[0])
            );
            currentModel.trigger("displayNetworkError");
          }
        }
      );

      //Init default values
      _.defaults(values, {
        revision: -1,
        viewId: "!defaultConsultation",
        initid: this.get("initid")
      });

      //Trigger (synchronous) before close event
      this.trigger("beforeClose", beforeCloseReturn, values, this._customClientData);
      const beforeClosePromise = EventPromiseUtils.getBeforeEventPromise(
        beforeCloseReturn,
        () => {
          if (options.force === true) {
            changeDocument();
          } else {
            this.trigger("displayCloseDocument", changeDocument, function mDocumentFetchUserCancel(error) {
              //Reinit properties
              currentModel.set(serverProperties);
              //Indicate success to the promise object
              globalCallback.error(error);
            });
          }
        },
        () => {
          //Reinit properties
          currentModel.set(serverProperties);
          //Indicate success to the promise object
          globalCallback.error({ eventPrevented: true });
        },
        { loadingStart: () => this.trigger("displayLoading") }
      );
      return beforeClosePromise.finally(() => globalCallback.promise);
    },

    saveDocument: function mDocumentSaveDocument(attributes, options) {
      var globalCallback = this._promiseCallback(),
        saveCallback = this._promiseCallback(),
        beforeSaveEvent = { prevent: false },
        currentModel = this,
        serverProperties = this.getServerProperties();

      options = options || {};

      if (_.isEmpty(this._customClientData)) {
        this.trigger("getCustomClientData");
      }
      this.trigger("beforeSave", beforeSaveEvent, this._customClientData);

      const beforeSavePromise = EventPromiseUtils.getBeforeEventPromise(
        beforeSaveEvent,
        () => {
          saveCallback.promise.then(
            function mDocument_saveDone() {
              currentModel._loadDocument(currentModel).then(
                function mDocument_loadDocumentDone() {
                  globalCallback.success();
                },
                function mDocument_loadDocumentFail() {
                  globalCallback.error.apply(currentModel, arguments);
                }
              );
            },
            function mDocument_saveFail() {
              globalCallback.error.apply(currentModel, arguments);
            }
          );

          if (currentModel.hasUploadingFile()) {
            this.trigger("displayLoading", {
              isSaving: true,
              text: i18n.___("Recording files in progress", "ddui")
            });
            currentModel.trigger("showMessage", {
              title: i18n.___("Waiting uploads in progress", "ddui"),
              type: "info"
            });
            currentModel.once("uploadFileFinished", function mDocumentsetValuesListenUploadUntilTheEnd(/*event*/) {
              if (!currentModel._formConfiguration) {
                currentModel.trigger("displayLoading", { isSaving: true });
              }
              currentModel.save(attributes, saveCallback);
            });
          } else {
            if (!currentModel._formConfiguration) {
              this.trigger("displayLoading", { isSaving: true });
            }
            currentModel.save(attributes, saveCallback);
          }
        },
        () => {
          globalCallback.error({ eventPrevented: true });
        }
      );

      globalCallback.promise.then(
        function onSaveSuccess(values) {
          currentModel.trigger("afterSave", serverProperties);
          currentModel.trigger("close", serverProperties);
          if (_.isFunction(options.success)) {
            options.success();
          }
          currentModel.trigger.apply(currentModel, _.union(["dduiDocumentReady"], values.promiseArguments));
        },
        function onSaveFail(values) {
          if (_.isFunction(options.error)) {
            options.error();
          }
          if (!(values.promiseArguments && values.promiseArguments[0] && values.promiseArguments[0].eventPrevented)) {
            currentModel.trigger.apply(currentModel, _.union(["dduiDocumentFail"], values.promiseArguments));
          }
        }
      );

      return beforeSavePromise.finally(() => globalCallback.promise);
    },

    deleteDocument: function mDocumentDelete(options) {
      var globalCallback = this._promiseCallback(),
        deleteCallback = this._promiseCallback(),
        beforeDeleteEvent = { prevent: false },
        currentModel = this,
        serverProperties = this.getServerProperties();

      options = options || {};

      if (_.isEmpty(this._customClientData)) {
        this.trigger("getCustomClientData");
      }
      this.trigger("beforeDelete", beforeDeleteEvent, this._customClientData);

      const preventPromise = EventPromiseUtils.getBeforeEventPromise(
        beforeDeleteEvent,
        () => {
          this.trigger("displayLoading");
          deleteCallback.promise.then(
            function mDocument_deleteDone() {
              currentModel.fetchDocument({ initid: currentModel.get("initid") }).then(
                function mDocument_afterDeleteLoadDone() {
                  globalCallback.success();
                },
                function mDocument_afterDeleteLoadFail() {
                  globalCallback.error.apply(currentModel, arguments);
                }
              );
            },
            function mDocument_deleteFail() {
              globalCallback.error.apply(currentModel, arguments);
            }
          );

          this.sync("delete", this, deleteCallback);
        },
        () => {
          globalCallback.error({ eventPrevented: true });
        }
      );

      globalCallback.promise.then(
        function onDeleteSuccess(values) {
          currentModel.trigger("afterDelete", serverProperties);
          currentModel.trigger("close", serverProperties);
          if (_.isFunction(options.success)) {
            options.success();
          }
          currentModel.trigger.apply(currentModel, _.union(["dduiDocumentReady"], values.promiseArguments));
        },
        function onDeleteFail(values) {
          if (_.isFunction(options.error)) {
            options.error();
          }
          if (!(values.promiseArguments && values.promiseArguments[0] && values.promiseArguments[0].eventPrevented)) {
            currentModel.trigger.apply(
              currentModel,
              _.union(["dduiDocumentFail", currentModel], values.promiseArguments)
            );
          }
        }
      );

      return preventPromise.finally(() => globalCallback.promise);
    },

    restoreDocument: function mDocumentRestoreDocument(options) {
      var globalCallback = this._promiseCallback(),
        restoreCallback = this._promiseCallback(),
        beforeRestoreEvent = { prevent: false },
        currentModel = this,
        serverProperties = this.getServerProperties(),
        preventPromise = Promise.resolve();

      options = options || {};

      if ("deleted" === this.get("properties").get("status")) {
        if (_.isEmpty(this._customClientData)) {
          this.trigger("getCustomClientData");
        }
        this.trigger("beforeRestore", beforeRestoreEvent, this._customClientData);

        preventPromise = EventPromiseUtils.getBeforeEventPromise(
          beforeRestoreEvent,
          () => {
            this.trigger("displayLoading", { isSaving: true });

            restoreCallback.promise.then(
              function mDocument_restoreDocument_Success() {
                currentModel._loadDocument(currentModel).then(
                  function mDocument_restoreDocument_loadSuccess() {
                    globalCallback.success();
                  },
                  function mDocument_restoreDocument_loadFail() {
                    globalCallback.error.apply(currentModel, arguments);
                  }
                );
              },
              function mDocument_restoreDocument_Fail() {
                globalCallback.error.apply(currentModel, arguments);
              }
            );

            this.get("properties").set("status", "alive");
            currentModel.save({}, restoreCallback);
          },
          () => {
            globalCallback.error({ eventPrevented: true });
          }
        );
      } else {
        globalCallback.error({
          systemError: true,
          errorMessage: "Unable to restore alive doc"
        });
      }

      globalCallback.promise.then(
        function mDocument_restoreDocument_onSuccess(values) {
          currentModel.trigger("afterRestore", serverProperties);
          currentModel.trigger("close", serverProperties);
          if (_.isFunction(options.success)) {
            options.success();
          }
          currentModel.trigger.apply(currentModel, _.union(["dduiDocumentReady"], values.promiseArguments));
        },
        function mDocument_restoreDocument_onFail(values) {
          if (_.isFunction(options.error)) {
            options.error();
          }
          if (
            !(
              values.promiseArguments &&
              values.promiseArguments[0] &&
              (values.promiseArguments[0].eventPrevented || values.promiseArguments[0].systemError)
            )
          ) {
            currentModel.trigger.apply(currentModel, _.union(["dduiDocumentFail"], values.promiseArguments));
          }
        }
      );

      return preventPromise.finally(() => globalCallback.promise);
    },

    /**
     * Get complementary data : family structure
     */
    _completeStructure: function mDocumentCompleteStructure() {
      var mStructure,
        documentModel = this,
        structurePromise = this._promiseCallback();

      var neededAttributes = this.get("renderOptions").needed;
      var visibilityAttributes = this.get("renderOptions").visibilities;
      var valueAttributes = this.get("originalValues");

      if (this._formConfiguration) {
        // Family has no attributes
        documentModel.set("attributes", LibSmartForm.getSmartFields(this._formConfiguration));
        structurePromise.success();
        return structurePromise.promise;
      }

      if (this.get("properties").get("type") === "family") {
        // Family has no attributes
        this.set("attributes", []);
        structurePromise.success();
        return structurePromise.promise;
      }

      if (!_.isUndefined(this.get("attributes"))) {
        this.set("attributes", this.get("attributes")); // to convert attributes to models
        structurePromise.success();
        return structurePromise.promise;
      }

      mStructure = new FamilyStructure({
        familyId: this.get("properties").get("family").name,
        referencedocument: {
          initid: this.get("initid"),
          viewId: this.get("viewId"),
          revision: this.get("revision")
        }
      });

      mStructure.fetch({
        success: function mDocumentCompleteStructureSuccess(structureModel, response) {
          if (
            _.isEqual(structureModel.get("referencedocument"), {
              initid: documentModel.get("initid"),
              viewId: documentModel.get("viewId"),
              revision: documentModel.get("revision")
            })
          ) {
            var attributes = flattenAttributes(attributes, response.data.family.structure);
            _.each(attributes, function mDocumentCompleteStructureSuccessEach(currentAttributeStructure) {
              if (currentAttributeStructure.id && valueAttributes[currentAttributeStructure.id]) {
                currentAttributeStructure.attributeValue = valueAttributes[currentAttributeStructure.id];
              }
              currentAttributeStructure.needed = neededAttributes[currentAttributeStructure.id] === true;
              if (currentAttributeStructure.id && visibilityAttributes[currentAttributeStructure.id]) {
                currentAttributeStructure.visibility = visibilityAttributes[currentAttributeStructure.id];
              }
            });
            documentModel.set("attributes", attributes);
            structurePromise.success();
          }
        },

        error: structurePromise.error
      });
      return structurePromise.promise;
    }
  });
});
