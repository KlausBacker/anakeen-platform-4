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
  });
});