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

  get hasLoadingSlot() {
    return !!this.$scopedSlots && !!this.$scopedSlots.loading;
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
  @Prop({ type: Boolean, default: true }) public autoUnload!: boolean;
  @Prop({ type: Object, default: null }) public customClientData!: object;
  @Prop({ type: Boolean, default: false }) public browserHistory!: boolean;

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
  public addEventListener(eventType, options?, callback?) {
    // @ts-ignore
    this.$refs.smartElement.addEventListener(eventType, options, callback);
  }

  public fetchSmartElement(value, options?) {
    // @ts-ignore
    return this.$refs.smartElement.fetchSmartElement(value, options);
  }

  public saveSmartElement(options) {
    // @ts-ignore
    return this.$refs.smartElement.saveSmartElement(options);
  }

  public showMessage(message) {
    // @ts-ignore
    this.$refs.smartElement.showMessage(message);
  }

  public getSmartFields() {
    // @ts-ignore
    return this.$refs.smartElement.getSmartFields();
  }

  public getSmartField(smartFieldId) {
    // @ts-ignore
    return this.$refs.smartElement.getSmartField(smartFieldId);
  }

  public setValue(smartFieldId, newValue) {
    // @ts-ignore
    this.$refs.smartElement.setValue(smartFieldId, newValue);
  }

  public reinitSmartElement(values, options) {
    // @ts-ignore
    return this.$refs.smartElement.reinitSmartElement(values, options);
  }

  public changeStateSmartElement(parameters, reinitOptions, options) {
    // @ts-ignore
    return this.$refs.smartElement.changeStateSmartElement(parameters, reinitOptions, options);
  }

  public deleteSmartElement(options) {
    // @ts-ignore
    return this.$refs.smartElement.deleteSmartElement(options);
  }

  public restoreSmartElement(options) {
    // @ts-ignore
    return this.$refs.smartElement.restoreSmartElement(options);
  }

  public getElement() {
    // @ts-ignore
    return this.$refs.smartElement.getElement();
  }

  public getProperty(property) {
    // @ts-ignore
    return this.$refs.smartElement.getProperty(property);
  }
  public getProperties() {
    // @ts-ignore
    return this.$refs.smartElement.getProperties();
  }

  public hasSmartField(smartFieldId) {
    // @ts-ignore
    return this.$refs.smartElement.hasSmartField(smartFieldId);
  }

  public hasMenu(menuId) {
    // @ts-ignore
    return this.$refs.smartElement.hasMenu(menuId);
  }

  public getMenu(menuId) {
    // @ts-ignore
    return this.$refs.smartElement.getMenu(menuId);
  }

  public getMenus() {
    // @ts-ignore
    return this.$refs.smartElement.getMenus();
  }

  public getValue(smartFieldId, type?: string) {
    // @ts-ignore
    return this.$refs.smartElement.getValue(smartFieldId, type);
  }

  public getValues() {
    // @ts-ignore
    return this.$refs.smartElement.getValues();
  }

  public getCustomServerData() {
    // @ts-ignore
    return this.$refs.smartElement.getCustomServerData();
  }

  public isModified() {
    // @ts-ignore
    return this.$refs.smartElement.isModified();
  }

  public addCustomClientData(check, value) {
    // @ts-ignore
    this.$refs.smartElement.addCustomClientData(check, value);
  }

  public getCustomClientData(deleteOnce) {
    // @ts-ignore
    return this.$refs.smartElement.getCustomClientData(deleteOnce);
  }

  public removeCustomClientData(key) {
    // @ts-ignore
    this.$refs.smartElement.removeCustomClientData(key);
  }

  public appendArrayRow(smartFieldId, values) {
    // @ts-ignore
    this.$refs.smartElement.appendArrayRow(smartFieldId, values);
  }

  public insertBeforeArrayRow(smartFieldId, values, index) {
    // @ts-ignore
    this.$refs.smartElement.insertBeforeArrayRow(smartFieldId, values, index);
  }

  public removeArrayRow(smartFieldId, index) {
    // @ts-ignore
    this.$refs.smartElement.removeArrayRow(smartFieldId, index);
  }

  public addConstraint(options, callback) {
    // @ts-ignore
    this.$refs.smartElement.addConstraint(options, callback);
  }

  public listConstraints() {
    // @ts-ignore
    return this.$refs.smartElement.listConstraints();
  }

  public removeConstraint(constraintName, allKind) {
    // @ts-ignore
    this.$refs.smartElement.removeConstraint(constraintName, allKind);
  }

  public listEventListeners() {
    // @ts-ignore
    return this.$refs.smartElement.listEventListeners();
  }

  public removeEventListener(eventName, allKind) {
    // @ts-ignore
    this.$refs.smartElement.removeEventListener(eventName, allKind);
  }

  public triggerEvent(eventName, ...parameters) {
    // @ts-ignore
    return this.$refs.smartElement.triggerEvent(eventName, ...parameters);
  }

  public hideSmartField(smartFieldId) {
    // @ts-ignore
    this.$refs.smartElement.hideSmartField(smartFieldId);
  }

  public showSmartField(smartFieldId) {
    // @ts-ignore
    this.$refs.smartElement.showSmartField(smartFieldId);
  }

  public maskSmartElement(message, px) {
    // @ts-ignore
    this.$refs.smartElement.maskSmartElement(message, px);
  }

  public unmaskSmartElement(force) {
    // @ts-ignore
    this.$refs.smartElement.unmaskSmartElement(force);
  }

  public tryToDestroy({ testDirty }) {
    // @ts-ignore
    return this.$refs.smartElement.tryToDestroy({ testDirty });
  }

  public injectJS(jsToInject) {
    // @ts-ignore
    return this.$refs.smartElement.injectJS(jsToInject);
  }

  public injectCSS(cssToInject) {
    // @ts-ignore
    return this.$refs.smartElement.injectCSS(cssToInject);
  }

  public selectTab(tabId) {
    // @ts-ignore
    return this.$refs.smartElement.selectTab(tabId);
  }

  public drawTab(tabId) {
    // @ts-ignore
    return this.$refs.smartElement.drawTab(tabId);
  }

  public setCustomClientData(smartElementCheck, value) {
    // @ts-ignore
    this.$refs.smartElement.setCustomClientData(smartElementCheck, value);
  }

  public setSmartFieldErrorMessage(smartFieldId, message, index) {
    // @ts-ignore
    this.$refs.smartElement.setSmartFieldErrorMessage(smartFieldId, message, index);
  }

  public cleanSmartFieldErrorMessage(smartFieldId, index) {
    // @ts-ignore
    this.$refs.smartElement.cleanSmartFieldErrorMessage(smartFieldId, index);
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
        this.$emit(`smartElementTab${capitalize(eventName)}`, ...args);
      });
    });
  }

  private onSmartElementLoaded() {
    this.smartElementLoaded = true;
    this.$emit("smartElementTabMounted");
  }
}
