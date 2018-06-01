import customEvent from 'vue-custom-element/src/utils/customEvent';

const getSenderElement = (element) => {
    let senderElement = element;
    while (senderElement && !senderElement.__vue_custom_element__) {
        senderElement = senderElement.parentNode;
    }

    if (!senderElement) {
        return element;
    }

    return senderElement;
};

const DEFAULT_EVENT_PARAMS = {
    bubbles: false,
    cancelable: false,
    detail: [],
};

const isEvent = (event, eventName) => {
    if (event === undefined || event === null) {
        return false;
    }

    if (event instanceof CustomEvent) {
        return true;
    }

    if (typeof event.type === 'string' && event.type === eventName
        && event.defaultPrevented && typeof event.preventDefault === 'function') {
        return true;
    }

    return false;
};

const createEvent = (eventName, params = {}, originalEvent) => {
    const options = Object.assign({}, DEFAULT_EVENT_PARAMS, params);
    let event;
    if (typeof window.CustomEvent === 'function') {
        event = new CustomEvent(eventName, options);
    } else {
        event = document.createEvent('CustomEvent');
        event.initCustomEvent(eventName, options.bubbles, options.cancelable, options.detail);
    }

    if (originalEvent !== undefined) {
        event.originalEvent = originalEvent;
    }

    return event;
};

const customEmit = (element, eventName, event) => {
    const senderElement = getSenderElement(element);
    if (senderElement) {
        senderElement.dispatchEvent(event);
    }

    return event;
};

const AnkVueEventMixin = {
    beforeCreate() {
        this.$createComponentEvent = createEvent;
        this.$emitAnkEvent = function emit(eventName, ...args) {
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
                        componentEvent = customEvent(eventName, ...args);
                    }

                    customEmit(this.$el, eventName, componentEvent);

                    this.__proto__ && this.__proto__.$emit.call(this, eventName, componentEvent); // eslint-disable-line no-proto

                    // Return if event is cancelled or not
                    return !componentEvent.defaultPrevented;
                };
    },

    created() {

    },
};

export default AnkVueEventMixin;
