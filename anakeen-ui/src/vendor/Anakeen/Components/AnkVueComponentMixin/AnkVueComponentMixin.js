// jscs:disable disallowFunctionDeclarations
import AnkVueEvent, { isEvent } from './AnkVueEvent';
const PUBLIC_METHODS_FIELD = 'publicMethods';

const IGNORED_METHODS_TOKEN = ['_', '$'];

const ERROR_CODES = {
    UICOMPONENT0001: {
        code: 'UICOMPONENT0001',
        message: 'Bad arguments for %s',
    },
    UICOMPONENT0002: {
        code: 'UICOMPONENT0002',
        message: '%s is not a valid jQuery selector',
    },
    UICOMPONENT0003: {
        code: 'UICOMPONENT0003',
        message: 'The element targetted by %s does not contain any %s property',
    },
};

const error = (errorCode, ...args) => {
    const displayErrorMsg = `Ank Component Mixin error : ${sprintf(ERROR_CODES[errorCode].message, args)}`;
    console.error(displayErrorMsg);
    throw displayErrorMsg;
};

const sprintf = (str, ...substitutes) => {
    let counter = 0;
    return str.replace(/%s/g, () => substitutes[counter++]);
};

const getSpecialType = (stringSelector) => {
    switch (stringSelector) {
        case 'window':
            return window;
        case 'document':
            return document;
        default:
            return stringSelector;
    }
};

const isPublicMethod = (methodName) => {
    let isPublic = true;
    let i = 0;
    while (isPublic && i < IGNORED_METHODS_TOKEN.length) {
        if (methodName.startsWith(IGNORED_METHODS_TOKEN[i++])) {
            isPublic = false;
        }
    }

    return isPublic;
};

// Parse full format bind-event prop "my-event: #myElement.other.method, other-event: #myElement.other.method2"
function analyzeQuickBindingArg(arg) {
    if (typeof arg !== 'string') {
        error('UICOMPONENT0001', 'quick binding attribute');
    }

    // Remove blanks
    const sanitized = arg.replace(/\s/g, '');
    const allBindings = sanitized.split(',');

    allBindings.forEach(analyzeBinding.bind(this));
}

// Parse binding "my-event:#myElement.other.method"
function analyzeBinding(binding) {
    const tokens = binding.split(':');
    if (tokens && tokens.length) {
        switch (tokens.length) {
            case 2:
                const eventName = tokens[0];
                const ruleContent = tokens[1];
                const actionRgx = ruleContent.match(/(.+?)\.(.+)/);
                if (actionRgx && actionRgx.length > 2) {
                    const selector = actionRgx[1];
                    const action = actionRgx[2];
                    analyzeAction.call(this, eventName, selector, action);
                }

                break;
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
                    const actionTokens = action.split('.');
                    if (actionTokens && actionTokens.length) {
                        let value = actionTokens.reduce((a, b) => {
                            if (a[b] === undefined || a[b] === null) {
                                error('UICOMPONENT0003', selector, b);
                            }

                            return a[b];
                        }, elem);
                        if (typeof value === 'function') {
                            value(...eventArgs);
                        }
                    }

                });
            });
        }
    } catch (e) {
        console.error(e);
        error('UICOMPONENT0002', selector);
    }
}

const AnkMixin = {
    mixins: [AnkVueEvent],
    props: {
        bindEvent: {
            type: String,
            default: '',
        },
    },
    created() {
        this._ank_protected = {
            // Enable quick event binding feature
            attachQuickBindingEvents: (data) => {
                analyzeQuickBindingArg.call(this, data);
            },

            // Expose public methods (from method sections) in DOM props
            attachPublicMethods: () => {
                // Attach public methods
                const _this = this;
                if (this && this.$options && this.$options.methods) {
                    Object.keys(this.$options.methods).forEach((methodName) => {
                        if (isPublicMethod(methodName)) {
                            const method = {
                                [methodName]: (...args) => {
                                    try {
                                        const ret = _this[methodName].call(_this, ...args);
                                        return ret;
                                    } catch (e) {
                                        throw e;
                                    }
                                },
                            };
                            this.$(this.$el).parent().prop(PUBLIC_METHODS_FIELD, (index, oldPropVal) => {
                                if (!oldPropVal) {
                                    return method;
                                } else {
                                    return Object.assign({}, oldPropVal, method);
                                }
                            });
                        }
                    });
                }
            },

            bindedEvents: [],

            listenPropsUpdateRequest: () => {
                this.$on('update:props', (newProps) => {
                    Object.keys(this.$options.propsData).forEach(propName => {
                        if (newProps[propName]) {
                            const element = this.$(this.$el).parent();
                            element.prop(propName, newProps[propName]);
                        }
                    });
                });
            },
        };
    },

    mounted() {
        const ready = () => {
            if (this.bindEvent) {
                this._ank_protected.attachQuickBindingEvents(this.bindEvent);
            }

            this._ank_protected.listenPropsUpdateRequest();
            this._ank_protected.attachPublicMethods();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', ready);
        } else {
            ready();
        }
    },

    beforeDestroy() {
        this._ank_protected.bindedEvents.forEach((e) => {
            this.$off(e);
        });
    },

};

export default AnkMixin;
