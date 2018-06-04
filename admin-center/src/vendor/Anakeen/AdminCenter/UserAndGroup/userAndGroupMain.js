import Vue from 'vue';
import VueCustomElement from 'vue-custom-element';
import "@progress/kendo-ui/js/kendo.treeview";
import "@progress/kendo-ui/js/kendo.splitter";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.toolbar";
import { TreeViewInstaller } from '@progress/kendo-treeview-vue-wrapper';
import { GridInstaller } from '@progress/kendo-grid-vue-wrapper'
import { ButtonsInstaller } from '@progress/kendo-buttons-vue-wrapper';

import AdminCenterUserAndGroup from './AdminCenterUserAndGroup.vue';

Vue.use(VueCustomElement);
Vue.use(ButtonsInstaller);
Vue.use(GridInstaller);
Vue.use(TreeViewInstaller);

Vue.customElement('ank-admin-user-group', AdminCenterUserAndGroup);