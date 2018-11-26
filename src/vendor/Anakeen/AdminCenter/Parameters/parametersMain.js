import Vue from "vue";
import AdminParameters from "./AdminCenterParameters";
import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.window";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.grid";

import installVuePlugin from "../Vue/installVuePlugin";

installVuePlugin(Vue, "ank-admin-parameters", AdminParameters);
