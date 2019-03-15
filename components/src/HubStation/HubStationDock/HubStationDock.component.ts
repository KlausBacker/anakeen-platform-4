// Vue class based component export
import { AnkIdentity, AnkLogout } from "@anakeen/user-interfaces";
import { Component, Prop, Vue } from "vue-property-decorator";
import HubDock from "../../HubDock/HubDock.vue";
import HubDockEntry from "../../HubDock/HubDockEntry/HubDockEntry.vue";
import { HubElementDisplayTypes } from "../../HubElement/HubElementTypes";
import HubLabel from "../../HubLabel/HubLabel.vue";
import {
  DockPosition,
  IHubStationPropConfig,
  InnerDockPosition
} from "../HubStationsTypes";

const urlJoin = require("url-join");

@Component({
  components: {
    "ank-identity": AnkIdentity,
    "ank-logout": AnkLogout,
    "hub-dock": HubDock,
    "hub-dock-entry": HubDockEntry,
    "hub-label": HubLabel
  },
  name: "hub-station-dock"
})
export default class HubStationDock extends Vue {
  public $refs!: {
    innerDock: HubDock | any;
  };

  // region props
  @Prop({ default: () => ({}), type: Array })
  public dockContent!: IHubStationPropConfig[];
  @Prop({ default: DockPosition.LEFT, type: String })
  public position!: DockPosition;
  @Prop({ default: "", type: String }) public rootUrl!: string;
  // endregion props

  protected dockIsCollapsed: boolean = true;

  get dockState() {
    return this.dockIsCollapsed
      ? HubElementDisplayTypes.COLLAPSED
      : HubElementDisplayTypes.EXPANDED;
  }
  // noinspection JSMethodCanBeStatic
  get HubElementDisplayTypes(): any {
    return HubElementDisplayTypes;
  }

  public mounted() {
    this.dockIsCollapsed = this.$refs.innerDock.collapsed;
  }

  // region methods
  protected getDockHeaders(configs: IHubStationPropConfig[]) {
    return configs
      .filter(c => {
        return c.position.innerPosition === InnerDockPosition.HEADER;
      })
      .sort((a, b) => {
        if (a.position && a.position.order && b.position && b.position.order) {
          return a.position.order - b.position.order;
        }
        return 0;
      });
  }

  protected getDockCenter(configs: IHubStationPropConfig[]) {
    return configs
      .filter(c => {
        return c.position.innerPosition === InnerDockPosition.CENTER;
      })
      .sort((a, b) => {
        if (a.position && a.position.order && b.position && b.position.order) {
          return a.position.order - b.position.order;
        }
        return 0;
      });
  }

  protected getDockFooter(configs: IHubStationPropConfig[]) {
    return configs
      .filter(c => {
        return c.position.innerPosition === InnerDockPosition.FOOTER;
      })
      .sort((a, b) => {
        if (a.position && a.position.order && b.position && b.position.order) {
          return a.position.order - b.position.order;
        }
        return 0;
      });
  }

  // noinspection JSMethodCanBeStatic
  protected resizeWindow() {
    this.dockIsCollapsed = this.$refs.innerDock.collapsed;
    this.$nextTick(() => {
      setTimeout(() => {
        window.dispatchEvent(new Event("resize"));
      }, 1);
    });
  }
  // noinspection JSMethodCanBeStatic
  protected isSelectableEntry(entry) {
    if (entry && entry.entryOptions) {
      return !!entry.entryOptions.selectable;
    }
    return true;
  }

  // noinspection JSMethodCanBeStatic
  protected isSelectedEntry(entry) {
    if (entry && entry.entryOptions && entry.entryOptions.route) {
      return window.location.pathname.indexOf(this.getEntryRoute(entry)) > -1;
    } else if (entry && entry.entryOptions) {
      return entry.entryOptions.activated;
    }
    return false;
  }

  protected onEntrySelected(event, entry) {
    this.$emit("hubElementSelected", entry);
  }

  protected getEntryRoute(entry) {
    if (entry && entry.entryOptions && entry.entryOptions.route) {
      return urlJoin("/", this.rootUrl, entry.entryOptions.route);
    }
    return "";
  }

  // endregion methods
}
