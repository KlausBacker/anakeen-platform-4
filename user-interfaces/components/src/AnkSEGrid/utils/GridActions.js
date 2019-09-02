import GridEvent from "./GridEvent";
import AbstractGridUtil from "./AbstractGridUtil";

export const DEFAULT_ACTION_PROPS = {
  edit: {},
  consult: {},
  custom: {}
};

export default class GridActions extends AbstractGridUtil {
  getAction(actionName) {
    const actionMethod = `${actionName}Action`;
    const actionObject = {};
    actionObject.title = this.vueComponent.translations[actionName] || actionName;
    actionObject.iconClass = DEFAULT_ACTION_PROPS[actionName] ? DEFAULT_ACTION_PROPS[actionName].iconClass : "";
    if (typeof this[actionMethod] === "function") {
      actionObject.click = this[actionMethod].bind(this);
    } else {
      actionObject.click = this.customAction.bind(this);
    }
    return actionObject;
  }

  editAction(e) {
    e.preventDefault();
    const target = e.currentTarget || e.item || e.target;
    const item = this.vueComponent.kendoGrid.dataItem(this.vueComponent.$(target).closest("tr")).rowData;
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
    this.vueComponent.$emit("action-click", event);
    if (!event.isDefaultPrevented()) {
      window.open(`/api/v2/smart-elements/${id}/views/!defaultEdition.html`, "_blank");
    }
  }

  consultAction(e) {
    if (typeof e.preventDefault === "function") {
      e.preventDefault();
    }
    const target = e.currentTarget || e.item || e.target;
    const item = this.vueComponent.kendoGrid.dataItem(this.vueComponent.$(target).closest("tr")).rowData;
    const event = new GridEvent(
      {
        type: "consult",
        row: item
      },
      target,
      true,
      "GridActionEvent"
    );
    this.vueComponent.$emit("action-click", event);
    const id = item.initid || item.id;
    if (!event.isDefaultPrevented()) {
      window.open(`/api/v2/smart-elements/${id}/views/!defaultConsultation.html`, "_blank");
    }
  }

  customAction(e, actionType) {
    e.preventDefault();
    const target = e.currentTarget || e.item || e.target;
    if (actionType) {
      const item = this.vueComponent.kendoGrid.dataItem(this.vueComponent.$(target).closest("tr")).rowData;
      const event = new GridEvent({ type: actionType, row: item }, target, false, "GridActionEvent");
      this.vueComponent.$emit("action-click", event);
    }
  }
}
