import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import { LayoutInstaller } from "@progress/kendo-layout-vue-wrapper";
import "@progress/kendo-ui/js/kendo.splitter";

import { Component, Vue } from "vue-property-decorator";

import TeServerLoad from "./TeServerLoad.vue";
import TeSupervision from "./TeSupervision.vue";
import TeUnitTransformation from "./TeUnitTransformation.vue";

Vue.use(LayoutInstaller);
Vue.use(ButtonsInstaller);

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    "te-config": () => import("./TeConfig.vue"),
    "te-server-load": TeServerLoad,
    "te-supervision": TeSupervision,
    "te-unit-transformation": TeUnitTransformation
  }
})
export default class TeManagerController extends Vue {
  public $refs!: {
    supervisionComponent: TeSupervision | any;
    serverLoadComponent: TeServerLoad | any;
    unitTransformationComponent: TeUnitTransformation | any;
  };
  public supervisionActived: boolean = false;
  public loadActived: boolean = false;
  public unitTransformationActived: boolean = false;

  public onTabActivate(e) {
    switch ($(e.item).data("id")) {
      case "supervision":
        this.supervisionActived = true;
        break;
      case "load":
        this.loadActived = true;
        break;
      case "unitTransformation":
        this.unitTransformationActived = true;
        break;
    }

    $(window).trigger("resize");
  }
}
