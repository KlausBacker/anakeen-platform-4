/* eslint-disable no-unused-vars */
/* tslint:disable:variable-name ordered-imports */
import { AnakeenController } from "./types/ControllerTypes";
import DOMReference = AnakeenController.Types.DOMReference;
import ControllerUID = AnakeenController.Types.ControllerUID;
import ListenableEventCallable = AnakeenController.BusEvents.ListenableEventCallable;
import ListenableEvent = AnakeenController.BusEvents.ListenableEvent;
import EVENTS_LIST = AnakeenController.SmartElement.EVENTS_LIST;
import ControllerOptions = AnakeenController.Types.IControllerOptions;
// @ts-ignore
import moduleTemplate from "!!raw-loader!./utils/templates/module.js.mustache";
import * as $ from "jquery";
import * as Mustache from "mustache";
import * as _ from "underscore";
import ControllerDispatcher from "./ControllerDispatcher";
import SmartElementController from "./SmartElementController";
import load from "./utils/ScriptLoader.js";

interface IAsset {
  key: string;
  path: string;
}

type CssAssetList = IAsset[];

const chainPromise = (...promisesList) =>
  promisesList.reduce((acc, curr) => {
    return acc.then(() => {
      const result = curr();
      if (result instanceof Promise || typeof result.then === "function") {
        return result;
      } else {
        return Promise.resolve(result);
      }
    });
  }, Promise.resolve());

export default class GlobalController extends AnakeenController.BusEvents.Listenable {
  /**
   * The singleton instance of the global controller;
   */
  private static _selfController: GlobalController;

  /**
   * Create script element
   * @param js
   * @param script
   * @private
   */
  private static _createScript(js, script: HTMLScriptElement) {
    const currentPath = js.path;
    const $script = $(script);
    $script.attr("data-id", js.key);
    $script.attr("data-src", currentPath);
    switch (js.type) {
      case "module":
        // Module mode injection
        $script.attr("type", "module");
        $script.text(Mustache.render(moduleTemplate, js));
        break;
      case "library":
        $script.attr("src", currentPath);
        break;
      default:
        // Global mode injection
        $script.attr("src", currentPath);
        break;
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
  private _verbose: boolean = false;
  private _scripts: { [scriptPath: string]: (controller: SmartElementController) => void } = {};

  private _isReady: boolean = false;

  private _domObserver: MutationObserver;

  private _registeredFunction: { [functionKey: string]: (controller: SmartElementController) => void } = {};
  /**
   * Constructor of the GlobalController. The GlobalController is a Singleton
   */
  // @ts-ignore
  public constructor(autoInit = true) {
    if (!GlobalController._selfController) {
      super();
      GlobalController._selfController = this;
      if (autoInit && !this._isReady) {
        this.init();
      }
    }
    return GlobalController._selfController;
  }

  public init() {
    if (!this._isReady) {
      const controllerDispatcher = require("./ControllerDispatcher").default;
      this._dispatcher = new controllerDispatcher();
      this._domObserver = new MutationObserver(mutations => this._onRemoveDOMController(mutations));
      this._domObserver.observe(document, { subtree: true, childList: true });
      this._isReady = true;
      this._dispatcher.on("injectCurrentSmartElementJS", (controller, event, properties, jsEvent) => {
        this._injectSmartElementJS(jsEvent);
      });
      this._dispatcher.on("renderCss", (controller, event, properties, css) => {
        this._onRenderCss(css);
      });
      this.emit("controllerReady", this);
      this._logVerbose("Global Anakeen Controller ready", "Global");
    }
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
   *
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
      this._registeredFunction[key] = scriptFunction;
      this._logVerbose(`register function with key ${key}`, "Asset", "JS");
    }
  }

  public setVerbose(enable: boolean = false) {
    this._verbose = enable;
    if (enable) {
      this._logVerbose("verbose mode enabled", "Global");
      // Log events
      EVENTS_LIST.forEach(event => {
        this._dispatcher.on(event, controller => {
          const seProps = controller.getProperties();
          this._logVerbose(`Smart element "${seProps.initid}" event ${event} triggered`, "Event");
        });
      });
    }
  }

  protected _onRemoveDOMController(mutationList: MutationRecord[]) {
    mutationList.forEach(mutation => {
      if (mutation.type === "childList" && mutation.removedNodes.length) {
        // tslint:disable-next-line:prefer-for-of
        for (let i = 0; i < mutation.removedNodes.length; i++) {
          const node = $(mutation.removedNodes[i]);
          const controllerIDs = node.find("[data-controller]").map((index, e) => $(e).attr("data-controller"));
          if (controllerIDs && controllerIDs.length) {
            for (let j = controllerIDs.length - 1; j >= 0; j--) {
              const controllerUID = controllerIDs[j];
              const controller = this.getScopedController(controllerUID) as SmartElementController;
              controller.tryToDestroy().finally(() => {
                this._logVerbose(
                  `remove scoped controller (${controllerUID}) for smart element "${
                    controller.getProperties().initid
                  }"`,
                  "Global"
                );
                this._dispatcher.removeController(controllerUID);
                this._cleanCss();
              });
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
  private _injectSmartElementJS(event) {
    const injectPromise = event.js.reduce((acc, currentJS) => {
      const currentPath = currentJS.path;
      // inject js if not alredy exist
      if ($('script[data-src="' + currentPath + '"]').length === 0) {
        return acc.then(() => {
          return new Promise((resolve, reject) => {
            load("", {
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
                  const functionKey = currentJS.function || currentJS.key;
                  this._registerScript(currentJS.path, this._getRegisteredFunction(functionKey));
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
      const customJS = _.pluck(event.js, "path");
      const promises = customJS.map(jsPath => {
        const promisify = Promise.resolve();
        if (typeof this._scripts[jsPath] === "function") {
          // eslint-disable-next-line no-useless-catch
          try {
            const scopedController = this.getScopedController(event.controller.uid) as SmartElementController;
            // Restrict the js to the current smart element view
            // @ts-ignore
            scopedController._defaultPersistent = false;
            const returnFunction: any = this._scripts[jsPath].call(this, scopedController);
            // If returnFunction is Promise => handle async operation, else immediately resolve
            return () =>
              promisify
                .then(() => {
                  // @ts-ignore
                  scopedController._defaultPersistent = true;
                  return returnFunction;
                })
                .catch(err => {
                  throw err;
                });
          } catch (err) {
            console.error(err);
            throw err;
          }
        }
        return () => promisify;
      });
      return chainPromise(...promises);
    });
  }

  /**
   * Register script function for later reuse after listener unbinding
   * @param scriptUrl
   * @param scriptFunction
   * @private
   */
  private _registerScript(scriptUrl: string, scriptFunction: (controller: SmartElementController) => void) {
    if (typeof scriptUrl === "string" && typeof scriptFunction === "function") {
      this._scripts[scriptUrl] = scriptFunction;
    }
    this.emit("_internal::scriptReady", scriptUrl);
  }

  private _getRegisteredFunction(key: string) {
    return this._registeredFunction[key];
  }

  private _logVerbose(message, ...categories) {
    if (this._verbose) {
      let strCategories = "";
      if (categories && categories.length) {
        strCategories = `[${categories.join("][")}]`;
      }
      const logMsg = `[Smart Element Controller]${strCategories} : ${message}`;
      window.console.log(logMsg);
      this.emit("controllerLog", logMsg);
    }
  }
}
