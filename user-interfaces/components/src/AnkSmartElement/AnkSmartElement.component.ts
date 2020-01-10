/* eslint-disable no-unused-vars */
/**
 * Anakeen Smart Element component object
 */
import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import SmartElementController from "../../../src/vendor/Anakeen/DOCUMENT/IHM/widgets/globalController/SmartElementController";
import { AnakeenController } from "../../../src/vendor/Anakeen/DOCUMENT/IHM/widgets/globalController/types/ControllerTypes";
import AnakeenGlobalController from "../AnkController";
import { ISmartElementValue } from "./ISmartElementValue";
import EVENTS_LIST = AnakeenController.SmartElement.EVENTS_LIST;
import ListenableEventCallable = AnakeenController.BusEvents.ListenableEventCallable;
import ListenableEvent = AnakeenController.BusEvents.ListenableEvent;
import SmartElementEvent = AnakeenController.SmartElement.SmartElementEvent;

@Component({
  name: "ank-smart-element"
})
export default class AnkSmartElement extends Vue implements AnakeenController.SmartElement.ISmartElementAPI {
  get initialData() {
    const data: ISmartElementValue = {
      noRouter: !this.browserHistory
    };
    return {
      customClientData: this.customClientData || null,
      initid: this.initid || 0,
      noRouter: !this.browserHistory,
      revision: this.revision === undefined ? -1 : this.revision,
      viewId: this.viewId || "!defaultConsultation"
    };
  }

  @Prop({ type: Boolean, default: false }) public browserHistory!: boolean;
  @Prop({ type: [String, Number], default: 0 }) public initid!: string | number;
  @Prop({ type: Object, default: null }) public customClientData!: object;
  @Prop({ type: String, default: "!defaultConsultation" })
  public viewId!: string;
  @Prop({ type: Number, default: -1 }) public revision!: number;

  public smartElementWidget: SmartElementController = null;
  private controllerScopeId: string;

  get smartFieldValues(): any {
    return this.getValues();
  }

  public mounted() {
    if (this.initialData.initid.toString() !== "0") {
      this._initController(this.initialData);
    }
  }
  @Watch('initid')
  protected watchInitId() {
    this.updateComponent();
  }
  @Watch('viewId')
  protected watchViewId() {
    this.updateComponent();
  }
  @Watch('browserHistory')
  protected watchBrowserHistory() {
    this.updateComponent();
  }
  @Watch('customClientData', { deep: true })
  protected watchCustomClientData() {
    this.updateComponent();
  }
  @Watch('revision')
  protected watchRevision() {
    this.updateComponent();
  }

  protected updateComponent() {
    if (this.initialData.initid.toString() !== "0") {
      if (!this.isLoaded()) {
        this._initController(this.initialData);
      } else if (this.isLoaded()) {
        this.fetchSmartElement(this.initialData);
      }
    }
  }
  /**
   * True when internal widget is loaded
   * @returns {boolean}
   */
  public isLoaded() {
    return this.smartElementWidget !== null;
  }

  public addEventListener(
    eventType: SmartElementEvent | ListenableEvent,
    options?: object | ListenableEventCallable,
    callback?: ListenableEventCallable
  ) {
    const operation = () => this.smartElementWidget.addEventListener(eventType, options, callback);
    if (this.isLoaded()) {
      operation();
    } else {
      this.$once("smartElementLoaded", operation);
    }
  }

  public fetchSmartElement(value, options?) {
    if (!this.isLoaded()) {
      this._initController(value, options);
      return Promise.resolve();
    } else {
      return this.smartElementWidget.fetchSmartElement(value, options).catch(error => {
        let errorMessage = "Undefined error";
        if (error && error.errorMessage && error.errorMessage.contentText) {
          console.error(error.errorMessage.contentText);
          errorMessage = error.errorMessage.contentText;
        } else {
          console.error(error);
        }
        // @ts-ignore
        if (!this.documentIsReady) {
          this.$emit("internalComponentError", {}, {}, { message: errorMessage });
        }
        throw error;
      });
    }
  }

  public saveSmartElement(options) {
    return this.smartElementWidget.saveSmartElement(options);
  }

  public showMessage(message) {
    return this.smartElementWidget.showMessage(message);
  }

  public getSmartFields() {
    return this.smartElementWidget.getSmartFields();
  }

  public getSmartField(smartFieldId) {
    return this.smartElementWidget.getSmartField(smartFieldId);
  }

  public setValue(smartFieldId, newValue) {
    if (typeof newValue === "string") {
      /* eslint-disable no-param-reassign */
      newValue = {
        displayValue: newValue,
        value: newValue
      };
    }

    return this.smartElementWidget.setValue(smartFieldId, newValue);
  }

  public reinitSmartElement(values, options) {
    return this.smartElementWidget.reinitSmartElement(values, options);
  }

  public changeStateSmartElement(parameters, reinitOptions, options) {
    return this.smartElementWidget.changeStateSmartElement(parameters, reinitOptions, options);
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

  public hasSmartField(smartFieldId) {
    return this.smartElementWidget.hasSmartField(smartFieldId);
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

  public getValue(smartFieldId, type) {
    return this.smartElementWidget.getValue(smartFieldId, type);
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

  public addCustomClientData(check, value) {
    return this.smartElementWidget.addCustomClientData(check, value);
  }

  public getCustomClientData(deleteOnce) {
    return this.smartElementWidget.getCustomClientData(deleteOnce);
  }

  public removeCustomClientData(key) {
    return this.smartElementWidget.removeCustomClientData(key);
  }

  public appendArrayRow(smartFieldId, values) {
    return this.smartElementWidget.appendArrayRow(smartFieldId, values);
  }

  public insertBeforeArrayRow(smartFieldId, values, index) {
    return this.smartElementWidget.insertBeforeArrayRow(smartFieldId, values, index);
  }

  public removeArrayRow(smartFieldId, index) {
    return this.smartElementWidget.removeArrayRow(smartFieldId, index);
  }

  public addConstraint(options, callback) {
    const operation = () => this.smartElementWidget.addConstraint(options, callback);
    if (this.isLoaded()) {
      operation();
    } else {
      this.$once("smartElementLoaded", operation);
    }
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

  public hideSmartField(smartFieldId) {
    return this.smartElementWidget.hideSmartField(smartFieldId);
  }

  public showSmartField(smartFieldId) {
    return this.smartElementWidget.showSmartField(smartFieldId);
  }

  public maskSmartElement(message, px) {
    return this.smartElementWidget.maskSmartElement(message, px);
  }

  public unmaskSmartElement(force) {
    return this.smartElementWidget.unmaskSmartElement(force);
  }

  public tryToDestroy() {
    return this.smartElementWidget.tryToDestroy().then(() => {
      AnakeenGlobalController.removeSmartElement(this.controllerScopeId);
    });
  }

  public injectJS(jsToInject) {
    return this.smartElementWidget.injectJS(jsToInject);
  }

  public injectCSS(cssToInject) {
    return this.smartElementWidget.injectCSS(cssToInject);
  }

  public selectTab(tabId: any) {
    return this.smartElementWidget.selectTab(tabId);
  }

  public drawTab(tabId: any) {
    return this.smartElementWidget.drawTab(tabId);
  }

  public setCustomClientData(smartElementCheck: any, value: any) {
    return this.smartElementWidget.setCustomClientData(smartElementCheck, value);
  }

  public setSmartFieldErrorMessage(smartFieldId: any, message: any, index: any) {
    return this.smartElementWidget.setSmartFieldErrorMessage(smartFieldId, message, index);
  }

  public cleanSmartFieldErrorMessage(smartFieldId: any, index: any) {
    return this.smartElementWidget.cleanSmartFieldErrorMessage(smartFieldId, index);
  }

  protected _initController(viewData, options = {}) {
    this.controllerScopeId = AnakeenGlobalController.addSmartElement(
      // @ts-ignore
      this.$refs.ankSEWrapper,
      viewData,
      options
    );
    this.smartElementWidget = AnakeenGlobalController.getScopedController(
      this.controllerScopeId
    ) as SmartElementController;
    this.listenEvents();
    this.$emit("smartElementLoaded");
    this.$emit("documentLoaded");
  }

  protected listenEvents() {
    EVENTS_LIST.forEach(eventName => {
      this.addEventListener(eventName, (...args) => {
        this.$emit(eventName, ...args);
      });
    });
  }
}
