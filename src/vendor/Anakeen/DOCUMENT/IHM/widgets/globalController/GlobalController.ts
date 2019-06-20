import ControllerDispatcher from "./ControllerDispatcher";
import { AnakeenController } from "./types/ControllerTypes";

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
    }
  }

  /**
   *
   * @param scopeId
   */
  public scope(scopeId: string) {}

  /**
   *
   * @param dom
   * @param viewData
   */
  public addSmartElement(
    dom?: AnakeenController.Types.DOMReference,
    viewData?: AnakeenController.Types.ViewData
  ) {
    viewData = viewData || {
      initid: 0,
      revision: -1,
      viewId: "!defaultConsultation"
    };
    // this.dispatcher.initController(dom, viewData);
  }

  /**
   *
   * @param scopeId
   * @param operation
   * @param args
   */
  public execute(scopeId: string, operation: string, ...args: any[]) {
    debugger;
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
    scopeId: string
  ) {}
}
