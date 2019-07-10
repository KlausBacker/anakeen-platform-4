import "./testRender.scss";

export default function(scopedController) {
  scopedController.addEventListener("ready", event => {
    console.log(scopedController);
    console.log("Youhouhouy !!!!!!!!");
    const docid = scopedController.getValue("tst_docname").value;
    const viewId =
      scopedController.getValue("tst_docviewid").value ||
      "!defaultConsultation";
    window.ank.smartElement.globalController.addSmartElement(
      event.target.find(".test-document"),
      {
        initid: docid,
        viewId,
        revision: -1
      }
    );
  });

  scopedController.addEventListener(
    "actionClick",
    (event, smartElementProps, data) => {
      if (data.eventId === "tst") {
        if (data.options.length > 0 && data.options[0] === "openWindow") {
          const testController = window.ank.smartElement.globalController.scope(
            event.target.find(".test-document")
          );
          if (testController) {
            window.open(testController.getProperties().url);
          }
        }
      }
    }
  );

  scopedController.addEventListener("beforeSave", (...args) => {
    console.log(...args);
  })
}
