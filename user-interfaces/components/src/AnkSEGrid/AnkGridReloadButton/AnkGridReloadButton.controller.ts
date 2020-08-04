import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.tooltip";
import $ from "jquery";
import Vue from "vue";
import VueI18n from "vue-i18n";
import { Component, Prop, Watch } from "vue-property-decorator";

@Component({
  name: "ank-se-grid-reload-button"
})
export default class AnkGridReloadButtonController extends Vue {
  @Prop({
    default: "",
    type: String
  })
  public iconClass;
  @Prop({
    default: null,
    type: Object
  })
  public gridComponent;

  public button = null;

  public get translations(): { [key: string]: VueI18n.TranslateResult } {
    return {
      tooltip: this.$t("gridReloadButton.Tooltip")
    };
  }

  public mounted(): void {
    const options = this.getButtonOptions();
    this.button = $(this.$refs.reloadButton)
      .kendoButton(options)
      .data("kendoButton");
    this.button.bind("click", () => {
      $(this.$el).addClass("k-state-reload-active");
      this.gridComponent.refreshGrid(true).then(() => {
        $(this.$el).removeClass("k-state-reload-active");
      });
    });
    $(this.$refs.reloadButton).kendoTooltip({
      width: 120,
      position: "top",
      autoHide: true,
      showOn: "mouseenter",
      content: this.translations.tooltip
    });
  }

  private getButtonOptions(): { [key: string]: string | boolean } {
    const options = {
      enable: true,
      icon: "",
      iconClass: "",
      imageUrl: "",
      spriteCssClass: ""
    };
    if (this.iconClass) {
      options.iconClass = this.iconClass;
    } else {
      options.icon = "reload";
    }
    return options;
  }
}
