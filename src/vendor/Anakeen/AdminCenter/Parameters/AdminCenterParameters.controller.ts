import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.window";
import Vue from "vue";
import Component from "vue-class-component";
import AdminCenterGlobalParameters from "./GlobalParameters/GlobalParameters.vue";
import AdminCenterUserParameters from "./UserParameters/UserParameters.vue";

@Component({
  components: {
    "admin-center-global-parameters": AdminCenterGlobalParameters,
    "admin-center-user-parameters": AdminCenterUserParameters
  }
})
export default class AdminCenterParametersController extends Vue {
  public globalParameters: boolean = true;

  public switchParameters() {
    this.globalParameters = !this.globalParameters;
  }
}
