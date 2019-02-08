import Vue from "vue";
import Component from "vue-class-component";
import AdminCenterGlobalParameters from "./GlobalParameters/GlobalParameters.vue";
import AdminCenterUserParameters from "./UserParameters/UserParameters.vue";

@Component({
    name: "ank-admin-parameters",
    components: {
        "admin-center-global-parameters": AdminCenterGlobalParameters,
        "admin-center-user-parameters": AdminCenterUserParameters
    }
})

export class AdminCenterParametersController extends Vue {
  globalParameters: boolean = true;

  switchParameters() {
      this.globalParameters = !this.globalParameters;
  }
};