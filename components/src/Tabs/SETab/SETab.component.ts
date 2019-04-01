import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import AnkSmartElement from "../../SmartElement/SmartElement.vue";
@Component({
  name: "ank-se-tab",
  components: {
    "ank-smart-element": AnkSmartElement
  }
})
export default class SETab extends Vue {
  @Prop({ default: "Chargement en cours...", type: String })
  public label!: string;
  @Prop({ default: false, type: Boolean }) public disabled!: boolean;
  @Prop({ type: [Number, String] }) public identifier!: string | number;
  @Prop({ default: false, type: Boolean }) public closable!: boolean;
  @Prop({ default: false, type: Boolean }) public lazy!: boolean;

  @Watch("label")
  onLabelPropCHange(newValue, oldValue) {
    this.$parent.$emit("tabLabelChanged");
  }

  public index: any = null;
  public loaded: boolean = false;

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
    const active = this.selectedTab == this.paneName;
    if (active) {
      this.loaded = true;
    }
    return active;
  }

  get paneName() {
    return this.identifier || this.index;
  }

  protected bindSmartElementEvents() {
    if (this.$refs.smartElement) {
      this.$refs.smartElement.$on("ready", (event, elementData) => {
        $(event.target, this.$el)
          .find(".dcpDocument__header")
          .hide();
      });
    } else {
      console.warn("[AnkSETab]: Smart Element component unfound in template");
    }
  }

  public mounted() {
    if (this.$slots && !(this.$slots.default && this.$slots.default.length)) {
      this.bindSmartElementEvents();
      const onLoaded = () => {
        // @ts-ignore
        this.$refs.smartElement.fetchSmartElement({
          initid: this.paneName
        });
      };
      // @ts-ignore
      if (this.$refs.smartElement.isLoaded()) {
        onLoaded();
      } else {
        this.$refs.smartElement.$on("documentLoaded", onLoaded);
      }
    }
  }
}
