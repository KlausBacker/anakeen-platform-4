// jscs:disable disallowFunctionDeclarations
export default {

    props: {
        bindData: {
            type: String,
            default: '',
        },
        bindEvent: {
            type: String,
            default: '',
        },
    },
    created() {
        this._protected = {
            parseProps:
                (propName, data) => {
                    switch (propName) {
                        case 'bindEvent':
                            const request = data.replace(/\s/g, '');
                            const bindings = request.split(',');
                            bindings.forEach((binding) => {
                                const words = binding.split(':');
                                if (words.length === 2) {
                                    const eventName = words[0];
                                    const rule = words[1];
                                    const match = rule.match(/(.+?)\.(.+)/);
                                    if (match && match.length > 2) {
                                        const id = match[1];
                                        const action = match[2];
                                        try {
                                            const elements = this.$(id);
                                            if (elements) {
                                                this._protected.bindedEvents.push(eventName);
                                                this.$on(eventName, (...arg) => {
                                                    elements.each((index, elem) => {
                                                        let value = action.split('.').reduce((a, b) => a[b], elem);
                                                        if (typeof value === 'function') {
                                                            value(...arg);
                                                        }
                                                    });
                                                });
                                            }
                                        } catch (e) {
                                            console.error('bind-event props error: ' +
                                                'The element specified is not a valid jQuery selector');
                                        }
                                    }
                                }
                            });
                            break;
                    }
                },

            // Expose public methods (from method sections) in DOM props
            bindPublicMethods:
                () => {
                    // Bind exposed methods to events
                    const _this = this;
                    Object.keys(this.$options.methods).forEach((methodName) => {
                        const method = {
                            [methodName]: (...args) => {
                                try {
                                    const ret = _this[methodName].call(_this, ...args);
                                    return ret;
                                } catch (e) {

                                }
                            },
                        };
                        if (methodName !== '$emit') {
                            this.$(this.$el).parent().prop('publicMethods', (index, oldPropVal) => {
                                if (!oldPropVal) {
                                    return method;
                                } else {
                                    return Object.assign({}, oldPropVal, method);
                                }
                            });
                        }
                    });
                },

            bindedEvents: [],
        };
    },

    mounted() {
        const ready = () => {
            if (this.bindData) {
                this._protected.parseProps('bindData', this.bindData);
            }

            if (this.bindEvent) {
                this._protected.parseProps('bindEvent', this.bindEvent);
            }

            this._protected.bindPublicMethods();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', ready);
        } else {
            ready();
        }
    },

    destroyed() {
        this._protected.bindedEvents.forEach((e) => {
            this.$off(e);
        });
    },

};
