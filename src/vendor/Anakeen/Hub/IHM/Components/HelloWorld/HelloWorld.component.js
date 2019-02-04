import { HubElementMixin } from "@aurelien/hub-components";

export default {
  mixins: [HubElementMixin],
  props: {
    msg: {
      type: String
    },
    test: {
      type: Boolean,
      default: false
    }
  },
  getHubConfiguration() {
    return {
      collapsedTemplate:
        "<div style='width: 100%; height: 100%;display: flex; justify-content: center; align-items: center'><hello-icon></hello-icon></i></div>",
      expandedTemplate:
        "<div style='width: 100%; height: 100%;display: flex; justify-content: center; align-items: center'><i class='fa fa-edit'></i> <span>My app</span></div>",
      contentEl: "#hubStationContent"
    };
  }
};
