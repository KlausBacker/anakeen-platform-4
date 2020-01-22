import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import AnkLoading from "../../AnkLoading/AnkLoading.vue";
import AnkSmartElement from "../../AnkSmartElement/AnkSmartElement.vue";
import { SmartElementEvents } from "../../AnkSmartElement/SmartElementEvents";
import $ from "jquery";

const capitalize = str => {
  return str.charAt(0).toUpperCase() + str.slice(1);
};

@Component({
  components: {
    "ank-loading": AnkLoading,
    "ank-smart-element": AnkSmartElement
  },
  name: "ank-se-tab"
})
export default class SETab extends Vue {
  get selectedTab() {
    // @ts-ignore
    return this.$parent.selectedTab;
  }

  get isClosable() {
    // @ts-ignore
    return this.closable || this.$parent.closable;
  }

  get active() {
    // @ts-ignore
    const active = this.selectedTab === this.paneName;
    if (active) {
      this.loaded = true;
    }
    return active;
  }

  get paneName() {
    return this.tabId || this.identifier || this.index;
  }

  public get tabTitle() {
    return this.elementTitle;
  }

  public get tabNavItemList() {
    return `<a href="/api/v2/smart-elements/${this.identifier}/views/${this.viewId || "!defaultConsultation"}.html"
              title="${this.elementTitle}"
              onclick="return false"
            >
              ${this.elementIcon}
              <span>${this.elementTitle}</span>
            </a>`;
  }
  @Prop({ default: "Chargement en cours...", type: String })
  public label!: string;
  @Prop({ default: false, type: Boolean }) public disabled!: boolean;
  @Prop({ type: String }) public identifier!: string;
  @Prop({ type: String }) public tabId!: string;
  @Prop({ type: String, default: "!defaultConsultation" })
  public viewId!: string;
  @Prop({ type: Number, default: -1 })
  public revision!: string;
  @Prop({ default: false, type: Boolean }) public closable!: boolean;
  @Prop({ default: false, type: Boolean }) public lazy!: boolean;

  public index: any = null;
  public loaded: boolean = false;
  public isDirty: boolean = false;
  public elementIcon: string = `<i class="fa fa-spinner fa-spin"></i>`;
  public elementTitle: string = this.label;
  public smartElementLoaded: boolean = false;

  public $refs!: {
    smartElement: AnkSmartElement;
  };

  public mounted() {
    if (this.identifier && this.lazy) {
      this.$http.get(`/api/v2/smart-elements/${this.identifier}.json`).then(response => {
        this.elementTitle = response.data.data.document.properties.title;
        this.elementIcon = `<img src="${response.data.data.document.properties.icon}"/>`;
      });
    }
    if (this.$refs.smartElement) {
      this.bindSmartElementEvents();
    }
    this.$emit("smartElementTabMounted");
  }

  public closeSmartElement() {
    // @ts-ignore
    if (this.$refs.smartElement && this.$refs.smartElement.isLoaded()) {
      try {
        // @ts-ignore
        return this.$refs.smartElement.tryToDestroy();
      } catch (err) {
        return Promise.resolve();
      }
    } else {
      return Promise.resolve();
    }
  }

  @Watch("label")
  protected onLabelPropCHange() {
    this.$parent.$emit("tabLabelChanged");
  }

  protected bindSmartElementEvents() {
    this.$refs.smartElement.$on("ready", (event, elementData) => {
      $(event.target, this.$el)
        .find(".dcpDocument__header")
        .hide();
      this.elementIcon = `<img src="${elementData.icon}"/>`;
      this.elementTitle = elementData.title;
    });
    const isDirtyCb = (event, elementData) => {
      this.isDirty = !!elementData.isModified;
    };
    this.$refs.smartElement.$on("smartFieldChange", isDirtyCb);
    this.$refs.smartElement.$on("close", isDirtyCb);
    SmartElementEvents.forEach(eventName => {
      this.$refs.smartElement.$on(eventName, (...args) => {
        this.$emit(`SmartElementTab${capitalize(eventName)}`, ...args);
      });
    });
  }

  private onSmartElementLoaded() {
    this.smartElementLoaded = true;
  }
}
