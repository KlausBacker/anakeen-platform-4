import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm.esm";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import TeTaskInfo from "./TeTaskInfo.vue";

import "@progress/kendo-ui/js/kendo.switch";
import { Component, Mixins } from "vue-property-decorator";

// noinspection JSUnusedGlobalSymbols
@Component({
  name: "TeUnitTransformation",
  components: {
    AnkSmartForm: () => AnkSmartForm,
    "te-task-info": TeTaskInfo,
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class TeUnitTransformation extends Mixins(AnkI18NMixin) {
  public fileToSend;
  public engine;
  public info;
  public progressMessages: string[] = [];
  public teVersion = "";
  public checkedTask: any = "";
  private progressText = "";
  private kProgress: any = null;
  private testRunning = false;
  private testStepNumber = 0;
  public tid;
  get smartFormData() {
    return {
      title: `${this.$t("AdminCenterTransformationFileManager.Unit transformation")}`,
      renderOptions: {
        types: {
          menu: {
            labelPosition: "right"
          }
        },
        fields: {
          te_unit_transformation_engine_list: {
            placeHolder: "Chose a transformation engine"
          }
        }
      },
      menu: [
        {
          beforeContent: '<div class="fa fa-share-square-o" />',
          iconUrl: "",
          id: "send",
          important: false,
          label: this.$t("AdminCenterTransformationFileManager.Send to server"),
          target: "_self",
          type: "itemMenu",
          url: "#action/teunittransformation.send"
        }
      ],
      structure: [
        {
          label: `${this.$t("AdminCenterTransformationFileManager.Unit transformation information")}`,
          name: "te_unit_transformation_unit_transformation_frame",
          type: "frame",
          content: [
            {
              label: `${this.$t("AdminCenterTransformationFileManager.Engine list")}`,
              name: "te_unit_transformation_engine_list",
              type: "text",
              autocomplete: {
                url: "/api/admin/transformationengine/engine-list/",
                outputs: {
                  te_unit_transformation_engine_list: "name"
                }
              }
            },
            {
              label: `${this.$t("AdminCenterTransformationFileManager.File")}`,
              name: "te_unit_transformation_upload_file",
              type: "file"
            }
          ]
        }
      ]
    };
  }

  public smartElementMounted() {
    // @ts-ignore
    this.$refs.teUnitTransformationFileForm.$on("smartFieldChange", (event, formProperties, field, values) => {
      if (field.id === "te_unit_transformation_engine_list") {
        this.engine = values.current.value;
      }
    });
    // @ts-ignore
    this.$refs.teUnitTransformationFileForm.$on(
      "smartFieldUploadFileDone",
      (event, smartElement, field, $el, index, options) => {
        if (field.id === "te_unit_transformation_upload_file") {
          this.fileToSend = options.file;
        }
      }
    );
    // @ts-ignore
    this.$refs.teUnitTransformationFileForm.$on("actionClick", (event, data, options) => {
      if (options.eventId === "teunittransformation.send") {
        this.progressMessages = [];
        this.checkedTask = "";
        // @ts-ignore
        this.checkStep("/api/admin/transformationengine/check/unit-transformation/0");
      }
    });
  }
  protected checkStep(url) {
    this.$http
      .put(url, { engine: this.engine, file: this.fileToSend })
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
        if (info.stepNumber && info.stepNumber > info.maxStep) {
          // @ts-ignore
          this.checkedTask = this.tid;
        }
        if (info.tid) {
          this.tid = { tid: info.tid };
        }
        if (info.failed) {
          $(".k-last", this.$el).addClass("k-state-failed");
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
}
