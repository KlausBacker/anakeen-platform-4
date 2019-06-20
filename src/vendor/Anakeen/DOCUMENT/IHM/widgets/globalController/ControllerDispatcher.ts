import SmartElementController from "./SmartElementController";
import { AnakeenController } from "./types/ControllerTypes";
import DOMReference = AnakeenController.Types.DOMReference;
import ViewData = AnakeenController.Types.ViewData;

export default class ControllerDispatcher {
  protected controllers: { [key: string]: SmartElementController } = {};

  public dispatch(scopeId: string, action: string, ...args: any[]) {}

  public initController(dom: DOMReference, viewData: ViewData) {
    const key = `controller/${viewData.initid}/${viewData.viewId}/${
      viewData.revision
    }`;
    this.controllers[key] = new SmartElementController(dom, viewData);
  }
}
