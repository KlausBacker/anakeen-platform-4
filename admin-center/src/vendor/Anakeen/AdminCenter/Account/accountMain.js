import Vue from 'vue';
import VueCustomElement from 'vue-custom-element';
import "@progress/kendo-ui/js/kendo.treeview";
import "@progress/kendo-ui/js/kendo.splitter";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.toolbar";
import { TreeViewInstaller } from '@progress/kendo-treeview-vue-wrapper';
import { GridInstaller } from '@progress/kendo-grid-vue-wrapper';
import { ButtonsInstaller } from '@progress/kendo-buttons-vue-wrapper';

import AdminCenterUserAndGroup from './AdminCenterAccount';
import AnkAxios from "../Components/utils/axios";

Vue.use(VueCustomElement);
Vue.use(ButtonsInstaller);
Vue.use(GridInstaller);
Vue.use(TreeViewInstaller);
Vue.use(AnkAxios);
Vue.jQuery = Vue.jquery = Vue.prototype.$ = kendo.jQuery;
Vue.kendo = Vue.prototype.$kendo = kendo;

Vue.customElement('ank-admin-account', AdminCenterUserAndGroup);