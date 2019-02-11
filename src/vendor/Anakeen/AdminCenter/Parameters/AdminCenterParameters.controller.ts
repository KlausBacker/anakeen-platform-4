import Vue from "vue";
import Component from "vue-class-component";
import AdminCenterGlobalParameters from "./GlobalParameters/GlobalParameters.vue";
import AdminCenterUserParameters from "./UserParameters/UserParameters.vue";
import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.window";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.grid";

@Component({
    components: {
        "admin-center-global-parameters": AdminCenterGlobalParameters,
        "admin-center-user-parameters": AdminCenterUserParameters
    }
})

export default class AdminCenterParametersController extends Vue {
  globalParameters: boolean = true;

  switchParameters() {
      this.globalParameters = !this.globalParameters;
  }
};