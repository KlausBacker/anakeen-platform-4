import { Component, Prop, Watch, Vue } from "vue-property-decorator";
import GridEvent from "../../AnkSEGrid/utils/GridEvent";
import "@progress/kendo-ui/js/kendo.menu";

export const DEFAULT_ACTION_PROPS = {
  edit: {},
  consult: {},
  custom: {}
};

@Component({
  name: "ank-se-grid-actions"
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
    default: () => {},
    type: Object
  })
  public gridComponent: any;

  public value = { action: "consult", title: "Consult" };

  public mounted() {
    const menu = this.$refs.actionMenu;
    const kendoMenuOptions = {
      openOnClick: {
        rootMenuItems: true
      },
      select: e => this.onActionMenuClick(e)
    };
    $(menu).kendoMenu(kendoMenuOptions);
  }

  onActionMenuClick(e) {
    const actionType = e.item.dataset.actiontype;
    if (actionType) {
      if (`${actionType}Action` !== "_subcommands") {
        const kendoMenu = $(this.$refs.actionMenu).data("kendoMenu");
        const $gridContent = $(".k-grid-content");
        const $menu = $(e.sender.element[0]);
        const threshold = 85;
        const gridContentOffset = $gridContent.offset();
        const menuOffset = $menu.offset();
        const remainingSpace = gridContentOffset.top + $gridContent.height() - (menuOffset.top + $menu.height());
        if (remainingSpace < threshold) {
          kendoMenu.setOptions({
            ...kendoMenu.options,
            direction: "top"
          });
        } else {
          kendoMenu.setOptions({
            ...kendoMenu.options,
            direction: "bottom"
          });
        }
      }
      const action = this.getAction(actionType);
      action.click(e, actionType);
    }
  }
  public getAction(actionName) {
    const actionMethod = `${actionName}Action`;
    const actionObject: any = {};
    // actionObject.title = this.grid.translations[actionName] || actionName;
    actionObject.title = actionName;
    actionObject.iconClass = DEFAULT_ACTION_PROPS[actionName] ? DEFAULT_ACTION_PROPS[actionName].iconClass : "";
    if (typeof this[actionMethod] === "function") {
      actionObject.click = this[actionMethod].bind(this);
    } else {
      actionObject.click = this.customAction.bind(this);
    }
    return actionObject;
  }

  public editAction(e) {
    e.preventDefault();
    // index - 1 to start from 0
    const index = e.item.closest("td").getAttribute("dataindex") - 1;
    const target = e.currentTarget || e.item || e.target;
    const item = this.gridComponent.dataItems[index].properties;
    const event = new GridEvent(
      {
        type: "edit",
        row: item
      },
      target,
      true,
      "GridActionEvent"
    );
    const id = item.initid || item.id;
    this.$emit("action-click", event);
    if (!event.isDefaultPrevented()) {
      window.open(`/api/v2/smart-elements/${id}/views/!defaultEdition.html`, "_blank");
    }
  }

  public consultAction(e) {

    if (typeof e.preventDefault === "function") {
      e.preventDefault();
    }
    // index - 1 to start from 0
    const index = e.item.closest("td").getAttribute("dataindex") - 1;
    const target = e.currentTarget || e.item || e.target;
    const item = this.gridComponent.dataItems[index].properties;
    const event = new GridEvent(
      {
        type: "consult",
        row: item
      },
      target,
      true,
      "GridActionEvent"
    );
    this.$emit("action-click", event);
    const id = item.initid || item.id;
    if (!event.isDefaultPrevented()) {
      window.open(`/api/v2/smart-elements/${id}/views/!defaultConsultation.html`, "_blank");
    }
  }

  public customAction(e, actionType) {
    e.preventDefault();
    // index - 1 to start from 0
    const index = e.item.closest("td").getAttribute("dataindex") - 1;
    const target = e.currentTarget || e.item || e.target;
    if (actionType) {
      const item = this.gridComponent.dataItems[index].properties;
      const event = new GridEvent({ type: actionType, row: item }, target, false, "GridActionEvent");
      this.$emit("action-click", event);
    }
  }
}
