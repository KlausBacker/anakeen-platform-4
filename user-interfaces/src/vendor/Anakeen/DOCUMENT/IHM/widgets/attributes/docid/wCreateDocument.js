define([
  "underscore",
  "jquery",
  "mustache",
  "dcpDocument/i18n/documentCatalog",
  "dcpDocument/views/document/attributeTemplate",
  "dcpDocument/document"
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

    open: function wcdOpen() {
      var wWidget = this;
      var config = this.options;
      var index = config.index;
      if (config.createLabel) {
        var documentModel = config.originDocumentModel;
        var attributeModel = documentModel
          .get("attributes")
          .get(config.attributeId);
        var currentValue;
        var url = "about:blank";
        var $createDocument;

        if (index >= 0) {
          currentValue = attributeModel.getValue()[index];
        } else {
          currentValue = attributeModel.getValue();
        }

        this.$dialog = $('<div class="dcpDocid-create-window"/>');
        $("body").append(this.$dialog);
        var renderTitle = Mustache.render(
          config.windowTitle || "",
          currentValue
        );
        var dw = this.$dialog
          .dcpWindow({
            title: renderTitle,
            width: config.windowWidth,
            height: config.windowHeight,
            content: url,
            iframe: true,
            close: function wcdClose(event) {
              wWidget.closeDialog(event, true);
            }
          })
          .data("dcpWindow");
        dw.kendoWindow().center();
        dw.open();

        $createDocument = dw.currentWidget;
        $createDocument.on(
          "documentcreate",
          function wAttributeCreateDocumentCreation() {
            dw.currentWidget.find("> iframe").addClass("k-content-frame");
          }
        );

        if (currentValue.value) {
          $createDocument.document({
            initid: currentValue.value,
            viewId: "!defaultEdition",
            withoutResize: true
          });
        } else {
          $createDocument.document({
            initid: config.familyName || attributeModel.get("typeFormat"),
            viewId: "!defaultCreation",
            withoutResize: true
          });
        }

        $createDocument.on("documentloaded", function() {
          $(this).document(
            "addEventListener",
            "ready",
            { name: "ddui:create:ready" },
            function wDocidCreateDocumentReady(event, documentInfo) {
              var wOrigin = this;
              var isPrevented;
              var menuItem;
              wWidget.$document = this;

              isPrevented = wWidget.proxyTrigger(event, "ready", {});
              if (!isPrevented) {
                if (documentInfo.viewId === "!defaultCreation") {
                  // Set form values
                  isPrevented = wWidget.proxyTrigger(
                    event,
                    "beforeSetFormValues",
                    {
                      getFormValues: function wcdCustomGetFormValues() {
                        return config.formValues;
                      },
                      setFormValues: function wcdCustomSetFormValues(
                        customFormValues
                      ) {
                        wWidget.setFormValue(customFormValues, wOrigin);
                      }
                    }
                  );

                  if (!isPrevented) {
                    wWidget.setFormValue(config.formValues, this);
                  }
                }
                menuItem = this.documentController("getMenu", "createAndClose");
                if (menuItem) {
                  menuItem.hide();
                }
                menuItem = this.documentController("getMenu", "saveAndClose");
                if (menuItem) {
                  menuItem.hide();
                }
                menuItem = this.documentController("getMenu", "create");
                if (menuItem) {
                  menuItem.setLabel(
                    Mustache.render(
                      config.createLabel,
                      attributeModel.attributes
                    )
                  );
                }
                menuItem = this.documentController("getMenu", "save");
                if (menuItem) {
                  menuItem.setLabel(
                    Mustache.render(
                      config.updateLabel,
                      attributeModel.attributes
                    )
                  );
                }
              }
            }
          );

          $(this).document(
            "addEventListener",
            "actionClick",
            {
              name: "ddui:create:close"
            },
            function wAttributeCreateDocumentbeforeClose(
              event,
              documentObject,
              options
            ) {
              if (options.eventId === "document.close") {
                event.preventDefault();
                wWidget.closeDialog(event, true);
              }
            }
          );

          $(this).document(
            "addEventListener",
            "afterSave",
            {
              name: "ddui:create:record"
            },
            function wAttributeCreateDocumentRecord(
              event,
              currentDocumentObject
            ) {
              var newOneValue = {
                value: currentDocumentObject.initid,
                displayValue: currentDocumentObject.title,
                familyRelation: currentDocumentObject.family.name,
                icon: currentDocumentObject.icon
              };
              var newValue;
              var isPrevented;
              isPrevented = wWidget.proxyTrigger(
                event,
                "beforeSetTargetValue",
                {
                  attributeValue: newOneValue
                }
              );
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
        });
      }
    },
    setFormValue: function wcdSetFormValue(formValues, $subDoc) {
      var documentModel = this.options.originDocumentModel;
      var tplData = attributeTemplate.getTemplateModelInfo(documentModel);
      // Set form values
      _.each(formValues, function wDocidFormValues(attrValue, attrId) {
        var rValue;
        if (_.isObject(attrValue)) {
          $subDoc.documentController("setValue", attrId, attrValue);
        } else {
          var isAttr = new RegExp("^{{attributes.(.+).attributeValue}}$").exec(
            attrValue
          );

          if (isAttr && documentModel.get("attributes").get(isAttr[1])) {
            rValue = documentModel
              .get("attributes")
              .get(isAttr[1])
              .getValue();
            $subDoc.documentController("setValue", attrId, rValue);
          } else {
            rValue = Mustache.render(attrValue, tplData);
            $subDoc.documentController("setValue", attrId, {
              value: rValue,
              displayValue: rValue
            });
          }
        }
      });
    },

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
        listener.apply(this.$document, args);
        return event.prevent === true;
      }
      return false;
    },

    confirmClose: function wcdConfirmClose() {
      var targetProperties = this.$document.documentController("getProperties");
      return new Promise(function wsdAskConfirmation(resolve, reject) {
        var confirmWindow = $("body").dcpConfirm({
          title: Mustache.render(
            i18n.___('Confirm close form "{{title}}"', "ddui"),
            targetProperties
          ),
          width: "510px",
          height: "150px",
          maxWidth: $(window).width(),
          messages: {
            okMessage: i18n.___("Abord modification", "ddui"),
            cancelMessage: i18n.___("Stay on the form", "ddui"),
            htmlMessage: i18n.___(
              "The form has been modified without saving",
              "ddui"
            ),
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
      if (this.$document) {
        var targetProperties = this.$document.documentController(
          "getProperties"
        );
        isPrevented = this.proxyTrigger(event, "beforeClose", {});

        if (
          !isPrevented &&
          askConfirm &&
          targetProperties &&
          targetProperties.isModified
        ) {
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
