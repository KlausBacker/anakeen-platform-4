// mixin.js
const path = require("path");
import { Component, Prop, Vue } from "vue-property-decorator";
import {
 IHubStationEntryOptions
} from "../../HubStation/HubStationsTypes";
import { HubElementDisplayTypes } from "../HubElementTypes";

// You can declare a mixin as the same style as components.
@Component
export default class HubElementMixin extends Vue {
  @Prop() public entryOptions!: IHubStationEntryOptions;
  @Prop() public displayType!: HubElementDisplayTypes;
  @Prop() public parentPath!: string;

  get isDockCollapsed() {
    return this.displayType === HubElementDisplayTypes.COLLAPSED;
  }

  get isDockExpanded() {
    return this.displayType === HubElementDisplayTypes.EXPANDED;
  }

  get isHubContent() {
    return this.displayType === HubElementDisplayTypes.CONTENT;
  }

  public resolveHubSubPath(subPath) {
    return path.join(this.parentPath, subPath);
  }
}
