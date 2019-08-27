define([
  "underscore",
  "jquery",
  "mustache",
  "dcpDocument/i18n/documentCatalog",
  "dcpDocument/views/document/attributeTemplate"
], function wCreateDocument(_, $, Mustache, i18n, attributeTemplate) {
  "use strict";

  $.widget("dcp.dcpCreateDocument", {
    options: {
      title: "Document",
      width: "300px",
      height: "400px",
      attributeId: null,
      originDocumentModel: null,
      index: -1
    },

    _create: function wcdCreate() {
      this.element.data("dcpCreateDocument", this);
    },

    /**
     * Init a kendo window and call the global controller
     * to have a SmartElement in the window
     */
    open: function wcdOpen() {
      var wWidget = this;
      var config = this.options;
      var index = config.index;
      if (config.createLabel) {
        var documentModel = config.originDocumentModel;
        var attributeModel = documentModel.get("attributes").get(config.attributeId);
        var currentValue;
        var $createDocument;

        if (index >= 0) {
          currentValue = attributeModel.getValue()[index];
        } else {
          currentValue = attributeModel.getValue();
        }

        this.$dialog = $('<div class="dcpDocid-create-window"/>');
        $("body").append(this.$dialog);
        var renderTitle = Mustache.render(config.windowTitle || "", currentValue);
        var dw = this.$dialog
          .dcpWindow({
            title: renderTitle,
            width: config.windowWidth,
            height: config.windowHeight,
            close: function wcdClose(event) {
              wWidget.closeDialog(event, true);
            }
          })
          .data("dcpWindow");
        dw.kendoWindow().center();
        dw.open();

        $createDocument = dw.currentWidget;
        $createDocument.on("documentcreate", function wAttributeCreateDocumentCreation() {
          dw.currentWidget.find("> iframe").addClass("k-content-frame");
        });

        //Use global controller to add the new Smart Element

        if (currentValue.value) {
          window.ank.smartElement.globalController.addSmartElement($createDocument, {
            initid: currentValue.value,
            viewId: "!defaultEdition"
          });
        } else {
          window.ank.smartElement.globalController.addSmartElement($createDocument, {
            initid: config.familyName || attributeModel.get("typeFormat"),
            viewId: "!defaultCreation"
          });
        }

        const scopedController = window.ank.smartElement.globalController.getScopedController($createDocument);

        this.scopedController = scopedController;

        scopedController.addEventListener("ready", { name: "ddui:create:ready" }, (event, smartElementInfo) => {
          const isReadyPrevented = wWidget.proxyTrigger(event, "ready", {});
          if (!isReadyPrevented) {
            if (smartElementInfo.viewId === "!defaultCreation") {
              // Set form values
              const isBeforePrevented = wWidget.proxyTrigger(event, "beforeSetFormValues", {
                getFormValues: function wcdCustomGetFormValues() {
                  return config.formValues;
                },
                setFormValues: function wcdCustomSetFormValues(customFormValues) {
                  wWidget.setFormValue(customFormValues, scopedController);
                }
              });

              if (!isBeforePrevented) {
                wWidget.setFormValue(config.formValues, scopedController);
              }
            }
            const menuCreateAndClose = scopedController.getMenu("createAndClose");
            if (menuCreateAndClose) {
              menuCreateAndClose.hide();
            }
            const menuSaveAndClose = scopedController.getMenu("saveAndClose");
            if (menuSaveAndClose) {
              menuSaveAndClose.hide();
            }
            const menuCreate = scopedController.getMenu("create");
            if (menuCreate) {
              menuCreate.setLabel(Mustache.render(config.createLabel, attributeModel.attributes));
            }
            const menuSave = scopedController.getMenu("save");
            if (menuSave) {
              menuSave.setLabel(Mustache.render(config.updateLabel, attributeModel.attributes));
            }
          }
        });

        scopedController.addEventListener(
          "actionClick",
          {
            name: "ddui:create:close"
          },
          function wAttributeCreateDocumentbeforeClose(event, documentObject, options) {
            if (options.eventId === "document.close") {
              event.preventDefault();
              wWidget.closeDialog(event, true);
            }
          }
        );

        scopedController.addEventListener(
          "afterSave",
          {
            name: "ddui:create:record"
          },
          function wAttributeCreateDocumentRecord(event, currentDocumentObject) {
            var newOneValue = {
              value: currentDocumentObject.initid,
              displayValue: currentDocumentObject.title,
              familyRelation: currentDocumentObject.family.name,
              icon: currentDocumentObject.icon
            };
            var newValue;
            var isPrevented;
            isPrevented = wWidget.proxyTrigger(event, "beforeSetTargetValue", {
              attributeValue: newOneValue
            });
            if (isPrevented) {
              return;
            }

            if (attributeModel.hasMultipleOption()) {
              newValue = attributeModel.getValue();
              if (index >= 0) {
                newValue = newValue[index];
              }
              if (_.isArray(newValue)) {
                newValue = _.clone(newValue); // need to clone to trigger backbone change
                newValue.push(newOneValue);
              } else {
                newValue = [newOneValue];
              }
            } else {
              newValue = newOneValue;
            }
            attributeModel.setValue(newValue, index);

            isPrevented = wWidget.proxyTrigger(event, "beforeClose", {
              attributeValue: newValue
            });
            if (!isPrevented) {
              dw.close();
            }
          }
        );
      }
    },
    /**
     * Set the value to the sub form
     *
     * @param formValues
     * @param controllerSubDoc
     */
    setFormValue: function wcdSetFormValue(formValues, controllerSubDoc) {
      var documentModel = this.options.originDocumentModel;
      var tplData = attributeTemplate.getTemplateModelInfo(documentModel);
      // Set form values
      _.each(formValues, function wDocidFormValues(attrValue, attrId) {
        var rValue;
        if (_.isObject(attrValue)) {
          controllerSubDoc.setValue(attrId, attrValue);
        } else {
          var isAttr = new RegExp("^{{attributes.(.+).attributeValue}}$").exec(attrValue);

          if (isAttr && documentModel.get("attributes").get(isAttr[1])) {
            rValue = documentModel
              .get("attributes")
              .get(isAttr[1])
              .getValue();
            controllerSubDoc.setValue(attrId, rValue);
          } else {
            rValue = Mustache.render(attrValue, tplData);
            controllerSubDoc.setValue(attrId, {
              value: rValue,
              displayValue: rValue
            });
          }
        }
      });
    },

    /**
     * Dispath event to the main backbone model
     *
     * @param event
     * @param triggerName
     * @param options
     * @returns {boolean}
     */
    proxyTrigger: function wcdTrigger(event, triggerName, options) {
      var listener, args;
      if (this.options[triggerName]) {
        listener = this.options[triggerName];
        args = [event, options];
      } else if (this.options.listener) {
        listener = this.options.listener;
        args = [event, triggerName, options];
      }
      if (listener) {
        options.index = this.options.index;
        options.dialogWindow = this.$dialog;
        listener.apply(this.scopedController, args);
        return event.prevent === true;
      }
      return false;
    },

    confirmClose: function wcdConfirmClose() {
      var targetProperties = this.scopedController.getProperties();
      return new Promise(function wsdAskConfirmation(resolve, reject) {
        var confirmWindow = $("body").dcpConfirm({
          title: Mustache.render(i18n.___('Confirm close form "{{title}}"', "ddui"), targetProperties),
          width: "510px",
          height: "150px",
          maxWidth: $(window).width(),
          messages: {
            okMessage: i18n.___("Abord modification", "ddui"),
            cancelMessage: i18n.___("Stay on the form", "ddui"),
            htmlMessage: i18n.___("The form has been modified without saving", "ddui"),
            textMessage: ""
          },
          confirm: resolve,
          cancel: reject
        });
        confirmWindow.data("dcpWindow").open();
      });
    },

    closeDialog: function wcdcloseDialog(event, askConfirm) {
      var wWidget = this;
      var isPrevented = false;
      var kDialog = this.$dialog.data("dcpWindow");
      if (this.scopedController) {
        var targetProperties = this.scopedController.getProperties();
        isPrevented = this.proxyTrigger(event, "beforeClose", {});

        if (!isPrevented && askConfirm && targetProperties && targetProperties.isModified) {
          wWidget.confirmClose().then(function wcdConfirmClose() {
            wWidget.closeDialog(event, false);
          });
          isPrevented = true;
        }
      }
      if (!isPrevented) {
        isPrevented = wWidget.proxyTrigger(event, "beforeDestroy", {});
        if (!isPrevented) {
          kDialog.destroy();
        }
      } else {
        event.preventDefault();
      }
    }
  });
});
