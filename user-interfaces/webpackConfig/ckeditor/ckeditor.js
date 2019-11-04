window.CKEDITOR_BASEPATH = "/uiAssets/externals/ckeditor/";
import $ from "jquery";

export default import("ckeditor4").then(() => {
  return import("ckeditor4/adapters/jquery");
});
