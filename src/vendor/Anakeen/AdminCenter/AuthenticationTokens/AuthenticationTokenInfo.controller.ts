import { LayoutInstaller } from "@progress/kendo-layout-vue-wrapper";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.splitter";
import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.window";
import IAuthenticationToken from "./IAuthenticationToken";

import { Component, Prop, Vue } from "vue-property-decorator";

Vue.use(LayoutInstaller);

// noinspection JSUnusedGlobalSymbols
@Component({
  name: "ank-token-info"
})
export default class AuthenticationTokenInfoController extends Vue {
  @Prop() public T1: string;

  @Prop() public info: IAuthenticationToken;
}
