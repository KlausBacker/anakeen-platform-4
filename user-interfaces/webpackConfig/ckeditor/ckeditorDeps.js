import $ from "jquery";
import "ckeditor4";
const oldQuery = window.jQuery;
const jqueryGlobal = !!window.jQuery;
if (!jqueryGlobal) {
  window.jQuery = $;
}
import("ckeditor4/adapters/jquery").then(() => {
  if (!jqueryGlobal) {
    window.jQuery = oldQuery;
  }
});
