/* eslint-disable no-unused-vars */
/* tslint:disable:variable-name max-classes-per-file */
import $ from "jquery";
import ControllerNotFoundError from "./ControllerNotFoundError";
import SmartElementController from "./SmartElementController";
import { AnakeenController } from "./types/ControllerTypes";
import DOMReference = AnakeenController.Types.DOMReference;
import ViewData = AnakeenController.Types.IViewData;
import ControllerUniqueID = AnakeenController.Types.ControllerUID;
import ControllerOptions = AnakeenController.Types.IControllerOptions;

interface IControllersMap {
  [key: string]: SmartElementController;
}
class ControllerUIDError extends Error {}

export default class ControllerDispatcher extends AnakeenController.BusEvents.Listenable {
  protected _controllers: IControllersMap = {};

  public dispatch(action: string, check: (controller: SmartElementController) => boolean = () => true, ...args: any[]) {
    const result = [];
    Object.keys(this._controllers)
      .filter(uid => typeof check !== "function" || check(this._controllers[uid]))
      .forEach(uid => {
        const controller = this._controllers[uid];
        if (typeof controller[action] === "function") {
          result.push(controller[action].call(controller, ...args));
        } else {
          result.push(null);
        }
      });
    return result;
  }

  /**
   * Create a scoped controller
   * @param dom
   * @param viewData
   * @param options
   * @throws ControllerUIDError if the controller name given is already used
   */
  public initController(dom: DOMReference, viewData: ViewData, options?: ControllerOptions) {
    const _dispatcher = this;
    const globalEventHandler = function(eventType, ...args) {
      // @ts-ignore
      _dispatcher.emit(eventType, this, ...args);
      if (options && typeof options.globalHandler === "function") {
        // @ts-ignore
        options.globalHandler.call(this, eventType, ...args);
      }
    };
    if (options && options.controllerName) {
      this._checkExistControllerName(options.controllerName);
    }
    const controller = new SmartElementController(dom, viewData, options, globalEventHandler);
    this._controllers[controller.uid] = controller;
    return controller;
  }

  public removeController(controllerUID: ControllerUniqueID) {
    delete this._controllers[controllerUID];
  }

  public getController(scopeId: ControllerUniqueID | DOMReference): SmartElementController {
    let controller;
    if (typeof scopeId === "string") {
      controller = this._controllers[scopeId];
    } else {
      const element = $(scopeId);
      if (element.length) {
        const controllerUid = element.closest("[data-controller]").attr("data-controller");
        if (typeof controllerUid === "string") {
          controller = this._controllers[controllerUid];
        }
      }
    }
    if (!controller) {
      throw new ControllerNotFoundError(`The controller with the uid "${scopeId}" does not exist`);
    }
    return controller;
  }

  public getControllers(asObject?: boolean): SmartElementController[] | IControllersMap {
    if (asObject) {
      return this._controllers;
    }
    return Object.keys(this._controllers).map(k => this._controllers[k]);
  }

  /**
   * Check if a controller with a given name already exists
   * @param controllerName
   * @throws ControllerUIDError
   * @private
   */
  private _checkExistControllerName(controllerName: string) {
    if (this._controllers[controllerName] !== undefined) {
      throw new ControllerUIDError(`The controller with the name "${controllerName}" already exists.`);
    }
  }
}
