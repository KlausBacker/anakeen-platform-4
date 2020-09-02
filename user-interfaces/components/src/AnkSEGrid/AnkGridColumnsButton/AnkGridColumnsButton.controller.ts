import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.tooltip";
import $ from "jquery";
import VueI18n from "vue-i18n";
import { Component, Mixins, Prop, Watch } from "vue-property-decorator";
import I18nMixin from "../../../mixins/AnkVueComponentMixin/I18nMixin";
import { SmartGridColumn } from "../AnkSEGrid.component";

@Component({
  name: "ank-se-grid-columns-button"
})
export default class AnkGridColumnsButtonController extends Mixins(I18nMixin) {
  @Prop({
    default: "",
    type: String
  })
  public iconClass;
  @Prop({ type: String, default: "" }) public title;
  @Prop({
    default: null,
    type: Object
  })
  public gridComponent;

  public button = null;
  public changes: object = {};
  public columns = [];
  public kendoWindow = null;
  public searchInput = "";

  public get validColumns(): SmartGridColumn[] {
    return this.gridComponent.columnsList.filter(c => {
      if (!this.searchInput) {
        return true;
      }
      const label = c.title ? c.title.toLowerCase() : c.field.toLowerCase();
      if (label) {
        return label.includes(this.searchInput.toLowerCase());
      }
    });
  }
  public get dialogTitle(): string {
    return this.title || this.translations.dialogTitle;
  }
  public get translations(): { [key: string]: VueI18n.TranslateResult } {
    return {
      resetConfiguration: this.$t("gridColumnsButton.Reset configuration"),
      applyChanges: this.$t("gridColumnsButton.Apply changes"),
      cancel: this.$t("gridColumnsButton.Cancel"),
      columnSearch: this.$t("gridColumnsButton.Search a column..."),
      dialogTitle: this.$t("gridColumnsButton.Columns management"),
      display: this.$t("gridColumnsButton.Display"),
      label: this.$t("gridColumnsButton.Title"),
      organize: this.$t("gridColumnsButton.Organize"),
      tooltip: this.$t("gridColumnsButton.Tooltip")
    };
  }

  public mounted(): void {
    const options = this.getButtonOptions();
    this.button = $(this.$refs.columnsButton)
      .kendoButton(options)
      .data("kendoButton");
    this.button.bind("click", () => {
      this.open();
    });
    $(".columns-wrapper")
      .kendoTooltip({
        width: 120,
        position: "bottom",
        autoHide: true,
        showOn: "mouseenter",
        content: this.translations.tooltip
      })
      .data("kendoTooltip");
    this.kendoWindow = kendo
      .jQuery(this.$refs.kendoWindow)
      .kendoWindow({
        actions: [],
        close: () => {
          this.searchInput = "";
          this.$forceUpdate();
        },
        modal: true,
        open: e => {
          // Window is not included in the component template
          e.sender.wrapper.find(".k-window-title").css("text-align", "center");
          e.sender.wrapper.find(".k-window-title").css("font-size", "1.5rem");
          e.sender.wrapper.find(".k-window-title").css("color", "#6F6F6F");
          e.sender.wrapper.find(".k-window-titlebar").css("border", "0");
        },
        title: this.dialogTitle,
        visible: false,
        width: "50%"
      })
      .data("kendoWindow");
  }

  public close(): void {
    if (this.kendoWindow) {
      this.kendoWindow.close();
    }
  }
  public open(): void {
    if (this.kendoWindow) {
      this.kendoWindow.center().open();
    }
  }
  public resize(): void {
    if (this.kendoWindow) {
      this.kendoWindow.resize();
    }
  }
  public acceptChanges(): void {
    this.gridComponent.onSettingsChange(this.changes).then(() => {
      this.close();
    });
  }
  public onDisplayColumn(e, colConfig): void {
    if (e.target.checked) {
      this.changes[colConfig.field] = { display: true };
    } else {
      this.changes[colConfig.field] = { display: false };
    }
  }
  public resetConfiguration() {
    if (this.gridComponent.persistStateKey) {
      this.gridComponent.resetConfiguration();
      if (this.kendoWindow) {
        this.kendoWindow.close();
      }
    }
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
      options.icon = "custom";
    }
    return options;
  }
}
