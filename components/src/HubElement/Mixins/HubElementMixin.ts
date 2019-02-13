// mixin.js
const path = require("path");
import {Component, Prop, Vue} from 'vue-property-decorator';
// import {
//   HubStationConfigComponentDef,
//   HubStationConfigPosition,
//   HubStationEntryOptions
// } from "../../HubStation/HubStationsTypes";
import {HubElementDisplayTypes} from "../HubElementTypes";

// You can declare a mixin as the same style as components.
@Component
export default class HubElementMixin extends Vue {
  // @Prop() position!: HubStationConfigPosition;
  // @Prop() component!: HubStationConfigComponentDef;
  // @Prop() entryOptions!: HubStationEntryOptions;
  @Prop() displayType!: HubElementDisplayTypes;
  @Prop() parentPath!: string;

  get isDockCollapsed() {
    return this.displayType === HubElementDisplayTypes.COLLAPSED;
  }

  get isDockExpanded() {
    return this.displayType === HubElementDisplayTypes.EXPANDED;
  }

  get isHubContent() {
    return this.displayType === HubElementDisplayTypes.CONTENT;
  }

  resolveHubSubPath(subPath) {
    return path.join(this.parentPath, subPath);
  }
}