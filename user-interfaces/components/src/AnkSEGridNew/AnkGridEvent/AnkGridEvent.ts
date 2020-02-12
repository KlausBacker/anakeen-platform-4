/**
 * Anakeen Grid component event class
 */
class GridEvent {
  public type;
  public cancelable;
  public target;
  public data;
  public defaultPrevented;

  /**
   * Constructor.
   * @param data - The data send to the event
   * @param target - The element that trigger the event
   * @param cancelable - if the event is cancelable
   * @param type - Type of the event
   */
  constructor(data = null, target = null, cancelable = true, type = "GridEvent") {
    this.type = type;
    this.cancelable = cancelable;
    this.target = target;
    this.data = data;
    this.defaultPrevented = false;
  }

  /**
   * Cancel the event if it is cancelable
   */
  preventDefault() {
    if (this.cancelable) {
      this.defaultPrevented = true;
    } else {
      console.warn(`${this.type} - The event is not cancelable`);
    }
  }

  /**
   * Test if the event is cancelled or not
   * @return {boolean} true if the event is cancelled, false otherwise
   */
  isDefaultPrevented() {
    return this.cancelable && this.defaultPrevented;
  }
}
export default GridEvent;
