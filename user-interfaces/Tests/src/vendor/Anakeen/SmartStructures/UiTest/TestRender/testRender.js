import "./testRender.scss";
import ankSmartController from "@anakeen/user-interfaces/components/lib/AnakeenController.esm";

ankSmartController.registerFunction("structureTestRender", scopedController => {
  let insideControllerid;
  scopedController.addEventListener("ready", event => {
    const docid = scopedController.getValue("tst_docname").value;
    const viewId = scopedController.getValue("tst_docviewid").value || "!defaultConsultation";
    insideControllerid = ankSmartController.addSmartElement(event.target.find(".test-document"), {
      initid: docid,
      viewId,
      revision: -1
    });
  });

  scopedController.addEventListener("actionClick", (event, smartElementProps, data) => {
    if (data.eventId === "tst") {
      if (data.options.length > 0 && data.options[0] === "openWindow") {
        const insideController = ankSmartController.getScopedController(insideControllerid);
        window.open(insideController.getProperties().pageUrl);
      }
    }
  });
});
