/* eslint-disable no-unused-vars */
/* tslint:disable:variable-name ordered-imports */
import { AnakeenController } from "./types/ControllerTypes";
import DOMReference = AnakeenController.Types.DOMReference;
import ControllerUID = AnakeenController.Types.ControllerUID;
import ListenableEventCallable = AnakeenController.BusEvents.ListenableEventCallable;
import ListenableEvent = AnakeenController.BusEvents.ListenableEvent;
import EVENTS_LIST = AnakeenController.SmartElement.EVENTS_LIST;
import ControllerOptions = AnakeenController.Types.IControllerOptions;
import ControllerNotFoundError from "./ControllerNotFoundError";

import moduleTemplate from "./utils/templates/module.mustache.js";
import $ from "jquery";
import Mustache from "mustache";
import _ from "underscore";
import ControllerDispatcher from "./ControllerDispatcher";
import SmartElementController from "./SmartElementController";
import load from "./utils/ScriptLoader.js";
import * as util from "util";

interface IAsset {
  key: string;
  path: string;
}

interface ICallBack {
  callback: (controller: SmartElementController) => void;
}

type CssAssetList = IAsset[];

const FunctionNotFound = function(this: Error, message): void {
  Error.captureStackTrace(this, this.constructor);
  this.name = this.constructor.name;
  this.message = message;
};
util.inherits(FunctionNotFound, Error);

export default class GlobalController extends AnakeenController.BusEvents.Listenable {
  /**
   * The singleton instance of the global controller;
   */
  private static _selfController: GlobalController;

  private notObserveUnload = {};
  /**
   * Create script element
   * @param js
   * @param script
   * @private
   */
  private static _createScript(js, script: HTMLScriptElement): void {
    const currentPath = js.path;
    const $script = $(script);
    $script.attr("data-id", js.key);
    $script.attr("data-src", currentPath);
    if (js.type === "module") {
      $script.attr("type", "module");
      $script.text(Mustache.render(moduleTemplate, js));
    }
  }

  /**
   * Controller actions dispatcher
   */
  protected _dispatcher: ControllerDispatcher;

  protected cssList: CssAssetList = [];

  /**
   * Verbose mode of the controller
   */
  private _verbose = false;

  private _isReady = false;

  private _domObserver: MutationObserver;

  private _registeredFunction: { [functionKey: string]: ICallBack } = {};
  /**
   * Constructor of the GlobalController. The GlobalController is a Singleton
   */
  public constructor(autoInit = true) {
    super(!GlobalController._selfController);
    if (!GlobalController._selfController) {
      GlobalController._selfController = this;
      if (autoInit && !this._isReady) {
        this.init();
      }
    }
    return GlobalController._selfController;
  }

  public init() {
    if (!this._isReady) {
      return import("./ControllerDispatcher" /* webpackChunkName: "ControllerDispatcher" */)
        .then(controllerDispatcher => {
          this._dispatcher = new controllerDispatcher.default();
          this._domObserver = new MutationObserver(mutations => this._onRemoveDOMController(mutations));
          this._domObserver.observe(document, { subtree: true, childList: true });
          this._isReady = true;
          this._dispatcher.on("injectCurrentSmartElementJS", (controller, event, properties, jsEvent) => {
            this._injectSmartElementJS(jsEvent);
          });
          this._dispatcher.on("renderCss", (controller, event, properties, css) => {
            this._onRenderCss(css);
          });
          this.setVerbose(this._verbose);
          this.emit("controllerReady", this);
          this._logVerbose("Global Anakeen Controller ready", "Global");
        })
        .then(() => {
          return this;
        });
    }
    return Promise.resolve(this);
  }

  public on(eventName: string, callback: AnakeenController.BusEvents.ListenableEventCallable) {
    super.on(eventName, callback);
    if (eventName === "controllerReady" && this._isReady) {
      // If controller is already ready, execute callback immediatly
      if (callback) {
        callback.call(null, this);
      }
    }
  }

  /**
   * Get a scoped controller. If no argument, return all controllers.
   *
   * @param scopeId
   * @return {SmartElementController} controller
   * @throws {Error} if controller not found
   */
  public getScopedController(
    scopeId?: ControllerUID | DOMReference
  ): SmartElementController | SmartElementController[] {
    if (scopeId === undefined) {
      return this.getControllers();
    }
    try {
      return this._dispatcher.getController(scopeId);
    } catch (error) {
      this.emit("controllerError", null, error);
      throw error;
    }
  }

  /**
   * Get all controllers
   *
   * @param scopeId
   */
  public getControllers(scopeId?: ControllerUID | DOMReference): SmartElementController[] {
    return this._dispatcher.getControllers() as SmartElementController[];
  }

  /**
   * Add the smart Element in the DOM
   * @param dom
   * @param viewData
   * @param options
   * @throws Error
   */
  public addSmartElement(
    dom: DOMReference,
    viewData?: AnakeenController.Types.IViewData,
    options?: ControllerOptions
  ): ControllerUID {
    viewData = viewData || {
      initid: 0,
      revision: -1,
      viewId: "!defaultConsultation"
    };
    try {
      const controller = this._dispatcher.initController(dom, viewData, options);
      this._logVerbose(`Add smart element "${viewData.initid}"`, controller.uid);
      this.emit("controllerSmartElementAdded", controller);
      return controller.uid;
    } catch (error) {
      this.emit("controllerError", null, error);
      throw error;
    }
  }
  public removeSmartElement(controllerUID) {
    try {
      const controller = this.getScopedController(controllerUID) as SmartElementController;
      this._logVerbose(
        `remove scoped controller (${controllerUID}) for smart element "${controller.getProperties().initid}"`,
        "Global"
      );
      this._dispatcher.removeController(controllerUID);
      this._cleanCss();
    } catch (e) {
      // Nothing to do : the element is already destroyed
      if (!(e instanceof ControllerNotFoundError)) {
        throw e;
      }
    }
  }
  /**
   *
   * @param operation
   * @param check
   * @param args
   */
  public execute(operation: string, check: (controller: SmartElementController) => boolean, ...args: any[]) {
    return this._dispatcher.dispatch(operation, check, ...args);
  }

  /**
   * Add global event listener
   * @param eventType
   * @param options
   * @param callback
   */
  public addEventListener(
    eventType: string | ListenableEvent,
    options: object | ListenableEventCallable,
    callback?: ListenableEventCallable
  ) {
    let currentEvent;
    let eventCallback = callback;
    let eventOptions = options;
    // options is not mandatory and the callback can be the second parameters
    if (_.isUndefined(eventCallback) && _.isFunction(eventOptions)) {
      eventCallback = eventOptions as ListenableEventCallable;
      eventOptions = {};
    }

    // the first parameters can be the final object (chain removeEvent and addEvent)
    if (_.isObject(eventType) && _.isUndefined(eventOptions) && _.isUndefined(eventCallback)) {
      currentEvent = eventType;
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
        externalEvent: false,
        name: _.uniqueId("event_" + eventType),
        once: false
      });
    }
    // the eventType must be one the list
    this._checkEventName(currentEvent.eventType);
    // callback is mandatory and must be a function
    if (!_.isFunction(currentEvent.eventCallback)) {
      throw new Error("An event needs a callback that is a function");
    }

    // Listen all controllers for this event type
    this._dispatcher.on(currentEvent.eventType, (controller, ...args) => {
      // Check execution
      if (
        !_.isFunction(currentEvent.check) ||
        currentEvent.check.call(controller._element, controller.getProperties())
      ) {
        currentEvent.eventCallback.call(controller._element, ...args);
      }
    });
    // return the name of the event
    return currentEvent.name;
  }

  public registerFunction(key: string, scriptFunction: (controller: SmartElementController) => void) {
    if (key && typeof scriptFunction === "function") {
      if (this._registeredFunction[key]) {
        this._logVerbose(`Beware ! The key ${key} is already used`, "Asset", "JS");
      }
      this._registeredFunction[key] = { callback: scriptFunction };
      this._logVerbose(`register function with key ${key}`, "Asset", "JS");
    } else {
      throw new Error(`You must register a function for ${key}`);
    }
  }

  public setVerbose(enable = false) {
    this._verbose = enable;
    if (enable) {
      this._logVerbose("verbose mode enabled", "Global");
      // Log events
      EVENTS_LIST.forEach(event => {
        this._dispatcher.on(event, (controller, ...args) => {
          let fieldId = "";
          if (event.indexOf("smartField") === 0) {
            if (args[2].prototype === "AttributePrototype") {
              fieldId = args[2].id;
            }
          }
          const seProps = controller.getProperties();
          this._logVerbose(`Smart element "${seProps.initid}" event ${event} triggered`, "Event", fieldId);
        });
      });
    }
  }
  public setAutoUnload(autoUnload: boolean, controllerId: string): void {
    // set autoUnload to true means set false to notObserveUnload for the controller id
    this.notObserveUnload[controllerId] = !autoUnload;
  }
  protected _onRemoveDOMController(mutationList: MutationRecord[]) {
    // Walk in dom mutation
    mutationList.forEach(mutation => {
      // filter only in dom removal mutations
      if (mutation.type === "childList" && mutation.removedNodes.length) {
        // tslint:disable-next-line:prefer-for-of
        for (let i = 0; i < mutation.removedNodes.length; i++) {
          const node = $(mutation.removedNodes[i]);
          // Check if dom removal concerns scoped controller
          const controllerIDs = node.find("[data-controller]").map((index, e) => $(e).attr("data-controller"));
          if (controllerIDs && controllerIDs.length) {
            for (let j = controllerIDs.length - 1; j >= 0; j--) {
              const controllerUID = controllerIDs[j];
              try {
                const controller = this.getScopedController(controllerUID) as SmartElementController;
                // Prepare clean up callback
                const onDestroy = () => {
                  this._logVerbose(
                    `remove scoped controller (${controllerUID}) for smart element "${
                      controller.getProperties().initid
                    }"`,
                    "Global"
                  );
                  this._dispatcher.removeController(controllerUID);
                  this._cleanCss();
                };
                // If the scoped controller must auto unload
                if (!this.notObserveUnload[controllerUID]) {
                  this._logVerbose(
                    `try to destroy smart element "${
                      controller.getProperties().initid
                    }" bind to controller "${controllerUID}"`,
                    "Global"
                  );
                  controller
                    .tryToDestroy({ testDirty: false })
                    .then(onDestroy)
                    .catch(onDestroy);
                } else {
                  // The scoped controller have not to auto unload, but clean it up nonetheless
                  onDestroy();
                }
              } catch (e) {
                // Nothing to do : the element is already destroyed
                if (!(e instanceof ControllerNotFoundError)) {
                  throw e;
                }
              }
            }
          }
        }
      }
    });
  }

  /**
   * Check if event name is valid
   *
   * @param eventName string
   * @private
   */
  private _checkEventName(eventName) {
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

  private _extractNewCss(currentList: CssAssetList, newList: CssAssetList): CssAssetList {
    const result: CssAssetList = [];
    newList.forEach(newCss => {
      const matches = currentList.filter(css => {
        return css.path === newCss.path;
      });
      if (!(matches && matches.length)) {
        result.push(newCss);
      }
    });
    return result;
  }

  private _cleanCss() {
    const controllers = this.getScopedController() as SmartElementController[];
    let allCss = [];
    controllers.forEach(controller => {
      // @ts-ignore
      const css = controller._model.get("customCSS");
      if (css) {
        const difference = css.filter(cssItem => {
          return !_.find(allCss, item => item.key === cssItem.key);
        });
        allCss = allCss.concat(difference);
      }
    });
    $("link[data-view=true]").each((index, element) => {
      const matches = allCss.filter(css => css.key === $(element).data("id"));
      if (!matches || !matches.length) {
        this._logVerbose(
          `remove useless stylesheet ${$(element).attr("href")} with key ${$(element).data("id")}`,
          "Asset",
          "CSS"
        );
        $(element).remove();
      }
    });
  }

  private _onRenderCss(customCss: CssAssetList) {
    this.cssList.push(...this._extractNewCss(this.cssList, customCss));
    // add custom css style
    const $head = $("head");
    const cssLinkTemplate = _.template(
      '<link rel="stylesheet" type="text/css" ' + 'href="<%= path %>" data-id="<%= key %>" data-view="true">'
    );
    // Clean CSS
    this._cleanCss();
    // Inject new CSS
    _.each(customCss, cssItem => {
      const $existsLink = $(`link[rel=stylesheet][data-id=${cssItem.key}]`);

      if ($existsLink.length === 0) {
        this._logVerbose(`add stylesheet ${cssItem.path} with key ${cssItem.key}`, "Asset", "CSS");
        // @ts-ignore
        if (document.createStyleSheet) {
          // Special thanks to IE : ! up to 31 css cause errors...
          // @ts-ignore
          document.createStyleSheet(cssItem.path);
        }
        $head.append(cssLinkTemplate(cssItem));
      }
    });
  }

  /**
   * Inject smart element js in the page
   * @param event
   * @private
   */
  private _injectSmartElementJS(event): void {
    const methodsToExecute = {} as { [functionKey: string]: ICallBack };
    const injectPromise = event.js.reduce((acc, currentJS) => {
      let needToRegisterScript = false;
      //Check if the needed function are already is the scope
      if (!currentJS.type || currentJS.type === "library") {
        let functionKey = currentJS.function || currentJS.key;
        if (!Array.isArray(functionKey)) {
          functionKey = [functionKey];
        }
        functionKey.forEach(currentMethodName => {
          try {
            methodsToExecute[currentMethodName] = this._getRegisteredFunction(currentMethodName);
          } catch (e) {
            if (e instanceof FunctionNotFound) {
              needToRegisterScript = true;
            } else {
              throw e;
            }
          }
        });
      } else {
        needToRegisterScript = true;
      }

      const currentPath = currentJS.path;
      // inject js if not alredy exist
      if (needToRegisterScript && $(`script[data-src="${currentPath}"], script[src="${currentPath}"]`).length === 0) {
        return acc.then(() => {
          return new Promise((resolve, reject) => {
            let url = currentPath;
            if (currentJS.type === "module") {
              url = "";
            }
            load(url, {
              callback: err => {
                if (err) {
                  reject(err);
                } else if (currentJS.type === "global") {
                  this._logVerbose(
                    `inject javascript ${currentJS.path} in mode ${currentJS.type}`,
                    event.controller.uid,
                    "Asset",
                    "JS"
                  );
                  resolve();
                } else if (!currentJS.type || currentJS.type === "library") {
                  this._logVerbose(
                    `inject javascript ${currentJS.path} in mode ${currentJS.type || "library"}`,
                    event.controller.uid,
                    "Asset",
                    "JS"
                  );
                  let currentKey = currentJS.function || currentJS.key;
                  if (!Array.isArray(currentKey)) {
                    currentKey = [currentKey];
                  }
                  currentKey.forEach(currentMethodName => {
                    try {
                      methodsToExecute[currentMethodName] = this._getRegisteredFunction(currentMethodName);
                    } catch (e) {
                      if (e instanceof FunctionNotFound) {
                        console.error(
                          `Missing executable function for key "${currentMethodName}" in script "${currentJS.path}"`
                        );
                      }
                    }
                  });
                  this.emit("_internal::scriptReady", currentJS.path);
                }
              },
              setup: script => GlobalController._createScript(currentJS, script)
            });
            // Wait script function registration for module and library injection
            if (currentJS.type !== "global") {
              this.on("_internal::scriptReady", url => {
                if (url === currentPath) {
                  resolve();
                }
              });
            }
          });
        });
      } else {
        // Script function is already available
        return acc;
      }
    }, Promise.resolve());
    // Set inject promise
    event.injectPromise = injectPromise.then(() => {
      // Execute script function with scoped controller
      const scopedController = this.getScopedController(event.controller.uid) as SmartElementController;
      const callback = Object.values(methodsToExecute).map(callBack => {
        return (): any => {
          let result;
          try {
            result = callBack.callback.call(this, scopedController);
          } catch (e) {
            console.error(e);
          }
          return result;
        };
      });
      //Execute all the stacked method one after other
      return callback.reduce((acc, currentCallBack) => {
        return acc.then(() => {
          const result = currentCallBack();
          if (result instanceof Promise) {
            return result;
          } else {
            return Promise.resolve(result);
          }
        });
      }, Promise.resolve());
    });
  }

  /**
   * Register script function for later reuse after listener unbinding
   * @param scriptUrl
   * @private
   */
  private _registerScript(scriptUrl: string): void {
    this.emit("_internal::scriptReady", scriptUrl);
  }

  /**
   * Return a registered function or throw an FunctionNotFound Error
   * @param key
   * @private
   */
  private _getRegisteredFunction(key: string): ICallBack {
    if (!this._registeredFunction[key]) {
      throw new FunctionNotFound("Function not registered");
    }
    return this._registeredFunction[key];
  }

  private _logVerbose(message, ...categories) {
    if (this._verbose) {
      let strCategories = "";
      if (categories && categories.length) {
        strCategories = `[${categories.filter(c => !!c).join("][")}]`;
      }
      const logMsg = `[Smart Element Controller]${strCategories} : ${message}`;
      window.console.log(logMsg);
      this.emit("controllerLog", logMsg);
    }
  }
}
