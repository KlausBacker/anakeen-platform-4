import AbstractGridUtil from "./AbstractGridUtil";

export default class GridVueUtil extends AbstractGridUtil {
  hasDefaultSlots() {
    return this.vueComponent.$slots && this.vueComponent.$slots.default;
  }

  getSlotConfig() {
    if (this.vueComponent.$slots && this.vueComponent.$slots.default) {
      return {
        smartFields: this.getColumnsSlotConfig(),
        toolbar: this.getToolbarSlotConfig(),
        actions: this.getActionsSlotConfig()
      };
    }
    return null;
  }

  getColumnsSlotConfig() {
    if (this.hasDefaultSlots()) {
      const defaultSlots = this.vueComponent.$slots.default;
      return defaultSlots
        .filter(defSlot => defSlot.tag && defSlot.tag.toLocaleLowerCase() === this.getColumnTag())
        .map(vnode => (vnode.data ? vnode.data.attrs : {}));
    }
    return [];
  }

  getActionsSlotConfig() {
    if (this.hasDefaultSlots()) {
      const actionsSlots = this.vueComponent.$slots.default.filter(
        slot => slot.tag && slot.tag.toLocaleLowerCase() === this.getActionsTag()
      );
      if (!actionsSlots.length) {
        return {};
      }
      const actionsConfig = actionsSlots.map(vnode => (vnode.data ? vnode.data.attrs : {}));
      const allActionConfigs = actionsSlots.reduce((acc, currItem) => {
        if (currItem.children) {
          const actionConfig = currItem.children
            .filter(defSlot => defSlot.tag && defSlot.tag.toLocaleLowerCase() === this.getActionTag())
            .map(vnode => (vnode.data ? vnode.data.attrs : {}));
          return acc.concat(actionConfig);
        }
        return acc;
      }, []);
      return {
        title: actionsConfig[0].title || "",
        actionConfigs: allActionConfigs
      };
    }
    return {};
  }

  getToolbarSlotConfig() {
    if (this.hasDefaultSlots()) {
      const toolbarSlots = this.vueComponent.$slots.default.filter(
        slot => slot.tag && slot.tag.toLocaleLowerCase() === this.getToolbarTag()
      );
      if (!toolbarSlots.length) {
        return {};
      }
      const toolbarConfig = toolbarSlots.map(vnode => (vnode.data ? vnode.data.attrs : {}));
      const allToolbarActionConfigs = toolbarSlots.reduce((acc, currItem) => {
        if (currItem.children) {
          const actionConfig = currItem.children
            .filter(defSlot => defSlot.tag && defSlot.tag.toLocaleLowerCase() === this.getToolbarActionTag())
            .map(vnode => (vnode.data ? vnode.data.attrs : {}));
          return acc.concat(actionConfig);
        }
        return acc;
      }, []);
      return {
        title: toolbarConfig[0].title || "",
        actionConfigs: allToolbarActionConfigs
      };
    }
    return {};
  }

  getGridTag() {
    let tagName = "ank-se-grid";
    if (this.vueComponent.$options && this.vueComponent.$options._componentTag) {
      tagName = this.vueComponent.$options._componentTag.toLocaleLowerCase();
    }
    return tagName;
  }

  getColumnTag() {
    return `${this.getGridTag()}-column`.toLocaleLowerCase();
  }
  getToolbarTag() {
    return `${this.getGridTag()}-toolbar`.toLocaleLowerCase();
  }
  getToolbarActionTag() {
    return `${this.getToolbarTag()}-action`.toLocaleLowerCase();
  }
  getActionsTag() {
    return `${this.getGridTag()}-actions`.toLocaleLowerCase();
  }
  getActionTag() {
    return `${this.getGridTag()}-action`.toLocaleLowerCase();
  }
}
