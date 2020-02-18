/* tslint:disable:variable-name */
// tslint:disable:max-classes-per-file

import { Component, Vue } from "vue-property-decorator";
interface IAnkVueEventOptions {
  cancelable?: boolean;
  data?: any;
  target?: any;
  vueInstance?: any;
}

const DEFAULT_OPTIONS: IAnkVueEventOptions = { cancelable: true, data: null, target: null, vueInstance: null };
class AnkVueEvent {
  public name: string;
  public target: any;
  public data: any;
  public detail: any;
  private _defaultPrevented: boolean;
  private readonly _cancelable: boolean;

  constructor(eventName, opts: IAnkVueEventOptions = DEFAULT_OPTIONS) {
    const options = Object.assign({}, DEFAULT_OPTIONS, opts);
    this.name = eventName;
    this._defaultPrevented = false;
    this._cancelable = options.cancelable;
    this.target = options.target;
    this.data = options.data;
    this.detail = this.data;
  }

  public isDefaultPrevented() {
    return this._cancelable && this._defaultPrevented;
  }

  public preventDefault() {
    this._defaultPrevented = true;
  }

  public getData() {
    return this.data;
  }

  public setData(...data) {
    this.data = data;
  }
}

/**
 * Mixin that add two functions $createComponentEvent to create events, $emitAnkEvent that emit created event
 * @type {{beforeCreate(): void}}
 */
@Component
export default class AnkVueEventMixin extends Vue {
  /**
   * Send a cancelable vue event.
   * @param eventName
   * @param eventData
   * @return {boolean} false if event is prevented
   */
  public $emitCancelableEvent(eventName, ...eventData) {
    const event = new AnkVueEvent(eventName, { target: this.$el, vueInstance: this, data: eventData });
    this.$emit(eventName, event);
    return event;
  }

  public $createEvent(eventName: string, opts: IAnkVueEventOptions = DEFAULT_OPTIONS) {
    return new AnkVueEvent(eventName, opts);
  }
}
