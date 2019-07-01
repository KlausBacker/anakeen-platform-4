import SmartElementController from "./SmartElementController";
import { AnakeenController } from "./types/ControllerTypes";
import DOMReference = AnakeenController.Types.DOMReference;
import ViewData = AnakeenController.Types.ViewData;
import ControllerUniqueID = AnakeenController.Types.ControllerUID;

type ControllersMap = { [key: string]: SmartElementController };
export default class ControllerDispatcher extends AnakeenController.BusEvents.Listenable {
  protected _controllers: ControllersMap = {};

  public dispatch(scopeId: ControllerUniqueID, action: string, ...args: any[]) {
    const controller = this.getController(scopeId);
    if (controller) {
      if (typeof controller[action] === "function") {
        controller[action].call(controller[action], ...args);
      }
    }
  }

  public initController(dom: DOMReference, viewData: ViewData) {
    const controller = new SmartElementController(dom, viewData);
    this._controllers[controller.uid] = controller;
    controller.on("injectCurrentSmartElementJS", (...args) => {
      this.emit("injectCurrentSmartElementJS", ...args);
    });
    controller.on("renderCss", (...args) => {
      this.emit("renderCss", ...args);
    });
    return controller;
  }

  public getController(scopeId: ControllerUniqueID | DOMReference) {
    if (typeof scopeId === "string") {
      return this._controllers[scopeId];
    } else {
      const element = $(scopeId);
      if (element.length) {
        const controllerUid = element.attr("data-controller");
        if (typeof controllerUid === "string") {
          return this._controllers[controllerUid];
        }
      }
    }
  }

  public getControllers(asObject?: boolean): SmartElementController[] | ControllersMap  {
    if (asObject) {
      return this._controllers;
    }
    return Object.keys(this._controllers).map(k => this._controllers[k]);
  }
}
