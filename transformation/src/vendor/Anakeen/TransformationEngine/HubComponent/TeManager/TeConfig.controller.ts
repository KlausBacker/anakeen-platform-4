import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import { InputsInstaller } from "@progress/kendo-inputs-vue-wrapper";
import { LayoutInstaller } from "@progress/kendo-layout-vue-wrapper";

import "@progress/kendo-ui/js/kendo.switch";
import { Component, Vue } from "vue-property-decorator";
import { ITeConfig } from "./ITeConfig";

Vue.use(InputsInstaller);
Vue.use(LayoutInstaller);
Vue.use(ButtonsInstaller);

// noinspection JSUnusedGlobalSymbols
@Component({
  name: "TeConfig"
})
export default class TeConfig extends Vue {
  public info: ITeConfig = {
    TE_ACTIVATE: false,
    TE_HOST: "",
    TE_PORT: 0,
    TE_TIMEOUT: 5,
    TE_URLINDEX: ""
  };
  public progressMessages: string[] = [];
  public teVersion: string = "";
  private progressText: string = "";
  private kProgress: any = null;
  private testRunning: boolean = false;
  private testStepNumber: number = 0;

  public recordConfig() {
    this.$http.put("/api/admin/transformationengine/config/", this.info).then(response => {
      this.info = response.data.data;
    });
  }

  public checkConfig() {
    this.progressMessages = [];
    this.testRunning = true;
    this.checkStep("/api/admin/transformationengine/check/0");
  }

  public mounted() {
    this.getInfo();
  }

  protected checkStep(url) {
    this.$http
      .put(url, this.info)
      .then(response => {
        const info = response.data.data;

        if (!this.kProgress) {
          const $progress = $(this.$refs.progressBar);
          this.kProgress = $progress
            .kendoProgressBar({
              chunkCount: info.maxStep,
              max: info.maxStep,
              min: 1,
              type: "chunk",
              value: 0
            })
            .data("kendoProgressBar");
        }
        this.kProgress.value(info.stepNumber);
        this.testStepNumber = info.stepNumber;
        this.kProgress.element.find(".k-item").removeClass("k-state-failed");
        this.kProgress.element.find(".k-item").removeClass("k-state-running");
        $(this.kProgress.element.find(".k-item").get(info.stepNumber - 1)).addClass("k-state-running");

        if (info.message) {
          this.progressMessages.push(info.message);
        }
        if (info.version) {
          this.teVersion = info.version;
        }

        if (info.progressText) {
          this.progressText = info.progressText;
        }
        if (info.nextStepUrl) {
          this.checkStep(info.nextStepUrl);
        } else {
          this.testRunning = false;
        }
      })
      .catch(response => {
        const info = response.response.data;
        this.testRunning = false;

        this.kProgress.element.find(".k-item").removeClass("k-state-running");
        $(this.kProgress.element.find(".k-item").get(this.testStepNumber - 1)).addClass("k-state-failed");
        this.progressMessages.push(info.message);
      });
  }

  protected getInfo() {
    this.$http.get("/api/admin/transformationengine/config/").then(response => {
      this.info = response.data.data;
    });
  }
}
