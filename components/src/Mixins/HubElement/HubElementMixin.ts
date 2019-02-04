// mixin.js
import { Component, Vue } from 'vue-property-decorator';

type ComponentHubConfiguration = {
  expandedTemplate: string,
  collapsedTemplate: string,
  hubRoutes: object[],
  contentEl: Element | string
}

// You can declare a mixin as the same style as components.
@Component
export default class HubElementMixin extends Vue {
  contentEl: Element | Vue | string | null = "#hubStationContent";

  getHubConfiguration() : ComponentHubConfiguration {
    return {
      expandedTemplate: "",
      collapsedTemplate: "",
      hubRoutes: [],
      contentEl: "#hubStationContent"
    }
  }
}