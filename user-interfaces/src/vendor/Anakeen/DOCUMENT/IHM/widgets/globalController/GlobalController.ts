/* tslint:disable:variable-name */
import DOMReference = AnakeenController.Types.DOMReference;
// @ts-ignore
import moduleTemplate from "!!raw-loader!./utils/templates/module.js.mustache";
import ControllerUID = AnakeenController.Types.ControllerUID;
import * as $ from "jquery";
import * as Mustache from "mustache";
import * as _ from "underscore";
import ControllerDispatcher from "./ControllerDispatcher";
import SmartElementController from "./SmartElementController";
import { AnakeenController } from "./types/ControllerTypes";
import load from "./utils/ScriptLoader.js";
import ListenableEventCallable = AnakeenController.BusEvents.ListenableEventCallable;
import ListenableEvent = AnakeenController.BusEvents.ListenableEvent;

type Asset = {
  key: string;
  path: string;
};

type CssAssetList = Asset[];

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

export default class GlobalController extends AnakeenController.BusEvents
  .Listenable {
  /**
   * The singleton instance of the global controller;
   */
  private static _selfController: GlobalController;

  /**
   * Controller actions dispatcher
   */
  protected _dispatcher: ControllerDispatcher;

  protected cssList: CssAssetList = [];
  private _scripts: { [key: string]: (SmartElementController) => void } = {};

  private _isReady: boolean = false;

  private _domObserver: MutationObserver;
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
      this._domObserver = new MutationObserver((...args) =>
        this._onRemoveDOMController(...args)
      );
      this._domObserver.observe(document, { subtree: true, childList: true });
      this._isReady = true;
      this._dispatcher.on(
        "injectCurrentSmartElementJS",
        (controller, event, properties, jsEvent) => {
          this._injectSmartElementJS(jsEvent);
        }
      );
      this._dispatcher.on("renderCss", (controller, event, properties, css) => {
        this._onRenderCss(css);
      });
      this.emit("controllerReady", this);
    }
  }

  public on(
    eventName: string,
    callback: AnakeenController.BusEvents.ListenableEventCallable
  ) {
    super.on(eventName, callback);
    if (eventName === "controllerReady" && this._isReady) {
      // If controller is already ready, execute callback immediatly
      if (callback) {
        callback.call(null, this);
      }
    }
  }

  /**
   *
   * @param scopeId
   */
  public scope(
    scopeId?: ControllerUID | DOMReference
  ): SmartElementController | SmartElementController[] {
    if (scopeId === undefined) {
      return this._dispatcher.getControllers() as SmartElementController[];
    }
    return this._dispatcher.getController(scopeId);
  }

  /**
   *
   * @param dom
   * @param viewData
   */
  public addSmartElement(
    dom: DOMReference,
    viewData?: AnakeenController.Types.IViewData,
    options?
  ): ControllerUID {
    viewData = viewData || {
      initid: 0,
      revision: -1,
      viewId: "!defaultConsultation"
    };
    const controller = this._dispatcher.initController(dom, viewData, options);
    return controller.uid;
  }

  /**
   *
   * @param scopeId
   * @param operation
   * @param args
   */
  public execute(scopeId: ControllerUID, operation: string, ...args: any[]) {
    this._dispatcher.dispatch(scopeId, operation, ...args);
  }

  // public addEventListener(eventType: string, options: object, callback) {
  //
  // }

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
    if (
      _.isObject(eventType) &&
      _.isUndefined(eventOptions) &&
      _.isUndefined(eventCallback)
    ) {
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

  protected _onRemoveDOMController(mutationList: MutationRecord[], observer) {
    mutationList.forEach(mutation => {
      if (mutation.type === "childList" && mutation.removedNodes.length) {
        // tslint:disable-next-line:prefer-for-of
        for (let i = 0; i < mutation.removedNodes.length; i++) {
          const node = $(mutation.removedNodes[i]);
          const controllerIDs = node
            .find("[data-controller]")
            .map((i, e) => $(e).attr("data-controller"));
          if (controllerIDs && controllerIDs.length) {
            for (let j = controllerIDs.length - 1; j >= 0; j--) {
              const controllerUID = controllerIDs[j];
              this._dispatcher.removeController(controllerUID);
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

  private _extractNewCss(
    currentList: CssAssetList,
    newList: CssAssetList
  ): CssAssetList {
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

  private _onRenderCss(customCss: CssAssetList) {
    this.cssList.push(...this._extractNewCss(this.cssList, customCss));
    // add custom css style
    const $head = $("head");
    const cssLinkTemplate = _.template(
      '<link rel="stylesheet" type="text/css" ' +
        'href="<%= path %>" data-id="<%= key %>" data-view="true">'
    );

    // Remove old CSS
    _.each($("link[data-view=true]"), currentLink => {
      if (
        _.find(this.cssList, currentCss => {
          return $(currentLink).data("id") === currentCss.key;
        }) === undefined
      ) {
        $(currentLink).remove();
      }
    });
    // Inject new CSS
    _.each(this.cssList, cssItem => {
      const $existsLink = $(`link[rel=stylesheet][data-id=${cssItem.key}]`);

      if ($existsLink.length === 0) {
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
   * Create script element
   * @param js
   * @param script
   * @private
   */
  private _createScript(js, script: HTMLScriptElement) {
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
   * Inject smart element js in the page
   * @param event
   * @private
   */
  private _injectSmartElementJS(event: any) {
    const injectPromises = event.js.map(currentJS => {
      const currentPath = currentJS.path;
      // inject js if not alredy exist
      if ($('script[data-src="' + currentPath + '"]').length === 0) {
        return new Promise((resolve, reject) => {
          load("", {
            callback: err => {
              if (err) {
                reject(err);
              } else if (currentJS.type === "global") {
                resolve();
              } else {
                const functionName = currentJS.function || currentJS.key;
                this._registerScript(
                  currentJS.path,
                  window[functionName] || global[functionName]
                );
              }
            },
            setup: script => this._createScript(currentJS, script)
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
      } else {
        // Script function is already available
        return Promise.resolve();
      }
    });
    // Set inject promise
    event.injectPromise = Promise.all(injectPromises).then(() => {
      // Execute script function with scoped controller
      const customJS = _.pluck(event.js, "path");
      const promises = customJS.map(jsPath => {
        const promisify = Promise.resolve();
        if (typeof this._scripts[jsPath] === "function") {
          try {
            const returnFunction: any = this._scripts[jsPath].call(
              this,
              this.scope(event.controller.uid)
            );
            // If returnFunction is Promise => handle async operation, else immediately resolve
            return () => promisify.then(() => returnFunction);
          } catch (err) {
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
  private _registerScript(scriptUrl, scriptFunction) {
    if (typeof scriptUrl === "string" && typeof scriptFunction === "function") {
      this._scripts[scriptUrl] = scriptFunction;
      this.emit("_internal::scriptReady", scriptUrl);
    }
  }
}
