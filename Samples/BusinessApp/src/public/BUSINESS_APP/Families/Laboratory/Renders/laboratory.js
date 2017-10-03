window.dcp.document.documentController("addEventListener",
  "actionClick",
  {
      "name": "gridstate",
      "documentCheck": function (document) {
          return document.family.name === "CCFD_GEP_SOUTIEN_CIRLONG"
      }
  },

  function displayCcfdAction(event, documentObject, options) {
      // rewrite url when document is in single page
      if (options.eventId === "ccfd" && options.options[0] === "action") {
          var actionId = options.options[1];
          var $a = $($(options.target).find("a"));
          var url="?app=CCFD&action=" + actionId;

          if (options.options[2] === "state") {
              url+="&state="+documentObject.state.reference;
          }

          $a.data("url", url);

          // reapply selection but with another url
          if (window.top === window) {
              $(options.target).closest("ul.k-menu").data("kendoMenu").trigger("select", {item: options.target});
          }

      }
  }
);