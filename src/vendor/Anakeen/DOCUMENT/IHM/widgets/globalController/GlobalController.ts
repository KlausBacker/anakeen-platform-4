import ControllerDispatcher from "./ControllerDispatcher";
import { AnakeenController } from "./types/ControllerTypes";
import SmartElementController from "./SmartElementController";
import ControllerUID = AnakeenController.Types.ControllerUID;
import * as $ from "jquery";
import * as _ from "underscore";
import load = require("little-loader");
import DOMReference = AnakeenController.Types.DOMReference;

type Asset = {
  key: string;
  path: string;
};

type CssAssetList = Asset[];

export default class GlobalController extends AnakeenController.BusEvents
  .Listenable {
  /**
   * The singleton instance of the global controller;
   */
  private static _selfController: GlobalController = null;

  /**
   * Controller actions dispatcher
   */
  protected _dispatcher: ControllerDispatcher = null;

  private _isReady: boolean = false;

  protected cssList: CssAssetList = [];
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
      const ControllerDispatcher = require("./ControllerDispatcher").default;
      this._dispatcher = new ControllerDispatcher();
      this._isReady = true;
      this._dispatcher.on("injectCurrentSmartElementJS", event => {
        this._injectSmartElementJS(event);
      });
      this._dispatcher.on("renderCss", css => {
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
    dom?: DOMReference,
    viewData?: AnakeenController.Types.ViewData
  ): ControllerUID {
    viewData = viewData || {
      initid: 0,
      revision: -1,
      viewId: "!defaultConsultation"
    };
    const controller = this._dispatcher.initController(dom, viewData);
    return controller.uid;
  }

  /**
   *
   * @param scopeId
   * @param operation
   * @param args
   */
  public execute(
    scopeId: ControllerUID,
    operation: string,
    ...args: any[]
  ) {
    this._dispatcher.dispatch(scopeId, operation, ...args);
  }

  /**
   *
   * @param eventName
   * @param callableFunction
   * @param scopeId
   */
  public addEventListener(
    eventName: string,
    callableFunction: () => void,
    scopeId: ControllerUID
  ) {}

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
    // console.log("union =>", this.cssList);
    // add custom css style
    const $head = $("head");
    const cssLinkTemplate = _.template(
      '<link rel="stylesheet" type="text/css" ' +
        'href="<%= path %>" data-id="<%= key %>" data-view="true">'
    );

    //Remove old CSS
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

  private _injectSmartElementJS(event: any) {
    event.injectPromise = new Promise((resolve, reject) => {
      const customJS = _.pluck(event.js, "path");
      Promise.all(
        customJS.map(currentPath => {
          if ($('script[data-controller="' + event.controller.uid + '"]').length === 0) {
            return new Promise(function addJs(resolve, reject) {
              // load(currentPath, {
              //   setup: script => {
              //     $(script).attr(
              //       "data-controller",
              //       event.controller.uid
              //     );
              //   },
              //   callback: err => {
              //     if (err) {
              //       reject(err);
              //     } else {
              //       resolve();
              //     }
              //   }
              // });
              const script = $(`<script type="module" async data-controller="${event.controller.uid}">
                    import ModuleInstallFunction from "${currentPath}";
                    ModuleInstallFunction(window.ank.smartElement.globalController.scope("${event.controller.uid}"));
                    // smartElementFunction("toto");
              </script>`).ready(() => {
                console.log("Loaded");
                resolve();
              });
              $(document.head).append(script);
            });
          } else {
            resolve();
          }
        })
      )
        .then(resolve)
        .catch(reject);
    });
  }
}
