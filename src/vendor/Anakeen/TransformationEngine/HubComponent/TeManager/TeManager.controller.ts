import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import { LayoutInstaller } from "@progress/kendo-layout-vue-wrapper";

import { Component, Vue } from "vue-property-decorator";

import TeServerLoad from "./TeServerLoad.vue";
import TeSupervision from "./TeSupervision.vue";

Vue.use(LayoutInstaller);
Vue.use(ButtonsInstaller);

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    "te-config": () => new Promise((resolve) => {
        import("./TeConfig.vue").then((component) => {
          resolve(component.default);
        });
    }),
    "te-server-load": () => new Promise((resolve) => {
      import("./TeServerLoad.vue").then((component) => {
        resolve(component.default);
      });
    }),
    "te-supervision": () => new Promise((resolve) => {
      import("./TeSupervision.vue").then((component) => {
        resolve(component.default);
      });
    }),
  }
})
export default class TeManagerController extends Vue {
  public $refs!: {
    supervisionComponent: TeSupervision | any;
    serverLoadComponent: TeServerLoad | any;
  };
  public supervisionActived: boolean = false;
  public loadActived: boolean = false;
  public onTabActivate(e) {
    switch ($(e.item).data("id")) {
      case "supervision":
        this.supervisionActived = true;
        break;
      case "load":
        this.loadActived = true;
        break;
    }

    $(window).trigger("resize");
  }
}
