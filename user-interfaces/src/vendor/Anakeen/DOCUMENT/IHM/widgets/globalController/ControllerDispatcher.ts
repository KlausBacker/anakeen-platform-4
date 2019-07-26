/* eslint-disable no-unused-vars */
/* tslint:disable:variable-name */
import * as $ from "jquery";
import SmartElementController from "./SmartElementController";
import { AnakeenController } from "./types/ControllerTypes";
import DOMReference = AnakeenController.Types.DOMReference;
import ViewData = AnakeenController.Types.IViewData;
import ControllerUniqueID = AnakeenController.Types.ControllerUID;
import EVENTS_LIST = AnakeenController.SmartElement.EVENTS_LIST;

interface IControllersMap {
  [key: string]: SmartElementController;
}
export default class ControllerDispatcher extends AnakeenController.BusEvents.Listenable {
  protected _controllers: IControllersMap = {};

  public dispatch(scopeId: ControllerUniqueID, action: string, ...args: any[]) {
    const controller = this.getController(scopeId);
    if (controller) {
      if (typeof controller[action] === "function") {
        controller[action].call(controller[action], ...args);
      }
    }
  }

  public initController(dom: DOMReference, viewData: ViewData, options?) {
    const _dispatcher = this;
    const globalEventHandler = function(eventType, ...args) {
      // @ts-ignore
      _dispatcher.emit(eventType, this, ...args);
      if (options && typeof options.globalHandler === "function") {
        // @ts-ignore
        options.globalHandler.call(this, eventType, ...args);
      }
    };

    const controller = new SmartElementController(dom, viewData, options, globalEventHandler);
    this._controllers[controller.uid] = controller;
    return controller;
  }

  public removeController(controllerUID: ControllerUniqueID) {
    delete this._controllers[controllerUID];
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

  public getControllers(asObject?: boolean): SmartElementController[] | IControllersMap {
    if (asObject) {
      return this._controllers;
    }
    return Object.keys(this._controllers).map(k => this._controllers[k]);
  }
}
