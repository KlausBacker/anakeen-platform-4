import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import AnkSmartElement from "../../SmartElement/SmartElement.vue";
import AnkLoading from "../../AnakeenLoading/AnakeenLoading.vue";
import { SmartElementEvents } from "../../SmartElement/SmartElementEvents";

const capitalize = str => {
  return str.charAt(0).toUpperCase() + str.slice(1);
};

@Component({
  name: "ank-se-tab",
  components: {
    "ank-smart-element": AnkSmartElement,
    "ank-loading": AnkLoading
  }
})
export default class SETab extends Vue {
  @Prop({ default: "Chargement en cours...", type: String })
  public label!: string;
  @Prop({ default: false, type: Boolean }) public disabled!: boolean;
  @Prop({ type: String }) public identifier!: string;
  @Prop({ type: String }) public tabId!: string;
  @Prop({ type: String, default: "!defaultConsultation" })
  public viewId!: string;
  @Prop({ default: false, type: Boolean }) public closable!: boolean;
  @Prop({ default: false, type: Boolean }) public lazy!: boolean;

  @Watch("label")
  onLabelPropCHange(newValue, oldValue) {
    this.$parent.$emit("tabLabelChanged");
  }

  public index: any = null;
  public loaded: boolean = false;
  public isDirty: boolean = false;
  public elementIcon: string = `<i class="fa fa-spinner fa-spin"></i>`;
  public elementTitle: string = this.label;
  public documentLoaded: boolean = false;

  public $refs!: {
    smartElement: AnkSmartElement;
  };

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
    return `<a href="/api/v2/smart-elements/${this.identifier}/views/${this
      .viewId || "!defaultConsultation"}.html"
              title="${this.elementTitle}"
              onclick="return false"
            >
              ${this.elementIcon}
              <span>${this.elementTitle}</span>
            </a>`;
  }

  protected bindSmartElementEvents() {
    if (this.$refs.smartElement) {
      const eventOptions = {
        // @ts-ignore
        check: (properties) => this.$refs.smartElement.getProperties().initid === properties.initid
      };
      window.ank.smartElement.globalController.addEventListener(
        "ready",
        eventOptions,
        (event, elementData) => {
          $(event.target, this.$el)
            .find(".dcpDocument__header")
            .hide();
          this.elementIcon = `<img src="${elementData.icon}"/>`;
          this.elementTitle = elementData.title;
        }
      );
      const isDirtyCb = (event, elementData) => {
        this.isDirty = !!elementData.isModified;
      };
      window.ank.smartElement.globalController.addEventListener(
        "change",
        eventOptions,
        isDirtyCb
      );
      window.ank.smartElement.globalController.addEventListener(
        "close",
        eventOptions,
        isDirtyCb
      );
      SmartElementEvents.forEach(eventName => {
        this.$refs.smartElement.$on(eventName, (...args) => {
          this.$emit(`seTab${capitalize(eventName)}`, ...args);
        });
      });
    } else {
      console.warn("[AnkSETab]: Smart Element component unfound in template");
    }
  }

  public mounted() {
    if (this.$slots && !(this.$slots.default && this.$slots.default.length)) {
      this.bindSmartElementEvents();
      const onLoaded = () => {
        // // @ts-ignore
        // this.$refs.smartElement.fetchSmartElement({
        //   initid: this.identifier,
        //   viewId: this.viewId
        // });
      };
      // @ts-ignore
      if (this.$refs.smartElement.isLoaded()) {
        onLoaded();
      } else {
        this.$refs.smartElement.$on("documentLoaded", onLoaded);
      }
    }
  }

  public close() {
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

  private onDocumentLoaded() {
    this.documentLoaded = true;
  }
}
