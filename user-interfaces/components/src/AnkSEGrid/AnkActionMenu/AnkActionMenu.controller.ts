import { Component, Prop, Watch, Vue } from "vue-property-decorator";
import { Popup } from "@progress/kendo-vue-popup";
import GridEvent from "../AnkGridEvent/AnkGridEvent";

const isStandardAction = action => action === "display" || action === "modify";

@Component({
  name: "ank-se-grid-actions",
  components: {
    Popup
  }
})
export default class AnkActionMenuController extends Vue {
  @Prop({
    default: () => [
      {
        action: "",
        title: "",
        iconClass: ""
      }
    ],
    type: Array
  })
  public actions: object[];

  @Prop({
    type: Object
  })
  public rowData: any;

  @Prop({
    default: () => {},
    type: Object
  })
  public gridComponent: any;

  public showSecondaryActionsMenu: boolean = false;
  public hoverPopup: boolean = false;

  public get primaryActions() {
    if (this.actions) {
      if (this.actions.length > 2) {
        return [this.actions[0]];
      } else {
        return this.actions;
      }
    }
    return [];
  }

  public get secondaryActions() {
    if (this.actions) {
      if (this.actions.length > 2) {
        return this.actions.slice(1);
      } else {
        return [];
      }
    }
    return [];
  }

  public created() {
    window.addEventListener("click", () => {
      if (!this.hoverPopup) {
        this.showSecondaryActionsMenu = false;
      }
    });
  }

  public beforeDestroy() {
    window.removeEventListener("click", () => {
      if (!this.hoverPopup) {
        this.showSecondaryActionsMenu = false;
      }
    });
  }

  protected onRowActionClick(evt, action) {
    evt.preventDefault();
    const event = new GridEvent(
      {
        type: action.action,
        row: JSON.parse(JSON.stringify(this.rowData))
      },
      evt.target,
      isStandardAction(action.action),
      "GridActionEvent"
    );
    this.$emit("rowActionClick", event);
    if (isStandardAction(action.action) && !event.isDefaultPrevented()) {
      const viewId = action.action === "modify" ? "defaultEdition" : "defaultConsultation";
      window.open(
        `/api/v2/smart-elements/${this.rowData.properties.id || this.rowData.properties.initid}/views/!${viewId}.html`,
        "_blank"
      );
    }
    this.showSecondaryActionsMenu = false;
  }

  public customAction(e, actionType) {
    e.preventDefault();
    // index - 1 to start from 0
    const index = e.item.closest("td").getAttribute("dataindex") - 1;
    const target = e.currentTarget || e.item || e.target;
    if (actionType) {
      const item = this.gridComponent.dataItems[index].properties;
      const event = new GridEvent({ type: actionType, row: item }, target, false, "GridActionEvent");
      this.$emit("rowActionClick", event);
    }
  }
}