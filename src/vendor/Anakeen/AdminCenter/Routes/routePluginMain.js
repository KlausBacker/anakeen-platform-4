import Vue from "vue";

import RoutePlugin from "./RoutePlugin";

import "@progress/kendo-ui/js/kendo.tabstrip";
import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.dialog";
import installVuePlugin from "../Vue/installVuePlugin";

installVuePlugin(Vue, "ank-admin-routes", RoutePlugin);
