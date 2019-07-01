/**
 * Anakeen Smart Element component object
 */
import VueSetup from "../setup.js";
import { Component, Prop, Watch, Vue } from "vue-property-decorator";
import { SmartElementEvents } from "./SmartElementEvents";

type SmartElementValue = {
  initid?: number | string;
  viewId?: string;
  revision?: number;
  customClientData?: object;
  noRouter?: boolean;
};

Vue.use(VueSetup);
@Component({
  name: "ank-smart-element"
})
export default class AnkSmartElement extends Vue {
  @Prop({
    type: Object,
    default: () => ({
      initid: 0,
      viewId: "!defaultConsultation",
      revision: -1,
      customClientData: null
    }),
    validator: value => {
      if (value.initid === undefined) {
        console.error("value property must contain a initid key");
        return false;
      }
      return true;
    }
  })
  public value!: SmartElementValue;

  @Prop({ type: Boolean, default: false }) public browserHistory!: boolean;
  @Prop({ type: [String, Number], default: "0" }) public initid!:
    | string
    | number;
  @Prop({ type: Object, default: null }) public customClientData!: object;
  @Prop({ type: String, default: "!defaultConsultation" })
  public viewId!: string;
  @Prop({ type: Number, default: -1 }) public revision!: number;

  public smartElementWidget: any = {
    fetchSmartElement(fetchValue, options?) {
      return Promise.resolve();
    }
  };

  get initialData() {
    const data: SmartElementValue = {
      noRouter: !this.browserHistory
    };

    data.initid = this.value.initid || this.initid;
    data.customClientData =
      this.value.customClientData || this.customClientData;
    data.revision =
      this.value.revision !== -1 ? this.value.revision : this.revision;
    data.viewId = this.value.viewId || this.viewId;
    return data;
  }

  public mounted() {
    if (
      window.ank &&
      window.ank.smartElement &&
      window.ank.smartElement.globalController
    ) {
      // @ts-ignore
      const scopeId = window.ank.smartElement.globalController.addSmartElement(this.$refs.ankSEWrapper, this.initialData);
      this.smartElementWidget = window.ank.smartElement.globalController.scope(scopeId);
      this.$emit("documentLoaded");
      this.listenAttributes();
    }
  }

  public updated() {
    if (this.isLoaded()) {
      this.fetchSmartElement(this.initialData);
    } else {
      this.$once("documentLoaded", () => {
        this.fetchSmartElement(this.initialData);
      });
    }
  }

  /**
   * True when internal widget is loaded
   * @returns {boolean}
   */
  isLoaded() {
    return this.smartElementWidget !== undefined;
  }

  /**
   * Rebind all declared binding to internal widget
   * @returns void
   */
  listenAttributes() {
    const eventNames = SmartElementEvents;
    // @ts-ignore
    const localListener = this.$options._parentListeners || {};
    eventNames.forEach(eventName => {
      this.smartElementWidget.addEventListener(
        eventName,
        {
          name: `v-on-${eventName}-listen`,
          documentCheck(/* documentObject */) {
            return true;
          }
        },
        (event, documentObject, ...others) => {
          this.$emit(eventName, event, documentObject, ...others);
        }
      );
    });

    Object.keys(localListener).forEach(key => {
      // input is an internal vuejs bind
      if (
        eventNames.indexOf(key) === -1 &&
        key !== "documentLoaded" &&
        key !== "input" &&
        key !== "internalComponentError"
      ) {
        /* eslint-disable no-console */
        console.error(
          `Cannot listen to "${key}". It is not a defined listener for ank-smart-element component`
        );
      }
    });

    /**
     * Add listener to update component values
     */
    this.smartElementWidget.addEventListener(
      "ready",
      {
        name: "v-on-dcpready-listen"
      },
      (event, documentObject) => {
        if (
          this.initid &&
          documentObject.initid.toString() !== this.initid.toString()
        ) {
          // @ts-ignore
          this.documentIsReady = true;
          this.$emit("update:props", documentObject);
        }
      }
    );
  }

  addEventListener(eventType, options, callback) {
    return this.smartElementWidget.addEventListener(
      eventType,
      options,
      callback
    );
  }

  fetchSmartElement(value, options?) {
    return this.smartElementWidget
      .fetchSmartElement(value, options)
      .catch(error => {
        let errorMessage = "Undefined error";
        if (error && error.errorMessage && error.errorMessage.contentText) {
          console.error(error.errorMessage.contentText);
          errorMessage = error.errorMessage.contentText;
        } else {
          console.error(error);
        }
        // @ts-ignore
        if (!this.documentIsReady) {
          this.$emit(
            "internalComponentError",
            {},
            {},
            { message: errorMessage }
          );
        }
        throw error;
      });
  }

  saveSmartElement(options) {
    return this.smartElementWidget.saveSmartElement(options);
  }

  showMessage(message) {
    return this.smartElementWidget.showMessage(message);
  }

  getAttributes() {
    return this.smartElementWidget.getAttributes();
  }

  getAttribute(attributeId) {
    return this.smartElementWidget.getAttribute(attributeId);
  }

  setValue(attributeId, newValue) {
    if (typeof newValue === "string") {
      /* eslint-disable no-param-reassign */
      newValue = {
        value: newValue,
        displayValue: newValue
      };
    }

    return this.smartElementWidget.setValue(attributeId, newValue);
  }

  reinitSmartElement(values, options) {
    return this.smartElementWidget.reinitSmartElement(values, options);
  }

  changeStateSmartElement(parameters, reinitOptions, options) {
    return this.smartElementWidget.changeStateSmartElement(
      parameters,
      reinitOptions,
      options
    );
  }

  deleteSmartElement(options) {
    return this.smartElementWidget.deleteSmartElement(options);
  }

  restoreSmartElement(options) {
    return this.smartElementWidget.restoreSmartElement(options);
  }

  getProperty(property) {
    return this.smartElementWidget.getProperty(property);
  }

  getProperties() {
    return this.smartElementWidget.getProperties();
  }

  hasAttribute(attributeId) {
    return this.smartElementWidget.hasAttribute(attributeId);
  }

  hasMenu(menuId) {
    return this.smartElementWidget.hasMenu(menuId);
  }

  getMenu(menuId) {
    return this.smartElementWidget.getMenu(menuId);
  }

  getMenus() {
    return this.smartElementWidget.getMenus();
  }

  getValue(attributeId, type) {
    return this.smartElementWidget.getValue(attributeId, type);
  }

  getValues() {
    return this.smartElementWidget.getValues();
  }

  getCustomServerData() {
    return this.smartElementWidget.getCustomServerData();
  }

  isModified() {
    return this.smartElementWidget.getProperty("isModified");
  }

  addCustomClientData(documentCheck, value) {
    return this.smartElementWidget.addCustomClientData(documentCheck, value);
  }

  getCustomClientData(deleteOnce) {
    return this.smartElementWidget.getCustomClientData(deleteOnce);
  }

  removeCustomClientData(key) {
    return this.smartElementWidget.removeCustomClientData(key);
  }

  appendArrayRow(attributeId, values) {
    return this.smartElementWidget.appendArrayRow(attributeId, values);
  }

  insertBeforeArrayRow(attributeId, values, index) {
    return this.smartElementWidget.insertBeforeArrayRow(
      attributeId,
      values,
      index
    );
  }

  removeArrayRow(attributeId, index) {
    return this.smartElementWidget.removeArrayRow(attributeId, index);
  }

  addConstraint(options, callback) {
    return this.smartElementWidget.addConstraint(options, callback);
  }

  listConstraints() {
    return this.smartElementWidget.listConstraints();
  }

  removeConstraint(constraintName, allKind) {
    return this.smartElementWidget.removeConstraint(constraintName, allKind);
  }

  listEventListeners() {
    return this.smartElementWidget.listEventListeners();
  }

  removeEventListener(eventName, allKind) {
    return this.smartElementWidget.removeEventListener(eventName, allKind);
  }

  triggerEvent(eventName, ...parameters) {
    return this.smartElementWidget.triggerEvent(eventName, ...parameters);
  }

  hideAttribute(attributeId) {
    return this.smartElementWidget.hideAttribute(attributeId);
  }

  showAttribute(attributeId) {
    return this.smartElementWidget.showAttribute(attributeId);
  }

  maskSmartElement(message, px) {
    return this.smartElementWidget.maskSmartElement(message, px);
  }

  unmaskSmartElement(force) {
    return this.smartElementWidget.unmaskSmartElement(force);
  }

  tryToDestroy() {
    return this.smartElementWidget.tryToDestroy();
  }

  injectJS(jsToInject) {
    return this.smartElementWidget.injectJS(jsToInject);
  }

  injectCSS(cssToInject) {
    return this.smartElementWidget.injectCSS(cssToInject);
  }
}
