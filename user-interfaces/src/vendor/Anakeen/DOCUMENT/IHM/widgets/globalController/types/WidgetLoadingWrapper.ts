import DefaultLoading from "./DefaultLoading";

export default class WidgetLoadingWrapper extends DefaultLoading {
  protected loadingWidget: JQuery & { dcpLoading(...args): JQuery } = null;

  constructor(controller, $wLoading) {
    super(controller);
    this.loadingWidget = $wLoading;
  }

  addItem(number = 1): void {
    this.loadingWidget.dcpLoading("addItem", number);
  }

  hide(force?: boolean): void {
    this.loadingWidget.dcpLoading("hide", !!force);
  }

  isDisplayed(): boolean {
    return !!this.loadingWidget.dcpLoading("isDisplayed");
  }

  reset(): void {
    this.loadingWidget.dcpLoading("reset");
  }

  setLabel(label?: string): void {
    this.loadingWidget.dcpLoading("setLabel", label);
  }

  setNbItem(restItem): void {
    this.loadingWidget.dcpLoading("setNbItem", restItem);
  }

  setPercent(pc: number): void {
    this.loadingWidget.dcpLoading("setPercent", pc);
  }

  setTitle(title: string): void {
    this.loadingWidget.dcpLoading("setTitle", title);
  }

  show(label?: string, percent?: number): void {
    this.loadingWidget.dcpLoading("show", label, percent);
  }
}
