export default class SETabsEvent {
  protected defaultPrevented: boolean;
  protected cancelable: boolean;
  public type: string;
  public data: any;

  constructor(
    data: any = null,
    type: string = "SETabsEvent",
    cancelable: boolean = true
  ) {
    this.defaultPrevented = false;
    this.cancelable = cancelable;
    this.type = type;
    this.data = data;
  }

  public isDefaultPrevented() {
    return this.cancelable && this.defaultPrevented;
  }

  public preventDefault() {
    if (this.cancelable && !this.defaultPrevented) {
      this.defaultPrevented = true;
    }
  }
}
