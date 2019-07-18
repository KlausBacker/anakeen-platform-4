/**
 * Anakeen Smart Element component object
 */
import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import VueSetup from "../setup.js";
import { SmartElementEvents } from "./SmartElementEvents";

interface ISmartElementValue {
  initid?: number | string;
  viewId?: string;
  revision?: number;
  customClientData?: object;
  noRouter?: boolean;
}

Vue.use(VueSetup);
@Component({
  name: "ank-smart-element"
})
export default class AnkSmartElement extends Vue {
  get initialData() {
    const data: ISmartElementValue = {
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
  @Prop({
    default: () => ({
      customClientData: null,
      initid: 0,
      revision: -1,
      viewId: "!defaultConsultation"
    }),
    type: Object,
    validator: value => {
      if (value.initid === undefined) {
        console.error("value property must contain a initid key");
        return false;
      }
      return true;
    }
  })
  public value!: ISmartElementValue;

  @Prop({ type: Boolean, default: false }) public browserHistory!: boolean;
  @Prop({ type: [String, Number], default: 0 }) public initid!: string | number;
  @Prop({ type: Object, default: null }) public customClientData!: object;
  @Prop({ type: String, default: "!defaultConsultation" })
  public viewId!: string;
  @Prop({ type: Number, default: -1 }) public revision!: number;

  public smartElementWidget: any = null;

  public updated() {
    this._initController(this.initialData);
    if (this.isLoaded()) {
      this.fetchSmartElement(this.initialData);
    }
  }

  /**
   * True when internal widget is loaded
   * @returns {boolean}
   */
  public isLoaded() {
    return this.smartElementWidget !== null;
  }

  /**
   * Rebind all declared binding to internal widget
   * @returns void
   */
  public listenAttributes() {
    const eventNames = SmartElementEvents;
    // @ts-ignore
    const localListener = this.$options._parentListeners || {};
    eventNames.forEach(eventName => {
      this.smartElementWidget.addEventListener(
        eventName,
        {
          name: `v-on-${eventName}-listen`
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

  public addEventListener(eventType, options, callback) {
    return this.smartElementWidget.addEventListener(
      eventType,
      options,
      callback
    );
  }

  public fetchSmartElement(value, options?) {
    this._initController(value);
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

  public saveSmartElement(options) {
    return this.smartElementWidget.saveSmartElement(options);
  }

  public showMessage(message) {
    return this.smartElementWidget.showMessage(message);
  }

  public getAttributes() {
    return this.smartElementWidget.getAttributes();
  }

  public getAttribute(attributeId) {
    return this.smartElementWidget.getAttribute(attributeId);
  }

  public setValue(attributeId, newValue) {
    if (typeof newValue === "string") {
      /* eslint-disable no-param-reassign */
      newValue = {
        displayValue: newValue,
        value: newValue
      };
    }

    return this.smartElementWidget.setValue(attributeId, newValue);
  }

  public reinitSmartElement(values, options) {
    return this.smartElementWidget.reinitSmartElement(values, options);
  }

  public changeStateSmartElement(parameters, reinitOptions, options) {
    return this.smartElementWidget.changeStateSmartElement(
      parameters,
      reinitOptions,
      options
    );
  }

  public deleteSmartElement(options) {
    return this.smartElementWidget.deleteSmartElement(options);
  }

  public restoreSmartElement(options) {
    return this.smartElementWidget.restoreSmartElement(options);
  }

  public getProperty(property) {
    return this.smartElementWidget.getProperty(property);
  }

  public getProperties() {
    return this.smartElementWidget.getProperties();
  }

  public hasAttribute(attributeId) {
    return this.smartElementWidget.hasAttribute(attributeId);
  }

  public hasMenu(menuId) {
    return this.smartElementWidget.hasMenu(menuId);
  }

  public getMenu(menuId) {
    return this.smartElementWidget.getMenu(menuId);
  }

  public getMenus() {
    return this.smartElementWidget.getMenus();
  }

  public getValue(attributeId, type) {
    return this.smartElementWidget.getValue(attributeId, type);
  }

  public getValues() {
    return this.smartElementWidget.getValues();
  }

  public getCustomServerData() {
    return this.smartElementWidget.getCustomServerData();
  }

  public isModified() {
    return this.smartElementWidget.getProperty("isModified");
  }

  public addCustomClientData(documentCheck, value) {
    return this.smartElementWidget.addCustomClientData(documentCheck, value);
  }

  public getCustomClientData(deleteOnce) {
    return this.smartElementWidget.getCustomClientData(deleteOnce);
  }

  public removeCustomClientData(key) {
    return this.smartElementWidget.removeCustomClientData(key);
  }

  public appendArrayRow(attributeId, values) {
    return this.smartElementWidget.appendArrayRow(attributeId, values);
  }

  public insertBeforeArrayRow(attributeId, values, index) {
    return this.smartElementWidget.insertBeforeArrayRow(
      attributeId,
      values,
      index
    );
  }

  public removeArrayRow(attributeId, index) {
    return this.smartElementWidget.removeArrayRow(attributeId, index);
  }

  public addConstraint(options, callback) {
    return this.smartElementWidget.addConstraint(options, callback);
  }

  public listConstraints() {
    return this.smartElementWidget.listConstraints();
  }

  public removeConstraint(constraintName, allKind) {
    return this.smartElementWidget.removeConstraint(constraintName, allKind);
  }

  public listEventListeners() {
    return this.smartElementWidget.listEventListeners();
  }

  public removeEventListener(eventName, allKind) {
    return this.smartElementWidget.removeEventListener(eventName, allKind);
  }

  public triggerEvent(eventName, ...parameters) {
    return this.smartElementWidget.triggerEvent(eventName, ...parameters);
  }

  public hideAttribute(attributeId) {
    return this.smartElementWidget.hideAttribute(attributeId);
  }

  public showAttribute(attributeId) {
    return this.smartElementWidget.showAttribute(attributeId);
  }

  public maskSmartElement(message, px) {
    return this.smartElementWidget.maskSmartElement(message, px);
  }

  public unmaskSmartElement(force) {
    return this.smartElementWidget.unmaskSmartElement(force);
  }

  public tryToDestroy() {
    return this.smartElementWidget.tryToDestroy();
  }

  public injectJS(jsToInject) {
    return this.smartElementWidget.injectJS(jsToInject);
  }

  public injectCSS(cssToInject) {
    return this.smartElementWidget.injectCSS(cssToInject);
  }

  protected _initController(viewData) {
    if (!this.isLoaded() && viewData && viewData.initid !== 0) {
      if (
        window.ank &&
        window.ank.smartElement &&
        window.ank.smartElement.globalController
      ) {
        const scopeId = window.ank.smartElement.globalController.addSmartElement(
          // @ts-ignore
          this.$refs.ankSEWrapper,
          viewData
        );
        this.smartElementWidget = window.ank.smartElement.globalController.scope(
          scopeId
        );
        this.$emit("documentLoaded");
        this.listenAttributes();
      }
    }
  }
}
