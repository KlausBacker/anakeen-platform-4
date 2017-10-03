


window.dcp.document.documentController("addEventListener",
  "change",
  {
      "name": "BA_PROSPECT::pr_mail",
      "documentCheck": function(documentObject) {
          return documentObject.family.name === "BA_PROSPECT"
      },
      "attributeCheck" : function isTitle(attribute) {
          if (attribute.id === "pr_mail") {
              return true;
          }
      }
  },
  function changeDisplayError(event, documentObject, attributeObject, values) {
      var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

      if (values.current.value && !re.test(values.current.value)) {
          $(this).documentController("setAttributeErrorMessage", attributeObject.id, "Adresse de courriel incorrecte");
      } else {
          $(this).documentController("cleanAttributeErrorMessage", attributeObject.id);
      }
  }
);


window.dcp.document.documentController("addEventListener",
  "change",
  {
      "name": "BA_PROSPECT::pr_postalcode",
      "documentCheck": function(documentObject) {
          return documentObject.family.name === "BA_PROSPECT"
      },
      "attributeCheck" : function isTitle(attribute) {
          if (attribute.id === "pr_postalcode") {
              return true;
          }
      }
  },
  function changeDisplayError(event, documentObject, attributeObject, values) {
      var re = /^[0-9]{5}$/;

      if (values.current.value && !re.test(values.current.value)) {
          $(this).documentController("setAttributeErrorMessage", attributeObject.id, "Code postal incorrect");
      } else {
          $(this).documentController("cleanAttributeErrorMessage", attributeObject.id);
      }
  }
);


window.dcp.document.documentController("addEventListener",
  "afterSave",
  {
      "name": "BA_PROSPECT::afterSave",
      "documentCheck": function (document) {
          return document.family.name === "BA_PROSPECT"
      }
  },
  function (event, currentDocumentObject, oldDocObject) {
      console.log("PROSPECT AFTER SAVE", currentDocumentObject);

      if (currentDocumentObject.viewId === "complete" || oldDocObject.viewId === "!defaultCreation") {
          $(this).documentController("fetchDocument", {
              initid: currentDocumentObject.initid,
              viewId: "ack"
          });
      }
  }
);