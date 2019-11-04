import $ from "jquery";
import "@progress/kendo-ui/js/kendo.core";
import "@progress/kendo-ui/js/kendo.notification";
import "@progress/kendo-ui/js/kendo.menu";
import "@progress/kendo-ui/js/kendo.window";
import "@progress/kendo-ui/js/kendo.autocomplete";
import "@progress/kendo-ui/js/kendo.numerictextbox";
import "@progress/kendo-ui/js/kendo.calendar";
import "@progress/kendo-ui/js/kendo.datepicker";
import "@progress/kendo-ui/js/kendo.timepicker";
import "@progress/kendo-ui/js/kendo.datetimepicker";
import "@progress/kendo-ui/js/kendo.multiselect";
import "@progress/kendo-ui/js/kendo.combobox";
import "@progress/kendo-ui/js/kendo.dropdownlist";
import "@progress/kendo-ui/js/kendo.color";
import "@progress/kendo-ui/js/kendo.colorpicker";
import "@progress/kendo-ui/js/kendo.tabstrip";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.listview";

if (!window.$) {
  window.$ = $;
}
if (!window.jQuery) {
  window.jQuery = $;
}

export default $;
