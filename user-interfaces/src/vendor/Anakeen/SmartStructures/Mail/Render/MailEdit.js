import "./MailEdit.scss";
import $ from "jquery";

window.ank.smartElement.globalController.registerFunction("mailEdit", async scopedController => {
  const _ = await import("lodash");

  scopedController.addEventListener(
    "actionClick",
    {
      name: "sendmail"
    },
    function sendDocumentMail(event, documentObject, options) {
      //identify the good click
      if (options.eventId === "sendmail") {
        const values = scopedController.getValues();
        const targetDocument = window.dcp.viewData.customClientData.targetDocument;
        const url = `/api/v2/smart-elements/${targetDocument}/send/`;
        const $menu = $(options.target).closest(".dcpDocument__menu");

        let sendValues = {};

        _.each(values, function(attrValue, index) {
          if (_.isArray(attrValue)) {
            sendValues[index] = _.map(attrValue, function(singleValue) {
              return singleValue.value;
            });
          } else if (!_.isEmpty(attrValue.value)) {
            sendValues[index] = attrValue.value;
          }
        });

        const menuSend = scopedController.getMenu("sendmail");
        let $menuSend = $menu.find('[data-menu-id="sendmail"]');
        if (menuSend) {
          menuSend.disable();
          // Need research because DOM change
          $menuSend = $menu.find('[data-menu-id="sendmail"]');
          $(".fa", $menuSend).addClass("fa-spin");
        }

        $.ajax({
          type: "POST",
          url: url,
          dataType: "json",
          contentType: "application/json; charset=utf-8",
          data: JSON.stringify(sendValues)
        })
          .then(function(data) {
            let messages = data.messages;

            menuSend.setLabel(data.data.statusText);
            _.each(messages, function(message) {
              if (message.type !== "notice") {
                message.message = message.contentText;
                scopedController.showMessage(message);
              }
            });
            // Closes dialog in 3 seconds

            window.setTimeout(function() {
              const P$ = window.parent.$;
              const $iframes = P$(window.parent.document.body).find("iframe");
              $iframes.each(function() {
                if (this.contentWindow === window) {
                  menuSend.setLabel(data.data.closingText.replace("%d", 3));
                  $menuSend = $menu.find('[data-menu-id="sendmail"]');
                  $(".fa", $menuSend)
                    .addClass("fa-spin fa-circle-o-notch ")
                    .removeClass("fa-send");
                  window.setTimeout(() => {
                    const $dialog = P$(this).closest(".dialog-window");
                    if ($dialog.length > 0) {
                      const kDialog = $dialog.data("kendoWindow");
                      if (kDialog) {
                        kDialog.destroy();
                      }
                    }
                  }, 3000);
                }
              });
            }, 1000);
          })
          .fail(function(response) {
            let error = "Send error";
            if (response.responseJSON) {
              if (response.responseJSON.userMessage) {
                error = response.responseJSON.userMessage;
              } else if (response.responseJSON.exceptionMessage) {
                error = response.responseJSON.exceptionMessage;
              } else if (response.responseJSON.messages) {
                error = response.responseJSON.messages[0].contentText;
              }
            } else {
              error = response.responseText;
            }

            scopedController.showMessage({ type: "error", message: error });

            menuSend.enable();
          })
          .always(function() {
            $(".fa-spin", $menuSend).removeClass("fa-spin");
          });
      }
    }
  );
});
