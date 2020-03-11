window.CKEDITOR_BASEPATH = "/uiAssets/externals/ckeditor/";

import $ from "jquery";
const oldQuery = window.jQuery;
const jqueryGlobal = !!window.jQuery;
if (!jqueryGlobal) {
  window.jQuery = $;
}
export default import("ckeditor4"  /* webpackChunkName: "ckeditor4" */).then(() => {
  import("ckeditor4/adapters/jquery"  /* webpackChunkName: "ckeditor4jQuery" */).then(() => {
    if (!jqueryGlobal) {
      window.jQuery = oldQuery;
    }
    return new Promise((resolve, reject) => {
      if (window.CKEDITOR) {
        return resolve();
      }
      //wait if ckeditor is set
      var waitCKEDITOR = setInterval(function() {
        if (window.CKEDITOR) {
          clearInterval(waitCKEDITOR);
          resolve();
        }
      }, 100);
    });
  });
});