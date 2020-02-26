import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.tooltip";
import $ from "jquery";
import Vue from "vue";
import { Component, Prop, Watch } from "vue-property-decorator";

@Component({
  name: "ank-se-grid-expand-button"
})
export default class AnkGridExpandButtonController extends Vue {
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

  @Watch("gridComponent")
  public watchGridComponent(newValue) {
    this.gridComponent = newValue;
    this.initButton();
  }
  public get translations() {
    return {
      tooltip: this.$t("gridExpandButton.Tooltip")
    };
  }
  public initButton() {
    const options = this.getButtonOptions();
    this.button = $(this.$refs.expandButton)
      .kendoButton(options)
      .data("kendoButton");
    this.button.bind("click", () => {
      $(this.$el).toggleClass("k-state-expand-active");
      this.gridComponent.expandColumns();
    });
    $(".grid-expand-button")
      .kendoTooltip({
        width: 120,
        position: "top",
        autoHide: true,
        showOn: "mouseenter",
        content: this.translations.tooltip
      })
      .data("kendoTooltip");
  }

  private getButtonOptions() {
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
      options.icon = "arrows-resizing";
    }
    return options;
  }
}
