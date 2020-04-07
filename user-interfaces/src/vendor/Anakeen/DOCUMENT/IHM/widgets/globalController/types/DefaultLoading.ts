import { AnakeenController } from "./ControllerTypes";
import ISmartElementLoading = AnakeenController.SmartElement.ISmartElementLoading;
import SmartElementController from "../SmartElementController";

export default class DefaultLoading implements ISmartElementLoading {
  protected controller: SmartElementController;
  protected displayed = false;

  constructor(controller) {
    this.controller = controller;
  }

  addItem(number?: number): void {}

  hide(force?: boolean): void {
    this.displayed = false;
  }

  isDisplayed(): boolean {
    return this.displayed;
  }

  reset(): void {}

  setLabel(label?: string): void {}

  setNbItem(restItem: number): void {}

  setPercent(pc: number): void {}

  setTitle(title: string): void {}

  show(label?: string, percent?: number): void {
    this.displayed = true;
  }
}
