/* eslint-disable no-unused-vars */
/* tslint:disable:variable-name ordered-imports */
// @ts-ignore
import StaticErrorTemplate from "!!raw-loader!./utils/templates/SEError.html.mustache";
import { AnakeenController } from "./types/ControllerTypes";
import ListenableEvent = AnakeenController.BusEvents.ListenableEvent;
import ISmartElementAPI = AnakeenController.SmartElement.ISmartElementAPI;
import ISmartField = AnakeenController.SmartElement.ISmartField;
import ISmartElement = AnakeenController.SmartElement.ISmartElement;
import Listenable = AnakeenController.BusEvents.Listenable;
// @ts-ignore
import LoadingTemplate from "!!raw-loader!./utils/templates/SELoading.html.mustache";
import * as Backbone from "backbone";
// @ts-ignore
import AttributeInterface = require("dcpDocument/controllerObjects/attributeInterface");
// @ts-ignore
import MenuInterface = require("dcpDocument/controllerObjects/menuInterface");
// @ts-ignore
import TransitionInterface = require("dcpDocument/controllerObjects/transitionInterface");
// @ts-ignore
import i18n = require("dcpDocument/i18n/documentCatalog");
// @ts-ignore
import Model = require("dcpDocument/models/mDocument");
// @ts-ignore
import MenuModel = require("dcpDocument/models/mMenu");
// @ts-ignore
import TransitionModel = require("dcpDocument/models/mTransition");
// @ts-ignore
import Router = require("dcpDocument/routers/router.js");
// @ts-ignore
import View = require("dcpDocument/views/document/vDocument");
// @ts-ignore
import TransitionView = require("dcpDocument/views/workflow/vTransition");
import "dcpDocument/widgets/widget";
import "dcpDocument/widgets/window/wConfirm";
import "dcpDocument/widgets/window/wLoading";
import "dcpDocument/widgets/window/wNotification";
import SmartElementProperties = AnakeenController.Types.SmartElementProperties;
import ViewData = AnakeenController.Types.IViewData;
import DOMReference = AnakeenController.Types.DOMReference;
import ListenableEventCallable = AnakeenController.BusEvents.ListenableEventCallable;
import ListenableEventOptions = AnakeenController.BusEvents.IListenableEventOptions;
import IControllerOptions = AnakeenController.Types.IControllerOptions;
import * as $ from "jquery";
import * as Mustache from "mustache";
import * as _ from "underscore";
import SmartElementEvent = AnakeenController.SmartElement.SmartElementEvent;

const DEFAULT_OPTIONS: IControllerOptions = {
  controllerPrefix: "smart-element-controller-",
  customClientData: {},
  loading: true,
  notification: true,
  router: false
};

class ErrorModelNonInitialized extends Error {
  constructor(message?) {
    super();
    this.message = message || "The model is not initialized, use fetchSmartElement to initialise it.";
    this.name = "ErrorModelNonInitialized";
    this.stack = new Error().stack;
  }
}

interface ISmartElementModel extends Backbone.Model {
  _customClientData: {};
  _formConfiguration: null;
  _customRequestData: {};

  getModelProperties(): SmartElementProperties;

  fetchDocument(viewData?: ViewData, options?: any): Promise<any>;

  hasUploadingFile(): boolean;

  saveDocument(): Promise<any>;

  restoreDocument(): Promise<any>;

  deleteDocument(): Promise<any>;

  isModified(): boolean;

  getServerProperties(): any;

  getValues(): any;

  injectJS(jsToInject: string[]): Promise<any>;

  injectCSS(cssToInject: string[]): Promise<any>;
}

// tslint:disable-next-line:max-classes-per-file
export default class SmartElementController extends AnakeenController.BusEvents.Listenable implements ISmartElementAPI {
  public uid: string;
  public $: (selector: string) => JQuery = null;

  protected _registeredListeners: Listenable = new Listenable();
  protected _element: JQuery<DOMReference>;
  protected _smartElement: JQuery<DOMReference>;
  protected _view: Backbone.View;
  protected _model: ISmartElementModel;
  protected _router: Backbone.Router;
  protected _initialized: { model: boolean; view: boolean } = {
    model: false,
    view: false
  };
  protected _customClientData: {} = {};
  protected _internalViewData: ViewData = {
    initid: 0,
    revision: -1,
    viewId: "!defaultConsultation"
  };
  protected _requestData: ViewData;
  protected _options: IControllerOptions = {};
  protected $loading: JQuery & { dcpLoading(...args): JQuery };
  protected $notification: JQuery & { dcpNotification(...args): JQuery };
  protected _globalEventHandler: (...args: any[]) => any;
  protected _defaultPersistent: boolean = true;

  constructor(dom: DOMReference, viewData: ViewData, options?: IControllerOptions, globalEventHandler?) {
    super();
    this._options = _.defaults(options, DEFAULT_OPTIONS);
    this._generateUID(this._options);
    this._globalEventHandler = globalEventHandler;
    if (viewData) {
      this._internalViewData.initid = viewData.initid;
      this._internalViewData.viewId = viewData.viewId;
      this._internalViewData.revision = viewData.revision;
      if (viewData.customClientData && Object.keys(viewData.customClientData).length) {
        this._options.customClientData = viewData.customClientData;
      }
      this._requestData = _.defaults(Object.assign({}, this._internalViewData), {
        initid: 0,
        revision: -1,
        viewId: "!defaultConsultation"
      });
    }
    // @ts-ignore
    this._element = $(dom);
    this.$ = selector => $(selector, this._element);
    this._initialized = {
      model: false,
      view: false
    };
    if (!this._internalViewData.initid) {
      return;
    }
    // noinspection JSIgnoredPromiseFromCall
    this._initializeSmartElement({}, this._options);
  }

  /***************************************************************************************************************
   * External function
   **************************************************************************************************************/
  /**
   * Reinit the current document (close it and re-open it) : keep the same view, revision, etc...
   *
   * @param values object {"initid" : int, "revision" : int, "viewId" : string, "customClientData" : mixed}
   * @param options object {"success": fct, "error", fct}
   */
  public reinitSmartElement(values, options?) {
    const properties = this.getProperties();
    this.checkInitialisedModel();
    values = values || {};

    // Reinit model with server values
    _.defaults(values, {
      initid: properties.initid,
      revision: properties.revision,
      viewId: properties.viewId
    });

    return this.fetchSmartElement(values, options);
  }

  /**
   * Fetch a new smart element
   * @param values object {"initid" : int, "revision" : int, "viewId" : string, "customClientData" : mixed}
   * @param options object {"success": fct, "error", fct}
   */
  public fetchSmartElement(values, options) {
    let documentPromise;
    values = _.isUndefined(values) ? {} : values;
    options = options || {};
    if (!_.isObject(values)) {
      throw new Error('Fetch argument must be an object {"initid":, "revision": , "viewId": }');
    }

    if (!values.initid) {
      throw new Error("initid argument is mandatory");
    }

    if (!isNaN(values.initid)) {
      // Convert to numeric initid is possible
      values.initid = parseInt(values.initid, 10);
    }

    // Use default values when fetch another document
    _.defaults(values, { revision: -1, viewId: "!defaultConsultation" });
    _.defaults(options, { force: false });

    _.each(_.pick(values, "initid", "revision", "viewId"), (value, key) => {
      this._internalViewData[key] = value;
      this._requestData[key] = value;
    });
    try {
      if (!this._model) {
        const config: any = {
          customClientData: values.customClientData
        };
        if (options.formConfiguration) {
          config.formConfiguration = options.formConfiguration;
        }
        documentPromise = this._initializeSmartElement(options, config);
      } else {
        if (values.customClientData) {
          this._model._customClientData = values.customClientData;
        }
        if (options.formConfiguration) {
          this._model._formConfiguration = options.formConfiguration;
        }
        documentPromise = this._model.fetchDocument(this._getModelValue(), options);
      }
    } catch (e) {
      if (documentPromise) {
        documentPromise.reject(e);
      }
    }
    return this._registerOutputPromise(documentPromise, options);
  }

  /**
   * Save the current document
   * Reload the interface in the same mode
   * @param options object {"success": fct, "error", fct, "customClientData" : mixed}
   *
   */
  public saveSmartElement(options) {
    let documentPromise;
    options = options || {};
    this.checkInitialisedModel();
    if (options.customClientData) {
      this._model._customClientData = options.customClientData;
    }
    documentPromise = this._model.saveDocument();
    return this._registerOutputPromise(documentPromise, options);
  }

  /**
   * Change the workflow state of the document
   *
   * @param parameters
   * @param reinitOptions
   * @param options
   */
  public changeStateSmartElement(parameters, reinitOptions, options) {
    let documentPromise;
    this.checkInitialisedModel();
    if (!_.isObject(parameters)) {
      throw new Error(
        'changeStateDocument first argument must be an object {"nextState":, "transition": , "values":, "unattended":, "" }'
      );
    }
    if (!_.isString(parameters.nextState) || !_.isString(parameters.transition)) {
      throw new Error("nextState and transition arguments are mandatory");
    }
    documentPromise = this._initAndDisplayTransition(
      parameters.nextState,
      parameters.transition,
      parameters.values || null,
      parameters.unattended || false,
      parameters.transitionElementsCallBack || false,
      reinitOptions
    );
    return this._registerOutputPromise(documentPromise, options);
  }

  /**
   * Delete the current document
   * Reload the interface in the same mode
   * @param options object {"success": fct, "error", fct, "customClientData" : mixed}
   */
  public deleteSmartElement(options) {
    let documentPromise;
    options = options || {};
    this.checkInitialisedModel();
    if (options.customClientData) {
      this._model._customClientData = options.customClientData;
    }
    documentPromise = this._model.deleteDocument();
    return this._registerOutputPromise(documentPromise, options);
  }

  /**
   * Restore the current document
   * Reload the interface in the same mode
   * @param options object {"success": fct, "error", fct, "customClientData" : mixed}
   */
  public restoreSmartElement(options) {
    let documentPromise;
    options = options || {};
    this.checkInitialisedModel();
    if (options.customClientData) {
      this._model._customClientData = options.customClientData;
    }
    documentPromise = this._model.restoreDocument();
    return this._registerOutputPromise(documentPromise, options);
  }

  /**
   * Get a property value
   *
   * @param property
   * @returns {*}
   */
  public getProperty(property) {
    this.checkInitialisedModel();
    if (property === "isModified") {
      return this._model.isModified();
    }
    return this._model.getServerProperties()[property];
  }

  /**
   * Get all the properties
   * @returns {*}
   */
  public getProperties(): ISmartElement {
    let properties;
    let ready = true;
    try {
      this.checkInitialisedModel();
    } catch (e) {
      ready = false;
      properties = {
        notLoaded: true
      };
    }
    if (ready) {
      properties = this._model.getServerProperties();
      properties.isModified = this._model.isModified();
      properties.url = this._model.url();
      properties.pageUrl = this._computeUrl(properties);
    }

    return properties as ISmartElement;
  }

  /**
   * Check if an attribute exist
   *
   * @param smartField
   * @return {boolean}
   */
  public hasSmartField(smartField) {
    this.checkInitialisedModel();
    const attribute = this._model.get("attributes").get(smartField);
    return !!attribute;
  }

  /**
   * Get the attribute interface object
   * Return null if attribute not found
   * @param smartField
   * @returns AttributeInterface|null
   */
  public getSmartField(smartField): ISmartField {
    this.checkInitialisedModel();
    const attributeModel = this._getAttributeModel(smartField);
    if (!attributeModel) {
      return null;
    }
    return new AttributeInterface(this._getAttributeModel(smartField)) as ISmartField;
  }

  /**
   * Get all the attributes of the current document
   *
   * @returns [AttributeInterface]
   */
  public getSmartFields() {
    this.checkInitialisedModel();
    return this._model.get("attributes").map(currentAttribute => {
      return new AttributeInterface(currentAttribute);
    });
  }

  /**
   * Check if a menu exist
   *
   * @param menuId
   * @return {boolean}
   */
  public hasMenu(menuId) {
    this.checkInitialisedModel();
    const menu = this._getMenuModel(menuId);
    return !!menu;
  }

  /**
   * Get the menu interface object
   *
   * @param menuId
   * @returns MenuInterface
   */
  public getMenu(menuId) {
    this.checkInitialisedModel();
    const menu = this._getMenuModel(menuId);
    if (!menu) {
      return null;
    }
    return new MenuInterface(menu);
  }

  /**
   * Get all the menu of the current document
   *
   * @returns [MenuInterface]
   */
  public getMenus() {
    this.checkInitialisedModel();
    return this._model.get("menus").map(currentMenu => {
      return new MenuInterface(currentMenu);
    });
  }

  /**
   * Select a tab
   *
   * @param tabId
   * @returns void
   */
  public selectTab(tabId) {
    this.checkInitialisedModel();
    const attributeModel = this._getAttributeModel(tabId);
    if (!attributeModel) {
      throw new Error('The attribute "' + tabId + '" cannot be found.');
    }
    if (attributeModel.get("type") !== "tab") {
      throw new Error('The attribute "' + tabId + '" is not a tab.');
    }

    this._model.trigger("doSelectTab", tabId);
  }

  /**
   * Draw tab content
   *
   * @param tabId
   * @returns void
   */
  public drawTab(tabId) {
    this.checkInitialisedModel();
    const attributeModel = this._getAttributeModel(tabId);
    if (!attributeModel) {
      throw new Error('The attribute "' + tabId + '" cannot be found.');
    }
    if (attributeModel.get("type") !== "tab") {
      throw new Error('The attribute "' + tabId + '" is not a tab.');
    }

    this._model.trigger("doDrawTab", tabId);
  }

  /**
   * Get an attribute value
   *
   * @param smartFieldId
   * @param type string (current|previous|initial|all) what kind of value (default : current)
   * @returns {*}
   */
  public getValue(smartFieldId, type) {
    let attribute;
    this.checkInitialisedModel();
    const attributeModel = this._getAttributeModel(smartFieldId);
    if (!attributeModel) {
      return null;
    }
    attribute = new AttributeInterface(attributeModel);
    return _.clone(attribute.getValue(type));
  }

  /**
   * Get all the values
   *
   * @returns {*|{}}
   */
  public getValues() {
    this.checkInitialisedModel();
    return this._model.getValues();
  }

  /**
   * Get customData from render view model
   * @returns {*}
   */
  public getCustomServerData() {
    this.checkInitialisedModel();
    return this._model.get("customServerData");
  }

  /**
   * Add customData from render view model
   * @returns {*}
   */
  public addCustomClientData(check, value) {
    this.checkInitialisedModel();
    // First case no data, so check is data
    if (_.isUndefined(value)) {
      value = check;
      check = {};
    }
    // Second case check is a function and data is object
    if (_.isFunction(check) && _.isObject(value)) {
      check = { check };
    }
    // Third case check is an object and data is object => check if check property exist
    if (_.isObject(value) && _.isObject(check)) {
      check = _.defaults(check, {
        check: () => {
          return true;
        },
        once: true
      });
    } else {
      throw new Error("Constraint must be an value or a function and a value");
    }
    // Register the customClientData
    _.each(value, (currentValue, currentKey) => {
      this._customClientData[currentKey] = {
        check: check.check,
        once: check.once,
        value: currentValue
      };
    });
  }

  /**
   * Get customData from render view model
   * @returns {*}
   */
  public setCustomClientData(check, value) {
    console.error("this function (setCustomClientData) is deprecated");
    return this.addCustomClientData(check, value);
  }

  /**
   * Get customData from render view model
   * @returns {*}
   */
  public getCustomClientData(deleteOnce) {
    const values = {};
    let properties;
    const newCustomData = {};
    this.checkInitialisedModel();
    properties = this.getProperties();
    _.each(this._customClientData, (currentCustom: any, key) => {
      if (currentCustom.check.call(this, properties)) {
        values[key] = currentCustom.value;
        if (deleteOnce === true && !currentCustom.once) {
          newCustomData[key] = currentCustom;
        }
      } else {
        if (deleteOnce === true) {
          newCustomData[key] = currentCustom;
        }
      }
    });
    if (deleteOnce === true) {
      this._customClientData = newCustomData;
    }
    return values;
  }

  /**
   * Delete a custom data
   * @returns {*}
   */
  public removeCustomClientData(key) {
    if (this._customClientData[key]) {
      delete this._customClientData[key];
    }
    return this;
  }

  /**
   * Set a value
   * Trigger a change event
   *
   * @param smartFieldId string attribute identifier
   * @param value object { "value" : *, "displayValue" : *}
   * @returns {*}
   */
  public setValue(smartFieldId, value) {
    this.checkInitialisedModel();
    const attributeModel = this._getAttributeModel(smartFieldId);
    if (!attributeModel) {
      throw new Error("Unable to find smart field " + smartFieldId);
    }
    const attributeInterface = new AttributeInterface(attributeModel);
    let index;
    let currentValueLength;
    let i;

    if (attributeModel.getParent().get("type") === "array") {
      attributeInterface.setValue(value, true); // Just verify value conditions
      if (!_.isArray(value)) {
        index = value.index;
      } else {
        index = value.length - 1;
      }
      currentValueLength = attributeInterface.getValue().length;
      attributeInterface.setValue(value);

      _.defer(() => {
        // Pad values of complete array with default values
        const arrayModel = attributeModel.getParent();
        const modifiedColumns = {};
        arrayModel.get("content").each(aModel => {
          const aValue = _.clone(aModel.get("attributeValue"));
          let defaultValue = aModel.get("defaultValue");

          if (!defaultValue) {
            defaultValue = aModel.hasMultipleOption() ? [] : { value: null, displayValue: "" };
          }

          for (i = currentValueLength; i <= index; i++) {
            if (_.isUndefined(aValue[i])) {
              aValue[i] = defaultValue;
              modifiedColumns[aModel.id] = { model: aModel, values: aValue };
            }
          }
        });

        _.each(modifiedColumns, (modData: any) => {
          modData.model.set("attributeValue", modData.values);
        });
      });

      return;
    }
    return attributeInterface.setValue(value);
  }

  /**
   * Add a row to an array
   *
   * @param smartFieldId string attribute array
   * @param values object { "attributeId" : { "value" : *, "displayValue" : * }, ...}
   */
  public appendArrayRow(smartFieldId, values) {
    this.checkInitialisedModel();
    const attribute = this._getAttributeModel(smartFieldId);

    if (!attribute) {
      throw new Error("Unable to find attribute " + smartFieldId);
    }

    if (attribute.get("type") !== "array") {
      throw new Error("Smart Field " + smartFieldId + " must be an attribute of type array");
    }
    if (!_.isObject(values)) {
      throw new Error(
        "Values must be an object where each properties is an smart field of the array for " + smartFieldId
      );
    }
    attribute.get("content").each(currentAttribute => {
      let newValue = values[currentAttribute.id];
      const currentValue = currentAttribute.getValue();
      if (_.isUndefined(newValue)) {
        // Set default value if no value defined
        currentAttribute.createIndexedValue(currentValue.length, false, _.isEmpty(values));
      } else {
        newValue = _.defaults(newValue, {
          displayValue: newValue.value,
          value: ""
        });
        currentAttribute.addValue(newValue);
      }
    });
  }

  /**
   * Add a row before another row
   *
   * @param smartFieldId string attribute array
   * @param values object { "attributeId" : { "value" : *, "displayValue" : * }, ...}
   * @param index int index of the row
   */
  public insertBeforeArrayRow(smartFieldId, values, index) {
    this.checkInitialisedModel();
    const attribute = this._getAttributeModel(smartFieldId);
    let maxValue;
    if (!attribute) {
      throw new Error("Unable to find attribute " + smartFieldId);
    }
    if (attribute.get("type") !== "array") {
      throw new Error("Smart Field " + smartFieldId + " must be an attribute of type array");
    }
    if (!_.isObject(values)) {
      throw new Error(
        "Values must be an object where each properties is an smart field of the array for " + smartFieldId
      );
    }
    maxValue = this._getMaxIndex(attribute);
    if (index < 0 || index > maxValue) {
      throw new Error("Index must be between 0 and " + maxValue);
    }
    attribute.get("content").each(currentAttribute => {
      let currentValue = values[currentAttribute.id];
      if (!_.isUndefined(currentValue)) {
        currentValue = _.defaults(currentValue, {
          displayValue: currentValue.value,
          value: ""
        });
      } else {
        currentValue = currentAttribute.attributes.defaultValue;
        if (!currentValue) {
          currentValue = { value: "", displayValue: "" };
        }
      }
      currentAttribute.addIndexedValue(currentValue, index);
    });
  }

  /**
   * Remove an array row
   * @param smartFieldId string attribute array
   * @param index int index of the row
   */
  public removeArrayRow(smartFieldId, index) {
    this.checkInitialisedModel();
    const attribute = this._getAttributeModel(smartFieldId);
    let maxIndex;
    if (!attribute) {
      throw new Error("Unable to find smart field " + smartFieldId);
    }
    if (attribute.get("type") !== "array") {
      throw Error("Smart field " + smartFieldId + " must be a smart field of type array");
    }
    maxIndex = this._getMaxIndex(attribute) - 1;
    if (index < 0 || index > maxIndex) {
      throw Error("Index must be between 0 and " + maxIndex + " for " + smartFieldId);
    }
    attribute.get("content").each(currentAttribute => {
      currentAttribute.removeIndexValue(index);
    });
    attribute.removeIndexedLine(index);
  }

  /**
   * Add a constraint to the widget
   *
   * @param options object { "name" : string, "documentCheck": function}
   * @param callback function callback called when the event is triggered
   * @returns {*}
   */
  public addConstraint(options, callback) {
    return this.addEventListener("smartFieldConstraintCheck", options, callback);
  }

  /**
   * List the constraint of the widget
   *
   * @returns {*}
   */
  public listConstraints() {
    let constraints = this._registeredListeners.getEventsList().smartFieldConstraintCheck || [];
    constraints = constraints.concat(this.getEventsList().smartFieldConstraintCheck || []);
    return constraints;
  }

  /**
   * Remove a constraint of the widget
   *
   * @param constraintName
   * @param allKind
   * @returns {*}
   */
  public removeConstraint(constraintName, allKind) {
    return this.removeEventListener(constraintName, allKind);
  }

  /**
   * Add an event to the widget
   *
   * @param eventType string kind of event
   * @param options object { "name" : string, "check": function}
   * @param callback function callback called when the event is triggered
   * @returns {*|Window.options.name}
   */
  public addEventListener(
    eventType: SmartElementEvent | ListenableEvent,
    options?: object | ListenableEventCallable,
    callback?: ListenableEventCallable
  ) {
    let currentEvent;
    let eventCallback = callback;
    let eventOptions = options;
    // options is not mandatory and the callback can be the second parameters
    if (_.isUndefined(eventCallback) && _.isFunction(eventOptions)) {
      eventCallback = eventOptions;
      eventOptions = {};
    }

    // the first parameters can be the final object (chain removeEvent and addEvent)
    if (_.isObject(eventType) && _.isUndefined(eventOptions) && _.isUndefined(eventCallback)) {
      currentEvent = eventType;
      currentEvent._persistent = this._defaultPersistent;
      if (!currentEvent.name) {
        throw new Error(
          "When an event is initiated with a single object, this object needs to have the name property " +
            JSON.stringify(currentEvent)
        );
      }
    } else {
      currentEvent = _.defaults(eventOptions, {
        eventCallback,
        eventType,
        name: _.uniqueId("event_" + eventType),
        once: false
      });
      currentEvent._persistent = this._defaultPersistent;
    }
    // the eventType must be one the list
    this.checkEventName(currentEvent.eventType);
    // callback is mandatory and must be a function
    if (!_.isFunction(currentEvent.eventCallback)) {
      throw new Error("An event needs a callback that is a function");
    }
    this._addAndInitNewEvents(currentEvent);
    // return the name of the event
    return currentEvent.name;
  }

  /**
   * List of the events of the current widget
   *
   * @returns {*}
   */
  public listEventListeners() {
    let allEvents = [];
    Object.keys(this._registeredListeners.getEventsList()).forEach(eventType => {
      allEvents = allEvents.concat(this._registeredListeners.getEventsList()[eventType]);
    });
    Object.keys(this.getEventsList()).forEach(eventType => {
      allEvents = allEvents.concat(this.getEventsList()[eventType]);
    });
    return allEvents;
  }

  /**
   * Remove an event of the current widget
   *
   * @param eventName string can be an event name or a namespace
   * @param allKind remove internal/external events
   * @returns {*}
   */
  public removeEventListener(eventName, allKind) {
    let removed = [];
    const testRegExp = new RegExp("\\" + eventName + "$");
    allKind = !!allKind;
    Object.keys(this.getEventsList()).forEach(eventType => {
      removed = removed.concat(
        this.getEventsList()[eventType].filter(currentEvent => {
          return (
            (allKind || !currentEvent._persistent) &&
            (currentEvent.name === eventName || testRegExp.test(currentEvent.name))
          );
        })
      );
    });
    removed.forEach(event => {
      this.off(event.eventType, event.eventCallback);
    });
    let persistentRemoved = [];
    if (allKind) {
      Object.keys(this.getEventsList()).forEach(eventType => {
        persistentRemoved = persistentRemoved.concat(
          this._registeredListeners.getEventsList()[eventType].filter(currentEvent => {
            return currentEvent.name === eventName || testRegExp.test(currentEvent.name);
          })
        );
      });
      persistentRemoved.forEach(event => {
        this._registeredListeners.off(event.eventType, event.eventCallback);
      });
    }

    return persistentRemoved.concat(removed);
  }

  /**
   * Trigger an event
   *
   * @param eventName
   * @param args
   */
  public triggerEvent(eventName, ...args) {
    this.checkInitialisedModel();
    this.checkEventName(eventName);
    return this._triggerControllerEvent(eventName, null, ...args);
  }

  /**
   * Hide a visible attribute
   *
   * @param smartFieldId
   */
  public hideSmartField(smartFieldId) {
    this.checkInitialisedView();
    const attributeModel = this._getAttributeModel(smartFieldId);
    if (!attributeModel) {
      console.error("Unable find and hide the attribute " + smartFieldId);
      return;
    }
    attributeModel.trigger("hide");
  }

  /**
   * show a visible attribute (previously hidden)
   *
   * @param smartFieldId
   */
  public showSmartField(smartFieldId) {
    this.checkInitialisedView();
    const attributeModel = this._getAttributeModel(smartFieldId);
    if (!attributeModel) {
      console.error("Unable find and show the attribute " + smartFieldId);
      return;
    }
    attributeModel.trigger("show");
  }

  /**
   * Display a message to the user
   *
   * @param message
   */
  public showMessage(message) {
    this.checkInitialisedView();
    if (_.isString(message)) {
      message = {
        message,
        type: "info"
      };
    }
    if (_.isObject(message)) {
      message = _.defaults(message, {
        type: "info"
      });
    }
    this.$notification.dcpNotification("show", message.type, message);
  }

  /**
   * Display loading bar
   *
   * @param message
   * @param px
   */
  public maskSmartElement(message, px) {
    this.$loading.dcpLoading("show");
    if (message) {
      this.$loading.dcpLoading("setTitle", message);
    }
    if (px) {
      this.$loading.dcpLoading("setPercent", px);
    }
  }

  /**
   * Hide loading bar
   */
  public unmaskSmartElement(force) {
    this.$loading.dcpLoading("hide", force);
  }

  /**
   * Add an error message to an attribute
   *
   * @param smartFieldId
   * @param message
   * @param index
   */
  public setSmartFieldErrorMessage(smartFieldId, message, index) {
    this.checkInitialisedView();
    const attributeModel = this._getAttributeModel(smartFieldId);
    if (!attributeModel) {
      console.error("Unable find and show the smart field " + smartFieldId);
      return;
    }
    attributeModel.setErrorMessage(message, index);
  }

  /**
   * Clean the error message of an attribute
   *
   * @param smartFieldId
   * @param index
   */
  public cleanSmartFieldErrorMessage(smartFieldId, index) {
    this.checkInitialisedView();
    const attributeModel = this._getAttributeModel(smartFieldId);
    if (!attributeModel) {
      console.error("Unable find and show the smart field " + smartFieldId);
      return;
    }
    attributeModel.setErrorMessage(null, index);
  }

  public injectCSS(cssToInject) {
    this.checkInitialisedView();
    if (!_.isArray(cssToInject) && !_.isString(cssToInject)) {
      throw new Error("The css to inject must be an array string or a string");
    }
    if (_.isString(cssToInject)) {
      cssToInject = [cssToInject];
    }

    this._model.injectCSS(cssToInject);
  }

  public injectJS(jsToInject) {
    this.checkInitialisedView();
    if (!_.isArray(jsToInject) && !_.isString(jsToInject)) {
      throw new Error("The js to inject must be an array string or a string");
    }
    if (_.isString(jsToInject)) {
      jsToInject = [jsToInject];
    }

    return this._model.injectJS(jsToInject);
  }

  /**
   * tryToDestroy the widget
   *
   * @return Promise
   */
  public tryToDestroy() {
    return new Promise((resolve, reject) => {
      if (!this._model) {
        resolve();
        return;
      }
      if (
        this._model &&
        this._model.isModified() &&
        !window.confirm(
          this._model.get("properties").get("title") +
            "\n" +
            i18n.___("The form has been modified without saving, do you want to close it ?", "ddui")
        )
      ) {
        reject("Unable to destroy because user refuses it");
        return;
      }

      this._triggerControllerEvent("beforeClose", null, this._model.getModelProperties())
        .then(() => {
          this._model.trigger("destroy");
          resolve();
        })
        .catch(err => {
          reject("Unable to destroy because before close refuses it : " + err);
        });
    });
  }

  public emit(eventOptions: { name: string; type: "smartElement" | "smartField" | "constraint" }, ...args: any[]) {
    const name = eventOptions.name;
    const type = eventOptions.type || "smartElement";
    if (_.isFunction(this._globalEventHandler)) {
      this._globalEventHandler(name, ...args);
    }
    const persistentEvents = this._registeredListeners.getEventsList()[name] || [];
    const tempEvents = this.getEventsList()[name] || [];
    const allEvents = persistentEvents.concat(tempEvents);
    if (!allEvents.length) {
      return Promise.resolve();
    }
    return Promise.all(
      allEvents
        .filter(
          currentEvent => !_.isFunction(currentEvent.check) || currentEvent.check.call(this, this.getProperties())
        )
        .filter(
          currentEvent =>
            type !== "smartField" ||
            !_.isFunction(currentEvent.smartFieldCheck) ||
            currentEvent.smartFieldCheck.call(this, args[2], this.getProperties())
        )
        .map(cb => {
          let callbackReturn: any;
          if (cb.eventType === "smartFieldConstraintCheck") {
            const constraintOptions = args[args.length - 1];
            callbackReturn = cb.eventCallback.call(this, args[1], args[2], args[3]);
            if (Array.isArray(callbackReturn)) {
              callbackReturn.forEach(constraintOptions.displayConstraint);
            } else {
              constraintOptions.displayConstraint(callbackReturn);
            }
          } else {
            callbackReturn = cb.eventCallback.call(this, ...args);
            if (callbackReturn && callbackReturn instanceof Promise && name.indexOf("before") === 0) {
              return callbackReturn;
            } else {
              if (
                (args[0] && typeof args[0].isDefaultPrevented === "function" && args[0].isDefaultPrevented()) ||
                callbackReturn === false
              ) {
                return Promise.reject(callbackReturn);
              }
              return Promise.resolve(callbackReturn);
            }
          }
        })
    );
  }

  private _generateUID(options?: IControllerOptions) {
    if (options && options.controllerName) {
      this.uid = options.controllerName;
    } else if (options && options.controllerPrefix) {
      this.uid = _.uniqueId(options.controllerPrefix);
    } else {
      this.uid = _.uniqueId("smart-element-controller-");
    }
  }

  private _reinitListeners() {
    return this.offAll();
  }

  private _initializeSmartElement(options, config) {
    const onInitializeSuccess = () => {
      this._initialized.model = true;
    };
    const initOptions = options || {};
    this._initModel(this._getModelValue());
    this._initView();
    this._initExternalElements();
    if (initOptions.success) {
      initOptions.success = _.wrap(options.success, (success, ...args) => {
        onInitializeSuccess.apply(this);
        return success.apply(this, args);
      });
    }
    if (config.customClientData) {
      this._model._customClientData = config.customClientData;
    }
    if (config.formConfiguration) {
      this._model._formConfiguration = config.formConfiguration;
    }
    const resultPromise = this._model.fetchDocument(this._getModelValue(), options);
    if (!options.success) {
      resultPromise.then(onInitializeSuccess);
    }

    if (this._options.router !== false) {
      this._initRouter({ useHistory: true });
    }

    return resultPromise;
  }

  /**
   * Return essential element of the current smart element
   *
   * @returns {Object}
   * @private
   */
  private _getModelValue() {
    return _.pick(this._internalViewData, "initid", "viewId", "revision");
  }

  /**
   * Init the external elements (loading bar and notification widget)
   * @private
   */
  private _initExternalElements() {
    if (this._options.loading) {
      // @ts-ignore
      this.$loading = this._element.find(".dcpLoading").dcpLoading();
    }
    if (this._options.notification) {
      // @ts-ignore
      this.$notification = $("body").dcpNotification(window.dcp.notifications); // active notification
    }
  }

  private _computeUrl(options) {
    let urlSecondPart = "";
    let locationSearch = "";
    let identifier = options.initid;
    if (
      !identifier &&
      options.family &&
      (options.viewId === "!defaultCreation" || options.viewId === "!coreCreation")
    ) {
      identifier = options.family.name || options.family.id;
    }
    if (options.viewId !== "!defaultConsultation") {
      urlSecondPart = "/views/" + encodeURIComponent(options.viewId);
      if (options.revision >= 0 && options.security && options.security.fixed) {
        urlSecondPart += "/revisions/" + encodeURIComponent(options.revision);
      }
    } else {
      if (options.revision >= 0 && options.security && options.security.fixed) {
        urlSecondPart = "/revisions/" + encodeURIComponent(options.revision);
      }
    }

    if (options.customClientData) {
      locationSearch += locationSearch ? "&" : "?";
      locationSearch += "customClientData=";
      locationSearch += encodeURIComponent(JSON.stringify(options.customClientData));
    }

    return "/api/v2/smart-elements/" + identifier + urlSecondPart + ".html" + locationSearch;
  }

  /**
   * Init the model and bind the events
   *
   * @param initialValue
   * @returns DocumentModel
   * @private
   */
  private _initModel(initialValue) {
    let model;

    // Don't reinit the model
    if (!this._model) {
      model = new Model(initialValue);
      this._model = model;
      this._initModelEvents();
    } else {
      this._reinitModel();
    }
    return model;
  }

  /**
   * Clear and reinit the model with current widget values
   *
   * @private
   */
  private _reinitModel() {
    this._model.set(this._getModelValue());
  }

  /**
   * Init the view and bind the events
   *
   * @returns DocumentView
   * @private
   */
  private _initView() {
    let seView;
    /// Don't reinit view
    if (!this._view) {
      this._initDom();
      seView = new View({
        el: this._smartElement[0],
        model: this._model
      });
      this._view = seView;
      this._initViewEvents();
    }
    return this._view;
  }

  /**
   * Generate the dom where the view is inserted
   * @private
   */
  private _initDom() {
    const $se = this._element.find(".dcpDocument");
    if (!this._smartElement || $se.length === 0) {
      this._element.attr("data-controller", this.uid);
      const domTemplate = `<div class="smart-element-wrapper">
            <div class="document">
                <div class="dcpDocument"></div>
            </div>
            {{> loading}}
            {{> staticError}}
        </div>`;
      this._element.append(
        Mustache.render(
          domTemplate,
          {
            msg: {
              clickAction: i18n.___("Click to reload the document", "ddui"),
              loadError: i18n.___("Unable to load try again", "ddui")
            }
          },
          { loading: LoadingTemplate, staticError: StaticErrorTemplate }
        )
      );
      // Bind reload action in case of static error
      this._element.find(".dcpStaticErrorMessage .staticErrorReloadButton").on("click", () => {
        this._model.fetchDocument();
      });
      this._smartElement = this._element.find(".dcpDocument");
    }
  }

  /**
   * Bind the model event
   *
   * Re-trigger the event
   *
   * @private
   */
  private _initModelEvents() {
    this._model.listenTo(this._model, "invalid", (model, error) => {
      const result = this._triggerControllerEvent("displayError", null, this.getProperties(), error);
      if (result) {
        this.$notification.dcpNotification("showError", error);
      }
    });
    this._model.listenTo(this._model, "showError", error => {
      const result = this._triggerControllerEvent("displayError", null, this.getProperties(), error);
      if (result) {
        this.$notification.dcpNotification("showError", error);
      }
    });
    this._model.listenTo(this._model, "showMessage", msg => {
      const result = this._triggerControllerEvent("displayMessage", null, this.getProperties(), msg);
      if (result) {
        this.$notification.dcpNotification("show", msg.type, msg);
      }
    });
    this._model.listenTo(this._model, "reload", () => {
      this._model.fetchDocument();
    });
    this._model.listenTo(this._model, "sync", () => {
      this._initialized.model = true;
      this._internalViewData.initid = this._model.id;
      this._internalViewData.viewId = this._model.get("viewId");
      this._internalViewData.revision = this._model.get("revision");
      this._element.data("document", this._getModelValue());
      this._initListeners({ launchReady: false });
    });
    this._model.listenTo(this._model, "beforeRender", event => {
      event.promise = this._triggerControllerEvent(
        "beforeRender",
        event,
        this.getProperties(),
        this._model.getModelProperties()
      );
    });
    this._model.listenTo(this._model, "beforeClose", (event, nextDocument, customClientData) => {
      if (this._initialized.view) {
        event.promise = this._triggerControllerEvent(
          "beforeClose",
          event,
          this.getProperties(),
          nextDocument,
          customClientData
        );
      }
    });
    this._model.listenTo(this._model, "close", oldProperties => {
      if (this._initialized.view) {
        this._triggerControllerEvent("close", null, this.getProperties(), oldProperties);
      }
      this._initialized.view = false;
    });
    this._model.listenTo(this._model, "getCustomClientData", () => {
      try {
        this._model._customClientData = this.getCustomClientData(false);
      } catch (e) {
        // no test here
      }
    });
    this._model.listenTo(this._model, "beforeValidate", (event, customClientData) => {
      const requestOptions = {
        getRequestData: () => {
          return this._model.toJSON();
        },
        setRequestData: data => {
          this._model._customRequestData = data;
        }
      };
      event.promise = this._triggerControllerEvent(
        "beforeValidate",
        event,
        this.getProperties(),
        requestOptions,
        customClientData
      );
    });
    this._model.listenTo(this._model, "beforeSave", (event, customClientData) => {
      const requestOptions = {
        getRequestData: () => {
          return this._model.toJSON();
        },
        setRequestData: data => {
          this._model._customRequestData = data;
        }
      };
      event.promise = this._triggerControllerEvent(
        "beforeSave",
        event,
        this.getProperties(),
        requestOptions,
        customClientData
      );
    });
    this._model.listenTo(this._model, "afterSave", oldProperties => {
      this._triggerControllerEvent("afterSave", null, this.getProperties(), oldProperties);
    });
    this._model.listenTo(this._model, "beforeRestore", event => {
      event.promise = this._triggerControllerEvent("beforeRestore", event, this.getProperties());
    });
    this._model.listenTo(this._model, "afterRestore", oldProperties => {
      this._triggerControllerEvent("afterRestore", null, this.getProperties(), oldProperties);
    });
    this._model.listenTo(this._model, "beforeDelete", (event, customClientData) => {
      event.promise = this._triggerControllerEvent(
        "beforeDelete",
        event,
        this.getProperties(),
        this._model.getModelProperties(),
        customClientData
      );
    });
    this._model.listenTo(this._model, "afterDelete", oldProperties => {
      this._triggerControllerEvent("afterDelete", null, this.getProperties(), oldProperties);
    });
    this._model.listenTo(this._model, "validate", event => {
      event.prevent = !this._triggerControllerEvent("validate", event, this.getProperties());
    });
    this._model.listenTo(this._model, "changeValue", options => {
      try {
        const currentAttribute = this.getSmartField(options.attributeId);
        let index = 0;
        const values = currentAttribute.getValue("all");
        const mAttribute = this._getAttributeModel(options.attributeId);
        if (mAttribute.getParent().get("type") !== "array") {
          index = -1;
        } else {
          const changesIndex = [];
          _.each(values.current, (currentValue: any) => {
            let previous = values.previous[index];
            if (!previous) {
              changesIndex.push(index);
            } else {
              if (_.isArray(currentValue)) {
                currentValue = currentValue.join(",");
              }
              currentValue = _.has(currentValue, "value") ? currentValue.value : currentValue;
              if (_.isArray(previous)) {
                previous = previous.join(",");
              }
              previous = _.has(previous, "value") ? previous.value : previous;
              if (previous !== currentValue) {
                changesIndex.push(index);
              }
            }
            index++;
          });
          index = changesIndex.length === 1 ? changesIndex[0] : -1;
        }
        this._triggerAttributeControllerEvent(
          "smartFieldChange",
          null,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          currentAttribute.getValue("all"),
          index
        );
      } catch (error) {
        if (!(error instanceof ErrorModelNonInitialized)) {
          console.error(error);
        }
      }
    });
    this._model.listenTo(this._model, "beforeAttributeRender", (event, attributeId, $el, index) => {
      try {
        const currentAttribute = this.getSmartField(attributeId);
        event.promise = this._triggerAttributeControllerEvent(
          "smartFieldBeforeRender",
          event,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          $el,
          index
        );
      } catch (error) {
        if (!(error instanceof ErrorModelNonInitialized)) {
          console.error(error);
        }
      }
    });
    this._model.listenTo(this._model, "attributeRender", (attributeId, $el, index) => {
      try {
        const currentAttribute = this.getSmartField(attributeId);
        this._triggerAttributeControllerEvent(
          "smartFieldReady",
          null,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          $el,
          index
        );
      } catch (error) {
        if (!(error instanceof ErrorModelNonInitialized)) {
          console.error(error);
        }
      }
    });
    this._model.listenTo(this._model, "arrayModified", options => {
      try {
        const currentAttribute = this.getSmartField(options.attributeId);
        this._triggerAttributeControllerEvent(
          "smartFieldArrayChange",
          null,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          options.type,
          options.options
        );
      } catch (error) {
        if (!(error instanceof ErrorModelNonInitialized)) {
          console.error(error);
        }
      }
    });
    this._model.listenTo(this._model, "internalLinkSelected", (event, options) => {
      event.prevent = !this._triggerControllerEvent("actionClick", event, this.getProperties(), options);
    });
    this._model.listenTo(this._model, "downloadFile", (event, attrid, options) => {
      try {
        const currentAttribute = this.getSmartField(attrid);
        event.prevent = !this._triggerAttributeControllerEvent(
          "smartFieldDownloadFile",
          event,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          options.$el,
          options.index
        );
      } catch (error) {
        if (!(error instanceof ErrorModelNonInitialized)) {
          console.error(error);
        }
      }
    });
    this._model.listenTo(this._model, "uploadFile", (event, attrid, options) => {
      try {
        const currentAttribute = this.getSmartField(attrid);
        event.prevent = !this._triggerAttributeControllerEvent(
          "smartFieldUploadFile",
          event,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          options.$el,
          options.index,
          {
            file: options.file,
            hasUploadingFiles: this._model.hasUploadingFile()
          }
        );
      } catch (error) {
        if (!(error instanceof ErrorModelNonInitialized)) {
          console.error(error);
        }
      }
    });
    this._model.listenTo(this._model, "uploadFileDone", (event, attrid, options) => {
      try {
        const currentAttribute = this.getSmartField(attrid);
        event.prevent = !this._triggerAttributeControllerEvent(
          "smartFieldUploadFileDone",
          event,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          options.$el,
          options.index,
          {
            file: options.file,
            hasUploadingFiles: this._model.hasUploadingFile()
          }
        );
      } catch (error) {
        if (!(error instanceof ErrorModelNonInitialized)) {
          console.error(error);
        }
      }
    });

    this._model.listenTo(this._model, "attributeBeforeTabSelect", (event, attrid) => {
      const currentAttribute = this.getSmartField(attrid);
      let prevent;

      prevent = !this._triggerAttributeControllerEvent(
        "smartFieldBeforeTabSelect",
        event,
        currentAttribute,
        this.getProperties(),
        currentAttribute,
        $(event.item)
      );
      if (prevent) {
        event.preventDefault();
      }
    });
    this._model.listenTo(this._model, "attributeTabChange", (event, attrid, $el, data) => {
      const currentAttribute = this.getSmartField(attrid);

      this._triggerAttributeControllerEvent(
        "smartFieldTabChange",
        event,
        currentAttribute,
        this.getProperties(),
        currentAttribute,
        $el,
        data
      );
    });
    this._model.listenTo(this._model, "attributeAfterTabSelect", (event, attrid) => {
      const currentAttribute = this.getSmartField(attrid);

      this._triggerAttributeControllerEvent(
        "smartFieldAfterTabSelect",
        event,
        currentAttribute,
        this.getProperties(),
        currentAttribute,
        $(event.item)
      );
    });
    this._model.listenTo(this._model, "helperSearch", (event, attrid, options) => {
      try {
        const currentAttribute = this.getSmartField(attrid);
        event.prevent = !this._triggerAttributeControllerEvent(
          "smartFieldHelperSearch",
          event,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          options
        );
      } catch (error) {
        if (!(error instanceof ErrorModelNonInitialized)) {
          console.error(error);
        }
      }
    });
    this._model.listenTo(this._model, "helperResponse", (event, attrid, options) => {
      try {
        const currentAttribute = this.getSmartField(attrid);
        event.prevent = !this._triggerAttributeControllerEvent(
          "smartFieldHelperResponse",
          event,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          options
        );
      } catch (error) {
        if (!(error instanceof ErrorModelNonInitialized)) {
          console.error(error);
        }
      }
    });
    this._model.listenTo(this._model, "helperSelect", (event, attrid, options) => {
      try {
        const currentAttribute = this.getSmartField(attrid);
        event.prevent = !this._triggerAttributeControllerEvent(
          "smartFieldHelperSelect",
          event,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          options
        );
      } catch (error) {
        if (!(error instanceof ErrorModelNonInitialized)) {
          console.error(error);
        }
      }
    });

    // listener to prevent default actions when anchorClick is triggered
    this._model.listenTo(this._model, "anchorClick", (event, attrid, options) => {
      try {
        const currentAttribute = this.getSmartField(attrid);
        event.prevent = !this._triggerAttributeControllerEvent(
          "smartFieldAnchorClick",
          event,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          options.$el,
          options.index,
          options.options
        );
      } catch (error) {
        if (!(error instanceof ErrorModelNonInitialized)) {
          console.error(error);
        }
      }
    });

    // Generic listener for addCreateDocumentButton docid render option
    this._model.listenTo(this._model, "createDialogListener", (event, attrid, options) => {
      try {
        const currentAttribute = this.getSmartField(attrid);
        let triggername = "smartFieldCreateDialogSmartElement";
        // Uppercase first letter
        triggername += options.triggerId.charAt(0).toUpperCase() + options.triggerId.slice(1);

        event.prevent = !this._triggerAttributeControllerEvent(
          triggername,
          event,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          options
        );
      } catch (error) {
        if (!(error instanceof ErrorModelNonInitialized)) {
          console.error(error);
        }
      }
    });
    this._model.listenTo(this._model, "constraint", (attribute, constraintController) => {
      try {
        const currentAttribute = this.getSmartField(attribute);
        const currentModel = this.getProperties();
        const values = currentAttribute.getValue("all");
        const addConstraint = currentConstraint => {
          if (_.isString(currentConstraint)) {
            constraintController.addConstraintMessage(currentConstraint);
          }
          if (_.isObject(currentConstraint) && currentConstraint.message && _.isNumber(currentConstraint.index)) {
            constraintController.addConstraintMessage(currentConstraint.message, currentConstraint.index);
          }
        };
        this._triggerAttributeControllerEvent(
          "smartFieldConstraintCheck",
          null,
          currentAttribute,
          currentModel,
          currentAttribute,
          values,
          {
            displayConstraint: addConstraint
          }
        );
      } catch (error) {
        if (!(error instanceof ErrorModelNonInitialized)) {
          console.error(error);
        }
      }
    });
    this._model.listenTo(this._model, "showTransition", _.bind(this._initAndDisplayTransition, this));
    this._model.listenTo(
      this._model,
      "beforeParse",
      _.bind(() => {
        // Suppress customClientData after a sucessful transaction
        try {
          this.getCustomClientData(true);
        } catch (e) {
          // no test here
        }
      }, this)
    );

    this._model.listenTo(this._model, "injectCurrentSmartElementJS", event => {
      event.controller = this;
      // this._reinitListeners();
      this._triggerControllerEvent("injectCurrentSmartElementJS", null, this.getProperties(), event);
    });
  }

  /**
   * Bind the view
   * Re-trigger the events
   *
   * @private
   */
  private _initViewEvents() {
    this._view.on("cleanNotification", () => {
      this.$notification.dcpNotification("clear");
    });
    this._view.on("loading", (data, nbItem) => {
      this.$loading.dcpLoading("setPercent", data);
      if (nbItem) {
        this.$loading.dcpLoading("setNbItem", nbItem);
      }
    });
    this._view.on("loaderShow", (text, pc) => {
      this.$loading.dcpLoading("show", text, pc);
    });
    this._view.on("loaderHide", () => {
      this.$loading.dcpLoading("hide");
    });
    this._view.on("partRender", () => {
      this.$loading.dcpLoading("addItem");
    });
    this._view.on("renderDone", () => {
      this.$loading.dcpLoading("setPercent", 100);
      this.$loading.dcpLoading("setLabel", null);
      this._initialized.view = true;
      this._triggerControllerEvent("ready", null, this.getProperties());
      _.delay(() => {
        this.$loading.dcpLoading("hide", true);
      });
    });
    this._view.on("showMessage", message => {
      const result = this._triggerControllerEvent("displayMessage", null, this.getProperties(), message);
      if (result) {
        this.$notification.dcpNotification("show", message.type, message);
      }
    });
    this._view.on("showSuccess", message => {
      if (message) {
        message.type = message.type ? message.type : "success";
      }
      const result = this._triggerControllerEvent("displayMessage", null, this.getProperties(), message);
      if (result) {
        this.$notification.dcpNotification("showSuccess", message);
      }
    });
    this._view.on("reinit", () => {
      this._initModel(this._getModelValue());
      this._initView();
      this._model.fetchDocument();
    });
    this._view.on("renderCss", css => {
      this._triggerControllerEvent("renderCss", null, this.getProperties, css);
    });
  }

  /**
   * Init the pushstate router
   *
   * @private
   */
  private _initRouter(config) {
    if (this._router) {
      return this._router;
    }
    try {
      if (window.history && history.pushState) {
        Backbone.history.start({ pushState: true });
      } else {
        // For browser without API history
        Backbone.history.start();
      }
    } catch (e) {
      console.error(e);
    }
    this._router = new Router({
      document: this._model,
      useHistory: !config || config.useHistory
    });
  }

  /**
   * Init and display the change state pop-up
   *
   * @param nextState
   * @param transition
   * @param values
   * @param withoutInterface
   * @param transitionElementsCallBack
   * @param reinitOptions
   */
  private _initAndDisplayTransition(
    nextState,
    transition,
    values,
    withoutInterface,
    transitionElementsCallBack,
    reinitOptions
  ) {
    const $target = $('<div class="dcpTransition"/>');
    const transitionElements: any = {};
    let result;
    let transitionInterface;
    const documentServerProperties = this.getProperties();

    return new Promise((resolve, reject) => {
      result = !this._triggerControllerEvent(
        "beforeDisplayChangeState",
        null,
        this.getProperties(),
        new TransitionInterface(null, $target, nextState, transition)
      );
      if (result) {
        reject();
        return this;
      }

      // Init transition model
      transitionElements.model = new TransitionModel({
        documentId: this._model.id,
        documentModel: this._model,
        state: nextState,
        transition
      });

      // Init transition view
      if (withoutInterface !== true) {
        transitionElements.view = new TransitionView({
          el: $target,
          model: transitionElements.model
        });
      }

      transitionInterface = new TransitionInterface(transitionElements.model, $target, nextState, transition);

      if (transitionElements.view) {
        // Propagate afterDisplayChange on renderDone
        transitionElements.view.once("renderTransitionWindowDone", () => {
          this._triggerControllerEvent("afterDisplayTransition", null, this.getProperties(), transitionInterface);
        });
      }

      // Propagate the beforeTransition
      transitionElements.model.listenTo(transitionElements.model, "beforeChangeState", event => {
        event.prevent = !this._triggerControllerEvent(
          "beforeTransition",
          null,
          this.getProperties(),
          transitionInterface
        );
      });

      // Propagate the beforeTransitionClose
      transitionElements.model.listenTo(transitionElements.model, "beforeChangeStateClose", event => {
        event.prevent = !this._triggerControllerEvent(
          "beforeTransitionClose",
          null,
          this.getProperties(),
          transitionInterface
        );
      });

      transitionElements.model.listenTo(transitionElements.model, "showError", error => {
        this._triggerControllerEvent("failTransition", null, this.getProperties(), transitionInterface, error);
        reject({ documentProperties: documentServerProperties });
      });

      transitionElements.model.listenTo(transitionElements.model, "success", messages => {
        if (transitionElements.view) {
          transitionElements.view.$el.hide();
          this._view.once("renderDone", () => {
            transitionElements.view.remove();
            _.each(messages, message => {
              this._view.trigger("showMessage", message);
            });
          });
        }

        // delete the pop up when the render of the pop up is done
        this._triggerControllerEvent("successTransition", null, this.getProperties(), transitionInterface);

        reinitOptions = reinitOptions || { revision: -1 };
        if (!_.has(reinitOptions, "revision")) {
          reinitOptions.revision = -1;
        }

        // Reinit the main model with last revision
        this.reinitSmartElement(reinitOptions).then(
          () => {
            resolve({ documentProperties: documentServerProperties });
          },
          () => {
            reject({ documentProperties: documentServerProperties });
          }
        );
      });

      transitionElements.model.listenTo(this._model, "sync", function documentController_TransitionClose() {
        // @ts-ignore
        this.trigger("close");
      });

      transitionElements.model.fetch({
        error: (theModel, response, options) => {
          const errorTxt: { title: string; message?: string } = {
            title: "Transition Error"
          };
          if (options && options.errorThrown) {
            errorTxt.message = options.errorThrown;
          }
          this.$notification.dcpNotification("showError", errorTxt);
          transitionElements.model.trigger("showError", errorTxt);
        },
        success: () => {
          if (withoutInterface === true) {
            transitionElements.model
              ._loadDocument(transitionElements.model)
              .then(() => {
                if (values) {
                  transitionElements.model.setValues(values);
                }
                if (_.isFunction(transitionElementsCallBack)) {
                  try {
                    transitionElementsCallBack(transitionElements);
                  } catch (e) {
                    // nothing to do;
                  }
                }
              })
              .then(() => {
                transitionElements.model.save(
                  {},
                  {
                    error: () => {
                      reject({
                        documentProperties: documentServerProperties
                      });
                    },
                    success: () => {
                      transitionElements.model.trigger("success");
                      resolve({
                        documentProperties: documentServerProperties
                      });
                    }
                  }
                );
              })
              .catch(function transitionModel_error() {
                reject({ documentProperties: documentServerProperties });
              });
          } else {
            transitionElements.model
              ._loadDocument(transitionElements.model)
              .then(() => {
                if (values) {
                  transitionElements.model.setValues(values);
                }
                if (_.isFunction(transitionElementsCallBack)) {
                  try {
                    transitionElementsCallBack(transitionElements);
                  } catch (e) {
                    // nothing to do;
                  }
                }
              })
              .then(() => {
                transitionElements.model.trigger("dduiDocumentReady");
              })
              .catch(() => {
                reject({ documentProperties: documentServerProperties });
              });
          }
        }
      });
    });
  }

  /**
   * Get a backbone model of an attribute
   *
   * @param attributeId
   * @returns {*}
   */
  private _getAttributeModel(attributeId) {
    const attributes = this._model.get("attributes");
    let attribute;
    if (!attributes) {
      throw new Error('Attribute models not initialized yet : The attribute "' + attributeId + '" cannot be found.');
    }
    attribute = this._model.get("attributes").get(attributeId);
    if (!attribute) {
      return undefined;
    }
    return attribute;
  }

  private _getMenuModel(menuId) {
    const menus = this._model.get("menus");

    let menu = menus.get(menuId);
    if (!menu && menus) {
      menus.each(itemMenu => {
        if (itemMenu.get("content")) {
          _.each(itemMenu.get("content"), (subMenu: any) => {
            if (subMenu.id === menuId) {
              menu = new MenuModel(subMenu);
            }
          });
        }
      });
    }
    return menu;
  }

  /**
   * Get all rendered attributes with their root dom node
   *
   * @returns {*}
   */
  private _getRenderedAttributes() {
    return this._model
      .get("attributes")
      .chain()
      .map(currentAttribute => {
        return {
          id: currentAttribute.id,
          view: currentAttribute.haveView()
        };
      })
      .filter(currentAttribut => {
        return currentAttribut.view.haveView;
      })
      .value();
  }

  /**
   * Get max index of an array
   *
   * @param attributeArray
   * @returns {*}
   */
  private _getMaxIndex(attributeArray) {
    return _.size(
      attributeArray
        .get("content")
        .max(currentAttr => {
          return _.size(currentAttr.get("attributeValue"));
        })
        .get("attributeValue")
    );
  }

  /**
   * Trigger attribute event
   *
   * Similar at trigger document event with a constraint on attribute
   *
   * @param eventName
   * @param originalEvent
   * @param attributeInternalElement
   * @param args
   * @returns {boolean}
   */
  private _triggerAttributeControllerEvent = (
    eventName,
    originalEvent,
    attributeInternalElement: ISmartField,
    ...args
  ) => {
    const event: any = $.Event(eventName);
    event.target = this._element;
    // internal event trigger
    if (originalEvent && originalEvent.preventDefault) {
      event.originalEvent = originalEvent;
    }
    const callbackArgs = [event, ...args];
    let eventPromise = Promise.resolve();
    try {
      eventPromise = this.emit({ name: eventName as string, type: "smartField" }, ...callbackArgs) as Promise<void>;
    } catch (e) {
      // @ts-ignore
      if (window.dcp.logger) {
        // @ts-ignore
        window.dcp.logger(e);
      } else {
        console.error(e);
      }
    }
    return eventPromise;
  };

  /**
   * Trigger a controller event
   * That kind of event are only for this widget
   *
   * @param eventName
   * @param originalEvent
   * @param args
   * @returns {boolean}
   */
  private _triggerControllerEvent(eventName, originalEvent, ...args: any[]) {
    const event: JQuery.Event & {
      target: JQuery<DOMReference>;
      originalEvent?: JQuery.Event;
    } = $.Event(eventName);
    event.target = this._element;
    if (originalEvent && originalEvent.preventDefault) {
      event.originalEvent = originalEvent;
    }
    // internal event trigger
    const callbackArgs = [event, ...args];

    let eventPromise = Promise.resolve();
    try {
      eventPromise = this.emit({ name: eventName, type: "smartElement" }, ...callbackArgs) as Promise<void>;
    } catch (e) {
      // @ts-ignore
      if (window.dcp.logger) {
        // @ts-ignore
        window.dcp.logger(e);
      } else {
        console.error(e);
      }
    }
    return eventPromise;
    // return !event.isDefaultPrevented();
  }

  /**
   * Check if event name is valid
   *
   * @param eventName string
   * @private
   */
  private checkEventName(eventName) {
    if (
      _.isString(eventName) &&
      (eventName.indexOf("custom:") === 0 ||
        _.find(AnakeenController.SmartElement.EVENTS_LIST, currentEventType => {
          return currentEventType === eventName;
        }))
    ) {
      return true;
    }
    throw new Error(
      "The event type " +
        eventName +
        " is not known. It must be one of " +
        AnakeenController.SmartElement.EVENTS_LIST.sort().join(" ,")
    );
  }

  /**
   * Check if the view is initialized
   *
   * @private
   */
  private checkInitialisedView() {
    if (!this._initialized.view) {
      throw new ErrorModelNonInitialized("The view is not initialized, use fetchSmartElement to initialise it.");
    }
  }

  /**
   * Check if the model is initialized
   *
   * @private
   */
  private checkInitialisedModel() {
    if (!this._initialized.model) {
      throw new ErrorModelNonInitialized();
    }
  }

  private _registerOutputPromise(documentPromise, options) {
    return new Promise((resolve, reject) => {
      documentPromise.then(
        values => {
          if (options && _.isFunction(options.success)) {
            try {
              if (window.console.warn) {
                window.console.warn('Callback "success" is deprecated use promise instead');
              }
              options.success.call(
                $(this._element),
                (values ? values.documentProperties : {}) || {},
                this.getProperties()
              );
            } catch (exception) {
              // @ts-ignore
              if (window.dcp.logger) {
                // @ts-ignore
                window.dcp.logger(exception);
              } else {
                console.error(exception);
              }
            }
          }
          resolve({
            element: $(this._element),
            nextDocument: this.getProperties(),
            previousDocument: (values ? values.documentProperties : {}) || {}
          });
        },
        values => {
          const errorArguments = values.arguments || values.promiseArguments;
          let errorMessage = { contentText: "Undefined error" };

          if (errorArguments) {
            try {
              if (errorArguments && errorArguments[0] && errorArguments[0].message) {
                errorMessage = { contentText: errorArguments[0].message };
              } else if (errorArguments && errorArguments[1] && errorArguments[1].responseJSON) {
                errorMessage = errorArguments[1].responseJSON.messages[0];
              }
            } catch (e) {
              // no error here
            }
            if (errorArguments && errorArguments[0] && errorArguments[0].eventPrevented) {
              errorMessage = { contentText: "Event prevented" };
            }
            if (errorArguments && errorArguments[0] && errorArguments[0].errorMessage) {
              errorMessage = errorArguments[0].errorMessage;
            }
          }
          if (options && _.isFunction(options.error)) {
            try {
              if (window.console.warn) {
                window.console.warn('Callback "error" is deprecated use promise instead');
              }
              options.error.call($(this._element), values.documentProperties || {}, null, errorMessage);
            } catch (exception) {
              // @ts-ignore
              window.dcp.logger(exception);
            }
          }
          reject({
            element: $(this._element),
            errorMessage,
            nextDocument: null,
            previousDocument: values.documentProperties || {}
          });
        }
      );
    });
  }

  private _addAndInitNewEvents(newEvent: ListenableEventOptions) {
    // let uniqueName = (newEvent.externalEvent ? "external_" : "internal_") + newEvent.name;
    const currentElementProperties = this.getProperties();
    if (newEvent._persistent) {
      if (newEvent.once) {
        this._registeredListeners.once(newEvent.eventType, newEvent);
      } else {
        this._registeredListeners.on(newEvent.eventType, newEvent);
      }
    } else {
      if (newEvent.once) {
        this.once(newEvent.eventType, newEvent);
      } else {
        this.on(newEvent.eventType, newEvent);
      }
    }
    if (!this._initialized.model) {
      // early event model is not ready (no trigger, or current register possible)
      return this;
    }

    // Check if the event is for the current document
    if (!_.isFunction(newEvent.check) || newEvent.check.call(this, currentElementProperties)) {
      if (this._initialized.view) {
        if (newEvent.eventType === "ready") {
          const event = $.Event(newEvent.eventType);
          // @ts-ignore
          event.target = this._element;
          try {
            // add element as function context
            newEvent.eventCallback.call(this, event, currentElementProperties);
          } catch (e) {
            console.error(e);
          }
        }
        if (newEvent.eventType === "smartFieldReady") {
          const event = $.Event(newEvent.eventType);
          // @ts-ignore
          event.target = this._element;
          _.each(this._getRenderedAttributes(), (currentAttribute: any) => {
            const objectAttribute = this.getSmartField(currentAttribute.id);
            if (!_.isFunction(newEvent.smartFieldCheck) || newEvent.smartFieldCheck.apply(this, [objectAttribute])) {
              try {
                // add element as function context
                newEvent.eventCallback.call(
                  this,
                  event,
                  currentElementProperties,
                  objectAttribute,
                  currentAttribute.view.elements
                );
              } catch (e) {
                console.error(e);
              }
            }
          });
        }
      }
    }
  }

  private _initListeners(options) {
    const currentProperties = this.getProperties();
    const initOptions = options || {};
    this._reinitListeners();
    // Trigger new added ready event
    if (this._initialized.view && initOptions.launchReady) {
      this._triggerControllerEvent("ready", null, currentProperties);
      _.each(this._getRenderedAttributes(), (currentAttribute: AttributeInterface) => {
        const objectAttribute = this.getSmartField(currentAttribute.id);
        this._triggerAttributeControllerEvent(
          "smartFieldReady",
          null,
          currentAttribute,
          currentProperties,
          objectAttribute,
          currentAttribute.view.elements
        );
      });
    }
  }
}
