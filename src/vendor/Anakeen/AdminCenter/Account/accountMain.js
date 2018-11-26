import Vue from "vue";
import "@progress/kendo-ui/js/kendo.treeview";
import "@progress/kendo-ui/js/kendo.splitter";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.toolbar";
import { TreeViewInstaller } from "@progress/kendo-treeview-vue-wrapper";
import { GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";

import AdminCenterUserAndGroup from "./AdminCenterAccount";
import installVuePlugin from "../Vue/installVuePlugin";

Vue.use(ButtonsInstaller);
Vue.use(GridInstaller);
Vue.use(TreeViewInstaller);

installVuePlugin(Vue, "ank-admin-account", AdminCenterUserAndGroup);
