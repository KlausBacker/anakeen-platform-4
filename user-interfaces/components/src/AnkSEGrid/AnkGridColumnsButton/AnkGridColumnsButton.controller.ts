import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.tooltip";
import $ from "jquery";
import { Component, Mixins, Prop, Watch } from "vue-property-decorator";
import I18nMixin from "../../../mixins/AnkVueComponentMixin/I18nMixin";

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

  @Watch("searchInput")
  public watchSearchInput(newValue) {
    this.filter(newValue);
  }

  public get validColumns() {
    return this.columns.filter(
      c => !!c.field && c.field !== "ank-grid_selected_rows" && c.field !== "smart_element_grid_action_menu"
    );
  }
  public get dialogTitle() {
    return this.title || this.translations.dialogTitle;
  }
  public get translations() {
    return {
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
  public mounted() {
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

  public filter(filterInput = "") {
    if (filterInput) {
      this.columns = this.columns.filter(col => {
        const title = col.title ? col.title.toLowerCase() : col.title;
        if (title) {
          return title.includes(filterInput.toLowerCase());
        }
        return false;
      });
    } else {
      this.columns = this.gridComponent.columnsList;
    }
  }
  public close() {
    if (this.kendoWindow) {
      this.kendoWindow.close();
    }
  }
  public open() {
    if (this.kendoWindow) {
      this.kendoWindow.center().open();
    }
  }
  public resize() {
    if (this.kendoWindow) {
      this.kendoWindow.resize();
    }
  }
  public acceptChanges() {
    this.gridComponent.onSettingsChange(this.changes);
    this.close();
  }
  public onDisplayColumn(e, colConfig) {
    if (e.target.checked) {
      this.changes[colConfig.field] = { display: true };
    } else {
      this.changes[colConfig.field] = { display: false };
    }
  }

  @Watch("gridComponent.allColumns")
  public watchColumnsList(newValue) {
    this.columns = newValue.filter(
      c => c.field !== "ank-grid_selected_rows" && c.field !== "smart_element_grid_action_menu"
    );
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
      options.icon = "custom";
    }
    return options;
  }
}