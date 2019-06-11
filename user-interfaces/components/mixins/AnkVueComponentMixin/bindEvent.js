// jscs:disable disallowFunctionDeclarations
import { isEvent } from "./eventUtilsMixin";

const ERROR_CODES = {
  UICOMPONENT0001: {
    code: "UICOMPONENT0001",
    message: "Bad arguments for %s"
  },
  UICOMPONENT0002: {
    code: "UICOMPONENT0002",
    message: "%s is not a valid jQuery selector"
  },
  UICOMPONENT0003: {
    code: "UICOMPONENT0003",
    message: "The element targetted by %s does not contain any %s property"
  },
  UICOMPONENT0004: {
    code: "UICOMPONENT0004",
    message:
      'Incorrect bindEvent format "%s", it should be : eventName:jquerySelector.functionName'
  },
  UICOMPONENT0005: {
    code: "UICOMPONENT0005",
    message: 'The action name "%s" does not match with a function name'
  }
};

const error = (errorCode, ...args) => {
  const displayErrorMsg = `Ank Component Mixin error (${
    ERROR_CODES[errorCode].code
  }) : ${sprintf(ERROR_CODES[errorCode].message, args)}`;
  console.error(displayErrorMsg);
  throw displayErrorMsg;
};

const sprintf = (str, ...substitutes) => {
  let counter = 0;
  return str.replace(/%s/g, () => substitutes[counter++]);
};

const getSpecialType = stringSelector => {
  switch (stringSelector) {
    case "window":
      return window;
    case "document":
      return document;
    default:
      return stringSelector;
  }
};

// Parse full format bind-event prop "my-event: #myElement.other.method, other-event: #myElement.other.method2"
function analyzeQuickBindingArg(arg) {
  if (typeof arg !== "string") {
    error("UICOMPONENT0001", "quick binding attribute");
  }

  // Remove blanks
  const sanitized = arg.replace(/\s/g, "");
  const allBindings = sanitized.split(",");

  allBindings.forEach(analyzeBinding.bind(this));
}

// Parse binding "my-event:#myElement.other.method"
function analyzeBinding(binding) {
  const tokens = binding.split(":");
  let eventName;
  let ruleContent;
  let actionRgx;
  if (tokens && tokens.length) {
    switch (tokens.length) {
      case 2:
        eventName = tokens[0];
        ruleContent = tokens[1];
        actionRgx = ruleContent.match(/(.+?)\.(.+)/);
        if (actionRgx && actionRgx.length > 2) {
          const selector = actionRgx[1];
          const action = actionRgx[2];
          analyzeAction.call(this, eventName, selector, action);
        } else {
          error("UICOMPONENT0004", tokens);
        }

        break;
      default:
        error("UICOMPONENT0004", tokens);
    }
  }
}

// Parse action "#myElement.other.method"
function analyzeAction(eventName, selector, action) {
  try {
    const realSelector = getSpecialType(selector);
    const elements = this.$(realSelector);
    if (elements && elements.length) {
      this._ank_protected.bindedEvents.push(eventName);
      this.$on(eventName, (...emitArgs) => {
        const possibleEvent = emitArgs[0] || [];
        let eventArgs = [];
        if (isEvent(possibleEvent, eventName)) {
          eventArgs = possibleEvent.detail || [];
          eventArgs = eventArgs.concat(emitArgs.slice(1));
        } else {
          eventArgs = emitArgs;
        }

        elements.each((index, elem) => {
          const actionTokens = action.split(".");
          if (actionTokens && actionTokens.length) {
            let value = actionTokens.reduce((a, b) => {
              if (a[b] === undefined || a[b] === null) {
                error("UICOMPONENT0003", selector, b);
              }

              return a[b];
            }, elem);
            if (typeof value === "function") {
              value(...eventArgs);
            } else {
              error("UICOMPONENT0005", actionTokens);
            }
          }
        });
      });
    }
  } catch (e) {
    console.error(e);
    error("UICOMPONENT0002", selector);
  }
}

const AnkMixin = {
  props: {
    bindEvent: {
      type: String,
      default: ""
    }
  },
  created() {
    const attachQuickBindingEvents = data => {
      analyzeQuickBindingArg.call(this, data);
    };

    const listenPropsUpdateRequest = () => {
      this.$on("update:props", newProps => {
        if (this.$options && this.$options.propsData) {
          Object.keys(this.$options.propsData).forEach(propName => {
            if (newProps[propName]) {
              const element = this.$(this.$el).parent();
              element.prop(propName, newProps[propName]);
            }
          });
        }
      });
    };

    this._ank_protected = {
      // List quick binded events
      bindedEvents: []
    };

    if (this.bindEvent) {
      attachQuickBindingEvents(this.bindEvent);
    }

    listenPropsUpdateRequest();
  },

  beforeDestroy() {
    this._ank_protected.bindedEvents.forEach(e => {
      this.$off(e);
    });
  }
};

export default AnkMixin;
