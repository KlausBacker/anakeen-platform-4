/**
 * Dynacase document component object ***
 */

import { AnkMixin } from '../AnkVueComponentMixin';

export default {
    mixins: [AnkMixin],
    name: 'ank-smart-element',
    data() {
        return {
            initialDocumentUrl: '/api/v2/documents/0.html',
        };
    },

    props: {
        seValue: {
            type: [String, Object],
            default: () => (JSON.stringify({
                initid: 0,
                viewId: '!defaultConsultation',
                revision: -1,
                customClientData: null,
            })),
            validator: (value) => {
                if (typeof value === 'string') {
                    try {
                        const parsed = JSON.parse(value);
                        return parsed.initid !== undefined;
                    } catch (e) {
                        console.error(e);
                        return false;
                    }
                }

                return true;
            },
        },
        browserHistory: {
            default: false,
            type: Boolean,
        },
        initid: {
            type: [Number, String],
            default: 0,
        },
        customClientData: {
            type: [String, Object],
            default: null,
            validator: (value) => {
                if (typeof value === 'string') {
                    try {
                        JSON.parse(value);
                        return true;
                    } catch (e) {
                        console.error(e);
                        return false;
                    }
                }

                return true;
            },
        },
        viewId: {
            type: String,
            default: '!defaultConsultation',
        },
        revision: {
            type: Number,
            default: -1,
        },
    },

    computed: {
        parsedSEValue() {
            if (typeof this.seValue === 'object') {
                return this.seValue;
            } else {
                return JSON.parse(this.seValue);
            }
        },

        getInitialData() {
            const initialData = {
                noRouter: this.browserHistory !== true,
            };

            /**
             * Prop documentValue are priority on single properties
             */
            initialData.initid = this.parsedSEValue.initid || this.initid;
            if (this.parsedSEValue.customClientData || this.customClientData) {
                initialData.customClientData = this.parsedSEValue.customClientData || this.customClientData;
            }

            if (this.parsedSEValue.revision !== -1) {
                initialData.revision = this.parsedSEValue.revision;
            } else if (this.revision !== -1) {
                initialData.revision = this.revision;
            }

            if (this.parsedSEValue.viewId !== '!defaultConsultation') {
                initialData.viewId = this.parsedSEValue.viewId;
            } else if (this.viewId !== '!defaultConsultation') {
                initialData.viewId = this.viewId;
            }

            return initialData;
        },
    },

    updated() {
        if (this.isLoaded()) {
            this.fetchDocument(this.getInitialData)
                .catch((error) => {
                console.error(error);
            });
        } else {
            this.$once('documentLoaded', () => {
                this.fetchDocument(this.getInitialData)
                    .catch((error) => {
                        console.error(error);
                    });
            });
        }
    },

    methods: {
        /**
         * True when internal widget is loaded
         * @returns {boolean}
         */
        isLoaded() {
            return (this.documentWidget !== undefined);
        },

        /**
         * Rebind all declared binding to internal widget
         * @returns void
         */
        listenAttributes() {
            const eventNames = ['beforeRender', 'ready', 'change', 'displayMessage', 'displayError', 'validate',
                'attributeBeforeRender', 'attributeReady',
                'attributeHelperSearch', 'attributeHelperResponse', 'attributeHelperSelect',
                'attributeArrayChange', 'actionClick',
                'attributeAnchorClick',
                'beforeClose', 'close',
                'beforeSave', 'afterSave', 'attributeDownloadFile', 'attributeUploadFile',
                'beforeDelete', 'afterDelete',
                'beforeRestore', 'afterRestore',
                'failTransition', 'successTransition',
                'beforeDisplayTransition', 'afterDisplayTransition',
                'beforeTransition', 'beforeTransitionClose',
                'destroy', 'attributeCreateDialogDocumentBeforeSetFormValues',
                'attributeCreateDialogDocumentBeforeSetTargetValue', 'attributeCreateDialogDocumentReady',
                'attributeCreateDialogDocumentBeforeClose', 'attributeCreateDialogDocumentBeforeDestroy',
            ];
            /* eslint-disable no-underscore-dangle */
            const localListener = this.$options._parentListeners || {};

            eventNames.forEach((eventName) => {
                this.documentWidget.addEventListener(
                    eventName,
                    {
                        name: `v-on-${eventName}-listen`,
                        documentCheck(/* documentObject */) {
                            return true;
                        },
                    },
                    (event, documentObject, ...others) => {
                        this.$emit(eventName, event, documentObject, ...others);
                    },
                );
            });

            Object.keys(localListener).forEach((key) => {
                // input is an internal vuejs bind
                if (eventNames.indexOf(key) === -1 && key !== 'documentLoaded' && key !== 'input') {
                    /* eslint-disable no-console */
                    console.error(`Cannot listen to "${key}". It is not a defined listener for ank-document component`);
                }
            });

            /**
             * Add listener to update component values
             */
            this.documentWidget.addEventListener(
                'ready',
                {
                    name: 'v-on-dcpready-listen',
                },
                (event, documentObject) => {
                    if (this.initid && documentObject.initid !== this.initid) {
                        this.$emit('update:props', documentObject);
                    }
                },
            );
        },

        addEventListener(eventType, options, callback) {
            return this.documentWidget.addEventListener(eventType, options, callback);
        },

        fetchDocument(value, options) {
            return this.documentWidget.fetchDocument(value, options);
        },

        saveDocument(options) {
            return this.documentWidget.saveDocument(options);
        },

        showMessage(message) {
            return this.documentWidget.showMessage(message);
        },

        getAttributes() {
            return this.documentWidget.getAttributes();
        },

        getAttribute(attributeId) {
            return this.documentWidget.getAttribute(attributeId);
        },

        setValue(attributeId, newValue) {
            if (typeof newValue === 'string') {
                /* eslint-disable no-param-reassign */
                newValue = {
                    value: newValue,
                    displayValue: newValue,
                };
            }

            return this.documentWidget.setValue(attributeId, newValue);
        },

        reinitDocument(values, options) {
            return this.documentWidget.reinitDocument(values, options);
        },

        changeStateDocument(parameters, reinitOptions, options) {
            return this.documentWidget.changeStateDocument(parameters, reinitOptions, options);
        },

        deleteDocument(options) {
            return this.documentWidget.deleteDocument(options);
        },

        restoreDocument(options) {
            return this.documentWidget.restoreDocument(options);
        },

        getProperty(property) {
            return this.documentWidget.getProperty(property);
        },

        getProperties() {
            return this.documentWidget.getProperties();
        },

        hasAttribute(attributeId) {
            return this.documentWidget.hasAttribute(attributeId);
        },

        hasMenu(menuId) {
            return this.documentWidget.hasMenu(menuId);
        },

        getMenu(menuId) {
            return this.documentWidget.getMenu(menuId);
        },

        getMenus() {
            return this.documentWidget.getMenus();
        },

        getValue(attributeId, type) {
            return this.documentWidget.getValue(attributeId, type);
        },

        getValues() {
            return this.documentWidget.getValues();
        },

        getCustomServerData() {
            return this.documentWidget.getCustomServerData();
        },

        isModified() {
            return this.documentWidget.getProperty('isModified');
        },

        addCustomClientData(documentCheck, value) {
            return this.documentWidget.addCustomClientData(documentCheck, value);
        },

        getCustomClientData(deleteOnce) {
            return this.documentWidget.getCustomClientData(deleteOnce);
        },

        removeCustomClientData(key) {
            return this.documentWidget.removeCustomClientData(key);
        },

        appendArrayRow(attributeId, values) {
            return this.documentWidget.appendArrayRow(attributeId, values);
        },

        insertBeforeArrayRow(attributeId, values, index) {
            return this.documentWidget.insertBeforeArrayRow(attributeId, values, index);
        },

        removeArrayRow(attributeId, index) {
            return this.documentWidget.removeArrayRow(attributeId, index);
        },

        addConstraint(options, callback) {
            return this.documentWidget.addConstraint(options, callback);
        },

        listConstraints() {
            return this.documentWidget.listConstraints();
        },

        removeConstraint(constraintName, allKind) {
            return this.documentWidget.removeConstraint(constraintName, allKind);
        },

        listEventListeners() {
            return this.documentWidget.listEventListeners();
        },

        removeEventListener(eventName, allKind) {
            return this.documentWidget.removeEventListener(eventName, allKind);
        },

        triggerEvent(eventName, ...parameters) {
            return this.documentWidget.triggerEvent(eventName, ...parameters);
        },

        hideAttribute(attributeId) {
            return this.documentWidget.hideAttribute(attributeId);
        },

        showAttribute(attributeId) {
            return this.documentWidget.showAttribute(attributeId);
        },

        maskDocument(message, px) {
            return this.documentWidget.maskDocument(message, px);
        },

        unmaskDocument(force) {
            return this.documentWidget.unmaskDocument(force);
        },

        tryToDestroy() {
            return this.documentWidget.tryToDestroy();
        },

        injectCSS(cssToInject) {
            return this.documentWidget.injectCSS(cssToInject);
        },
    },

    mounted() {
        const $iframe = this.$refs.iDocument;
        const documentWindow = $iframe.contentWindow;
        $iframe.addEventListener('load', () => {
            documentWindow.documentLoaded = (domNode) => {
                // Re Bind the internalController function to the current widget
                this.documentWidget = domNode.data('dcpDocumentController');
                if (this.initid !== 0) {
                    this.listenAttributes();
                    $iframe.style.visibility = '';
                    this.fetchDocument(this.getInitialData);
                } else {
                    this.documentWidget.addEventListener('ready', { once: true },
                        () => {
                            this.listenAttributes();
                            $iframe.style.visibility = '';
                        },
                    );
                }

                this.$emit('documentLoaded');
            };

            if (documentWindow.dcp && documentWindow.dcp.triggerReload &&
                documentWindow.dcp.documentReady === false) {
                documentWindow.dcp.triggerReload();
            }
        }, true);
    },
};
