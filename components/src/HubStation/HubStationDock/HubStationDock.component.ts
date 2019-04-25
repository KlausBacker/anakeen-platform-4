// Vue class based component export

import AnkIdentity from "@anakeen/user-interfaces/components/lib/AnkIdentity";
import AnkLogout from "@anakeen/user-interfaces/components/lib/AnkLogout";
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
  @Prop({ default: () => [], type: Array })
  public dockContent!: IHubStationPropConfig[];
  @Prop({ default: DockPosition.LEFT, type: String })
  public position!: DockPosition;
  @Prop({ default: "", type: String }) public rootUrl!: string;
  // endregion props

  protected dockIsCollapsed: boolean = true;
  get InnerDockPosition() {
    return InnerDockPosition;
  }
  get dockState() {
    return this.dockIsCollapsed
      ? HubElementDisplayTypes.COLLAPSED
      : HubElementDisplayTypes.EXPANDED;
  }
  // noinspection JSMethodCanBeStatic
  get HubElementDisplayTypes(): any {
    return HubElementDisplayTypes;
  }

  get isExpandable(): boolean {
    let result = false;
    if (this.dockContent) {
      this.dockContent.forEach(dC => {
        if (dC && dC.entryOptions) {
          result = result || dC.entryOptions.expandable;
        }
      });
    }
    return result;
  }

  public mounted() {
    this.dockIsCollapsed = this.$refs.innerDock.collapsed;
  }

  // region methods
  protected getDock(type, configs: IHubStationPropConfig[]) {
    return configs
      .filter(c => {
        return c.position.innerPosition === type;
      })
      .sort((a, b) => {
        const posa = a.position.order || 0;
        const posb = b.position.order || 0;
        if (posa > posb) {
          return 1;
        } else if (posa < posb) {
          return -1;
        } else if (posa === posb) {
          const sortTitle = a.entryOptions.name.localeCompare(
            b.entryOptions.name
          );
          if (sortTitle > 0) {
            return 1;
          } else if (sortTitle < 0) {
            return -1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
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
      return this.getEntryRoute(entry) === urlJoin(window.location.pathname);
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
      return urlJoin("/", this.rootUrl, entry.entryOptions.route, "/");
    }
    return "";
  }
  // endregion methods
}
