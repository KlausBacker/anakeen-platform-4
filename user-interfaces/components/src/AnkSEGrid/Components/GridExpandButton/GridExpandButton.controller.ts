import "@progress/kendo-ui/js/kendo.button";
import Vue from "vue";
import { Component, Prop, Watch } from "vue-property-decorator";

@Component({
  name: "ank-se-grid-expand-button"
})
export default class GridExpandButtonController extends Vue {
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

  public initButton() {
    const options = this.getButtonOptions();
    this.button = $(this.$refs.expandButton)
      .kendoButton(options)
      .data("kendoButton");
    this.button.bind("click", () => {
      this.gridComponent.onExpandButtonClicked();
    });
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