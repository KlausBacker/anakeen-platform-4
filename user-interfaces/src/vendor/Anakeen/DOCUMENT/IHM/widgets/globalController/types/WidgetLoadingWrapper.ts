import DefaultLoading from "./DefaultLoading";

export default class WidgetLoadingWrapper extends DefaultLoading {
  protected loadingWidget: JQuery & { dcpLoading(...args): JQuery } = null;

  constructor(controller, $wLoading) {
    super(controller);
    this.loadingWidget = $wLoading;
  }

  addItem(number = 1): void {
    try {
      this.loadingWidget.dcpLoading("addItem", number);
    } catch (e) {
      //unable to display the loading, that can be normal
    }
  }

  hide(force?: boolean): void {
    try {
      this.loadingWidget.dcpLoading("hide", !!force);
    } catch (e) {
      //unable to display the loading, that can be normal
    }
  }

  isDisplayed(): boolean {
    try {
      return !!this.loadingWidget.dcpLoading("isDisplayed");
    } catch (e) {
      //unable to display the loading, that can be normal
    }
  }

  reset(): void {
    try {
      this.loadingWidget.dcpLoading("reset");
    } catch (e) {
      //unable to display the loading, that can be normal
    }
  }

  setLabel(label?: string): void {
    try {
      this.loadingWidget.dcpLoading("setLabel", label);
    } catch (e) {
      //unable to display the loading, that can be normal
    }
  }

  setNbItem(restItem): void {
    try {
      this.loadingWidget.dcpLoading("setNbItem", restItem);
    } catch (e) {
      //unable to display the loading, that can be normal
    }
  }

  setPercent(pc: number): void {
    try {
      this.loadingWidget.dcpLoading("setPercent", pc);
    } catch (e) {
      //unable to display the loading, that can be normal
    }
  }

  setTitle(title: string): void {
    try {
      this.loadingWidget.dcpLoading("setTitle", title);
    } catch (e) {
      //unable to display the loading, that can be normal
    }
  }

  show(label?: string, percent?: number): void {
    try {
      this.loadingWidget.dcpLoading("show", label, percent);
    } catch (e) {
      //unable to display the loading, that can be normal
    }
  }
}
