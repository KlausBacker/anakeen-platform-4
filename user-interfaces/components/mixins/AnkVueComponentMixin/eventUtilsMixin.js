const DEFAULT_EVENT_PARAMS = {
  bubbles: false,
  cancelable: false,
  detail: []
};

/**
 * Get the dom element that send the event
 * @param element
 * @returns {*}
 */
const getSenderElement = element => {
  let senderElement = element;
  while (senderElement && !senderElement.__vue_custom_element__) {
    senderElement = senderElement.parentNode;
  }

  if (!senderElement) {
    return element;
  }

  return senderElement;
};

/**
 * Check if the event object is an event type or note
 *
 * @param event
 * @param eventName
 * @returns {boolean}
 */
export const isEvent = (event, eventName) => {
  if (event === undefined || event === null) {
    return false;
  }

  if (event instanceof CustomEvent) {
    return true;
  }

  if (
    typeof event.type === "string" &&
    event.type === eventName &&
    event.defaultPrevented &&
    typeof event.preventDefault === "function"
  ) {
    return true;
  }

  return false;
};

/**
 * Emit current event on the root element of the component
 *
 * @param element
 * @param eventName
 * @param event
 * @returns {*}
 */
const customEmit = (element, eventName, event) => {
  const senderElement = getSenderElement(element);
  if (senderElement) {
    try {
      senderElement.dispatchEvent(event);
    } catch (e) {
      console.error(e);
    }
  }

  return event;
};

/**
 * Create an event object
 *
 * @param eventName event name for the generated event object
 * @param params object transfered to the event handler (cancelable: true if the event is cancelable)
 * @param originalEvent (optionnal) original event
 * @returns CustomEvent<any>
 */
export const createEvent = (eventName, params = {}, originalEvent) => {
  const options = Object.assign({}, DEFAULT_EVENT_PARAMS, params);
  let event;
  if (typeof window.CustomEvent === "function") {
    event = new CustomEvent(eventName, options);
  } else {
    event = document.createEvent("CustomEvent");
    event.initCustomEvent(
      eventName,
      options.bubbles,
      options.cancelable,
      options.detail
    );
  }

  if (originalEvent !== undefined) {
    event.originalEvent = originalEvent;
  }

  return event;
};

/**
 * Mixin that add two functions $createComponentEvent to create events, $emitAnkEvent that emit created event
 * @type {{beforeCreate(): void}}
 */
export const $createComponentEvent = createEvent;
export const $emitAnkEvent = function emit(eventName, ...args) {
  let componentEvent;
  if (isEvent(args[0], eventName)) {
    componentEvent = args[0];
    const others = args.slice(1);
    if (others.length) {
      if (componentEvent.detail && componentEvent.detail.length) {
        componentEvent.detail = componentEvent.detail.concat(others);
      } else {
        componentEvent.detail = [...others];
      }
    }
  } else {
    componentEvent = createEvent(eventName, {
      cancelable: true,
      detail: [...args]
    });
  }

  customEmit(this.$el, eventName, componentEvent);

  //this.__proto__ && this.__proto__.$emit.call(this, eventName, componentEvent); // eslint-disable-line no-proto

  // Return false if event is cancelled (dispatchEvent behavior)
  return !componentEvent.defaultPrevented;
};

/**
 * Mixin that add two functions $createComponentEvent to create events, $emitAnkEvent that emit created event
 * @type {{beforeCreate(): void}}
 */
const AnkVueEventMixin = {
  beforeCreate() {
    this.$createComponentEvent = $createComponentEvent;
    this.$emitAnkEvent = $emitAnkEvent;
  }
};

export default AnkVueEventMixin;
