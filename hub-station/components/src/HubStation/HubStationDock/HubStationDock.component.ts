// Vue class based component export
import AnkIdentity from "@anakeen/user-interfaces/components/lib/AnkIdentity.esm";
import AnkLogout from "@anakeen/user-interfaces/components/lib/AnkLogout.esm";
import { Component, Inject, Prop, Vue } from "vue-property-decorator";
import HubDock from "../../HubDock/HubDock.vue";
import HubDockEntry from "../../HubDock/HubDockEntry/HubDockEntry.vue";
import { HubElementDisplayTypes } from "../../HubElement/HubElementTypes";
// eslint-disable-next-line no-unused-vars
import { DockPosition, IHubStationPropConfig, InnerDockPosition } from "../HubStationsTypes";
import HubLabel from "../../HubLabel/HubLabel.vue";

const urlJoin = require("url-join");

declare global {
  interface Window {
    ank: {
      hub: [];
    };
  }
}

window.ank = window.ank || {};
window.ank.hub = window.ank.hub || {};
const customComponents = Object.keys(window.ank.hub).reduce((acc, currentComponent) => {
  acc[currentComponent] = () => window.ank.hub[currentComponent].promise;
  return acc;
}, {});

@Component({
  components: {
    ...(customComponents || {}),
    ...{
      "ank-identity": AnkIdentity,
      "ank-logout": AnkLogout,
      "hub-dock": HubDock,
      "hub-dock-entry": HubDockEntry,
      "hub-label": HubLabel
    }
  },
  name: "hub-station-dock"
})
export default class HubStationDock extends Vue {
  get InnerDockPosition() {
    return InnerDockPosition;
  }
  get dockState() {
    return true;
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

  protected static normalizeUrl(...url) {
    return urlJoin("/", ...url, "/");
  }

  @Inject("rootHubStation") public readonly rootHubStation!: Vue;
  public $refs!: {
    innerDock: HubDock | any;
  };

  // region props
  @Prop({ default: () => [], type: Array })
  public dockContent!: IHubStationPropConfig[];
  @Prop({ default: DockPosition.LEFT, type: String })
  public position!: DockPosition;
  @Prop({ default: "", type: String })
  public rootUrl!: string;
  @Prop({ default: "", type: String })
  public activeRoute!: string;
  @Prop({ default: {}, type: Object })
  public dockConfiguration!: object;
  // endregion props

  public dockIsCollapsed = true;

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
          const sortTitle = a.entryOptions.name.localeCompare(b.entryOptions.name);
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
      return HubStationDock.normalizeUrl(this.activeRoute).indexOf(this.getEntryRoute(entry)) > -1;
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
      return HubStationDock.normalizeUrl(entry.entryOptions.route);
    }
    return "";
  }

  protected onComponentMounted(entry, ref) {
    const currentComponent = this.$refs[ref][0];
    if (currentComponent) {
      const layout = currentComponent.$children[0];
      if (layout && layout.$slots && layout.$slots.hubContent) {
        const data = Object.assign({}, entry, {
          hubContentLayout: layout
        });
        data.entryOptions = data.entryOptions || {};
        data.entryOptions.completeRoute = urlJoin(this.rootUrl, entry.entryOptions.route);
        // @ts-ignore
        this.rootHubStation.panes.push(data);
      }
    }
  }
  // endregion methods
}
