export default class VueEventBus {
  protected events: { [key: string]: Array<(...args: any[]) => void> } = {};
  public emit(eventName, ...args) {
    if (this.events[eventName]) {
      this.events[eventName].forEach(cb => {
        cb.call(null, ...args);
      });
    }
  }

  public on(eventName: string, callback: (...args: any[]) => void) {
    if (this.events[eventName]) {
      this.events[eventName].push(callback);
    } else {
      this.events[eventName] = [callback];
    }
  }

  public off(eventName: string) {
    if (this.events[eventName]) {
      delete this.events[eventName];
    }
  }
}
