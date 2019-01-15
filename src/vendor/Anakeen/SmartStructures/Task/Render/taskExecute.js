window.dcp.document.documentController(
  "addEventListener",
  "actionClick",
  {
    name: "task.executeNow",
    documentCheck: documentObject => {
      return documentObject.family.name === "TASK";
    }
  },
  (event, documentObject, data) => {
    if (data.eventId === "task") {
      if (data.options.length > 0 && data.options[0] === "executeNow") {
        let $waiting = $(
          '<div class="fa-3x task-waiting">' +
            '  <i class="fa fa-cog fa-spin"></i>' +
            "</div>"
        );

        let wWaiting = $waiting
          .kendoWindow({
            modal: true,
            title: "Executing task ..."
          })
          .data("kendoWindow");
        wWaiting.center().open();
        $.ajax({
          dataType: "json",
          method: "PUT",
          url: "/api/v2/admin/task/" + documentObject.id
        })
          .done(response => {
            let data = response.data;
            window.dcp.document.documentController("fetchDocument", {
              initid: data.properties.initid,
              revision: data.properties.revision
            });
            window.dcp.document.documentController("showMessage", {
              type: "success",
              message: data.message
            });
          })
          .fail(response => {
            let errorMsg = "";
            if (response.responseJSON) {
              errorMsg =
                response.responseJSON.message || response.responseJSON.error;
            }
            if (!errorMsg) {
              errorMsg = response.responseText;
            }

            window.dcp.document.documentController("showMessage", {
              message: errorMsg,
              type: "error"
            });
          })
          .always(() => {
            wWaiting.destroy();
          });
      }
    }
  }
);
