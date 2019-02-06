// mixin.js
import {Component, Prop, Vue} from 'vue-property-decorator';
import {
  HubStationConfigComponentDef,
  HubStationConfigPosition,
  HubStationEntryOptions
} from "../../HubStation/HubStationsTypes";
import {HubElementDisplayTypes} from "../../HubElement/HubElementTypes";

// You can declare a mixin as the same style as components.
@Component
export default class HubElementMixin extends Vue {
  @Prop() position!: HubStationConfigPosition;
  @Prop() component!: HubStationConfigComponentDef;
  @Prop() entryOptions!: HubStationEntryOptions;
  @Prop() displayType!: HubElementDisplayTypes;

  get isCollapsed() {
    return this.displayType === HubElementDisplayTypes.COLLAPSED;
  }

  get isExpanded() {
    return this.displayType === HubElementDisplayTypes.EXPANDED;
  }

  get isContent() {
    return this.displayType === HubElementDisplayTypes.CONTENT;
  }
}