/* global define, console */
define([
    'jquery',
    'underscore',
    'backbone',
    'dcpDocument/routers/router',
    'dcpDocument/models/mDocument',
    'dcpDocument/controllerObjects/attributeInterface',
    'dcpDocument/controllerObjects/menuInterface',
    'dcpDocument/controllerObjects/transitionInterface',
    'dcpDocument/views/document/vDocument',
    'dcpDocument/models/mTransition',
    'dcpDocument/views/workflow/vTransition',
    'dcpDocument/models/mMenu',
    'dcpDocument/i18n/documentCatalog',
    'dcpDocument/widgets/widget',
    'dcpDocument/widgets/window/wConfirm',
    'dcpDocument/widgets/window/wLoading',
    'dcpDocument/widgets/window/wNotification'
], function documentController($, _, Backbone, Router, DocumentModel,
                               AttributeInterface, MenuInterface, TransitionInterface,
                               DocumentView, TransitionModel, TransitionView, MenuModel, i18n)
{
    'use strict';

    var ErrorModelNonInitialized = function ErrorModelNonInitialized(message)
    {
        this.name = "ErrorModelNonInitialized";
        this.message = message || "The widget model is not initialized, use fetchDocument to initialise it.";
        this.stack = (new Error()).stack;
    };
    ErrorModelNonInitialized.prototype = Object.create(Error.prototype);
    ErrorModelNonInitialized.prototype.constructor = ErrorModelNonInitialized;

    var eventList = ["beforeRender", "ready", "change", "displayMessage", "displayError", "validate",
        "attributeBeforeRender", "attributeReady",
        "attributeHelperSearch", "attributeHelperResponse", "attributeHelperSelect",
        "attributeArrayChange", "actionClick",
        "attributeAnchorClick",
        "beforeClose", "close",
        "beforeSave", "afterSave", "attributeDownloadFile", "attributeUploadFile", "attributeUploadFileDone",
        "beforeDelete", "afterDelete",
        "beforeRestore", "afterRestore",
        "failTransition", "successTransition",
        "attributeBeforeTabSelect", "attributeAfterTabSelect","attributeTabChange",
        "beforeDisplayTransition", "afterDisplayTransition",
        "beforeTransition", "beforeTransitionClose",
        "destroy", "attributeCreateDialogDocumentBeforeSetFormValues",
        "attributeCreateDialogDocumentBeforeSetTargetValue", "attributeCreateDialogDocumentReady",
        "attributeCreateDialogDocumentBeforeClose", "attributeCreateDialogDocumentBeforeDestroy"
    ];

    $.widget("dcp.documentController", {

        options: {
            eventPrefix: "document",
            initid: null,
            viewId: undefined,
            revision: undefined,
            constraintList: [],
            eventListener: [],
            _model: null,
            activatedConstraint: {},
            activatedEventListener: {},
            _initializedModel: false,
            _initializedView: false
        },

        /**
         * Create widget
         * @private
         */
        _create: function documentController_create()
        {
            this.options.constraintList = {};
            this.options.eventListener = {};
            this.activatedConstraint = {};
            this.activatedEventListener = {};
            this._initializedModel = false;
            this._initializedView = false;
            this._customClientData = {};
            if (!this.options.initid) {
                console.log("Widget initialised without document");
                return;
            }
            this._initializeWidget({}, this.options.customClientData);
            this._super();
        },

        /**
         * Delete the widget
         * @private
         */
        _destroy: function documentController_destroy()
        {
            this._triggerControllerEvent("destroy", null, this.getProperties());
            this.options.constraintList = {};
            this.options.eventListener = {};
            this.activatedConstraint = {};
            this.activatedEventListener = {};
            this._initializedModel = false;
            this._initializedView = false;
            this.element.removeData("document");
            if (this._model) {
                this._model.trigger("destroy");
            }
            this._trigger("destroy");
            this._super();
        },

        /**
         * Initialize the widget
         *
         * Create Model, initView
         *
         * @param options object {"success": fct, "error", fct}
         * @param customClientData object
         *
         * @private
         */
        _initializeWidget: function documentController_initializeWidget(options, customClientData)
        {
            var promise,
                currentWidget = this,
                initializeSuccess = function documentController_initializeSuccess()
                {
                    currentWidget._initializedModel = true;
                };
            options = options || {};
            this._initExternalElements();
            this._initModel(this._getModelValue());
            this._initView();
            if (options.success) {
                options.success = _.wrap(options.success, function documentController_fetchSuccess(success)
                {
                    initializeSuccess.apply(this, _.rest(arguments));
                    return success.apply(this, _.rest(arguments));
                });
            }
            if (customClientData) {
                this._model._customClientData = customClientData;
            }
            promise = this._model.fetchDocument(this._getModelValue(), options);
            if (!options.success) {
                promise.then(initializeSuccess);
            }

            this._initRouter({useHistory: !this.options.noRouter});

            return promise;
        },

        /**
         * Return essential element of the current document
         *
         * @returns {Object}
         * @private
         */
        _getModelValue: function documentController_getModelValue()
        {
            return _.pick(this.options, "initid", "viewId", "revision");
        },

        /**
         * Generate the dom where the view is inserted
         * @private
         */
        _initDom: function documentController_initDom()
        {
            var $document = this.element.find(".dcpDocument");
            if (!this.$document || $document.length === 0) {
                this.element.append('<div class="dcpDocument"></div>');
                this.$document = this.element.find(".dcpDocument");
            }
        },

        /**
         * Init the model and bind the events
         *
         * @param initialValue
         * @returns DocumentModel
         * @private
         */
        _initModel: function documentController_initModel(initialValue)
        {
            var model;

            //Don't reinit the model
            if (!this._model) {
                model = new DocumentModel(initialValue);
                this._model = model;
                this._initModelEvents();
            } else {
                this._reinitModel();
            }
            return model;
        },

        /**
         * Init the view and bind the events
         *
         * @returns DocumentView
         * @private
         */
        _initView: function documentController_initView()
        {
            var documentView;
            ///Don't reinit view
            if (!this.view) {
                this._initDom();
                documentView = new DocumentView({model: this._model, el: this.$document[0]});
                this.view = documentView;
                this._initViewEvents();
            }
            return this.view;
        },

        /**
         * Clear and reinit the model with current widget values
         *
         * @private
         */
        _reinitModel: function documentController_reinitModel()
        {
            this._model.set(this._getModelValue());
        },

        /**
         * Init the external elements (loading bar and notification widget)
         * @private
         */
        _initExternalElements: function documentController_initExternalElements()
        {
            this.$loading = $(".dcpLoading").dcpLoading();
            this.$notification = $('body').dcpNotification(
                window.dcp.notifications
            ); // active notification
        },

        /**
         * Bind the model event
         *
         * Re-trigger the event
         *
         * @private
         */
        _initModelEvents: function documentController_initEvents()
        {
            var currentWidget = this;
            this._model.listenTo(this._model, "invalid", function documentController_triggerShowInvalid(model, error)
            {
                var result = currentWidget._triggerControllerEvent("displayError", null,
                    currentWidget.getProperties(), error);
                if (result) {
                    currentWidget.$notification.dcpNotification("showError", error);
                }
            });
            this._model.listenTo(this._model, "showError", function documentController_triggerShowError(error)
            {
                var result = currentWidget._triggerControllerEvent("displayError", null,
                    currentWidget.getProperties(), error);
                if (result) {
                    currentWidget.$notification.dcpNotification("showError", error);
                }
            });
            this._model.listenTo(this._model, "showMessage", function documentController_triggerShowMessage(msg)
            {
                var result = currentWidget._triggerControllerEvent("displayMessage", null,
                    currentWidget.getProperties(), msg);
                if (result) {
                    currentWidget.$notification.dcpNotification("show", msg.type, msg);
                }
            });
            this._model.listenTo(this._model, "reload", function documentController_triggerReinit()
            {
               // currentWidget._initModel(currentWidget._getModelValue());
               // currentWidget._initView();
                currentWidget._model.fetchDocument();
            });
            this._model.listenTo(this._model, "sync", function documentController_triggerSync()
            {
                currentWidget._initializedModel = true;
                currentWidget.options.initid = currentWidget._model.id;
                currentWidget.options.viewId = currentWidget._model.get("viewId");
                currentWidget.options.revision = currentWidget._model.get("revision");
                currentWidget.element.data("document", currentWidget._getModelValue());
                currentWidget._initActivatedConstraint();
                currentWidget._initActivatedEventListeners({launchReady: false});
            });
            this._model.listenTo(this._model, "beforeRender", function documentController_triggerBeforeRender(event)
            {
                event.prevent = !currentWidget._triggerControllerEvent("beforeRender", event,
                    currentWidget.getProperties(), currentWidget._model.getModelProperties());
            });
            this._model.listenTo(this._model, "beforeClose", function documentController_triggerBeforeClose(event,
                                                                                                            nextDocument, customClientData)
            {
                if (currentWidget._initializedView !== false) {
                    event.prevent = !currentWidget._triggerControllerEvent("beforeClose", event,
                        currentWidget.getProperties(), nextDocument, customClientData);
                }
            });
            this._model.listenTo(this._model, "close", function documentController_triggerClose(oldProperties)
            {
                if (currentWidget._initializedView !== false) {
                    currentWidget._triggerControllerEvent("close", null,
                        currentWidget.getProperties(), oldProperties);
                }
                currentWidget._initializedView = false;
            });
            this._model.listenTo(this._model, "getCustomClientData", function documentController_triggerAddCustomData()
            {
                try {
                    currentWidget._model._customClientData = currentWidget.getCustomClientData(false);
                } catch (e) {

                }
            });
            this._model.listenTo(this._model, "beforeSave", function documentController_triggerBeforeSave(event, customClientData)
            {
                var _model=this;
                var requestOptions={
                    getRequestData: function getRequestData() {
                        return _model.toJSON();
                    },
                    setRequestData: function documentControllerSetRequestData(data) {
                       _model._customRequestData=data;
                    }
                };
                event.prevent = !currentWidget._triggerControllerEvent("beforeSave", event,
                    currentWidget.getProperties(),
                    requestOptions,
                    customClientData);
            });
            this._model.listenTo(this._model, "afterSave", function documentController_triggerAfterSave(oldProperties)
            {
                currentWidget._triggerControllerEvent("afterSave", null,
                    currentWidget.getProperties(), oldProperties);
            });
            this._model.listenTo(this._model, "beforeRestore", function documentController_triggerBeforeRestore(event)
            {
                event.prevent = !currentWidget._triggerControllerEvent("beforeRestore", event,
                    currentWidget.getProperties());
            });
            this._model.listenTo(this._model, "afterRestore", function documentController_triggerAfterRestore(oldProperties)
            {
                currentWidget._triggerControllerEvent("afterRestore", null,
                    currentWidget.getProperties(), oldProperties);
            });
            this._model.listenTo(this._model, "beforeDelete", function documentController_triggerBeforeDelete(event, customClientData)
            {
                event.prevent = !currentWidget._triggerControllerEvent("beforeDelete", event,
                    currentWidget.getProperties(), currentWidget._model.getModelProperties(), customClientData);
            });
            this._model.listenTo(this._model, "afterDelete", function documentController_triggerAfterDelete(oldProperties)
            {
                currentWidget._triggerControllerEvent("afterDelete", null,
                    currentWidget.getProperties(), oldProperties);
            });
            this._model.listenTo(this._model, "validate", function documentController_triggerValidate(event)
            {
                event.prevent = !currentWidget._triggerControllerEvent("validate",  event, currentWidget.getProperties());
            });
            this._model.listenTo(this._model, "changeValue", function documentController_triggerChangeValue(options)
            {
                try {
                    var currentAttribute = currentWidget.getAttribute(options.attributeId),
                        index = 0, values = currentAttribute.getValue("all"),
                        mAttribute = currentWidget._getAttributeModel(options.attributeId);
                    if (mAttribute.getParent().get("type") !== "array") {
                        index = -1;
                    } else {
                        var changesIndex=[];
                        _.each(values.current, function documentController_valueIsModified(currentValue)
                        {
                            var result, previous = values.previous[index];
                            if (!previous) {
                                changesIndex.push(index);
                            } else {
                                if (_.isArray(currentValue)) {
                                    currentValue = currentValue.join(",");
                                }
                                currentValue = _.has(currentValue, "value") ? currentValue.value : currentValue;
                                if (_.isArray(previous)) {
                                    previous = previous.join(",");
                                }
                                previous = _.has(previous, "value") ? previous.value : previous;
                                if (previous !== currentValue) {
                                     changesIndex.push(index);
                                }

                            }
                            index++;

                        });
                        index=(changesIndex.length === 1)?changesIndex[0]:-1;
                    }
                    currentWidget._triggerAttributeControllerEvent("change", null, currentAttribute,
                        currentWidget.getProperties(),
                        currentAttribute,
                        currentAttribute.getValue("all"),
                        index
                    );
                } catch (error) {
                    if (!(error instanceof ErrorModelNonInitialized)) {
                        console.error(error);
                    }
                }

            });
            this._model.listenTo(this._model, "beforeAttributeRender", function documentController_triggerAttributeRender(event, attributeId, $el, index)
            {
                try {
                    var currentAttribute = currentWidget.getAttribute(attributeId);
                    event.prevent = !currentWidget._triggerAttributeControllerEvent("attributeBeforeRender", event, currentAttribute,
                        currentWidget.getProperties(),
                        currentAttribute,
                        $el,
                        index
                    );
                } catch (error) {
                    if (!(error instanceof ErrorModelNonInitialized)) {
                        console.error(error);
                    }
                }
            });
            this._model.listenTo(this._model, "attributeRender", function documentController_triggerAttributeRender(attributeId, $el, index)
            {
                try {
                    var currentAttribute = currentWidget.getAttribute(attributeId);
                    currentWidget._triggerAttributeControllerEvent("attributeReady", null, currentAttribute,
                        currentWidget.getProperties(),
                        currentAttribute,
                        $el,
                        index
                    );
                } catch (error) {
                    if (!(error instanceof ErrorModelNonInitialized)) {
                        console.error(error);
                    }
                }

            });
            this._model.listenTo(this._model, "arrayModified", function documentController_triggerArrayModified(options)
            {
                try {
                    var currentAttribute = currentWidget.getAttribute(options.attributeId);
                    currentWidget._triggerAttributeControllerEvent("attributeArrayChange", null, currentAttribute,
                        currentWidget.getProperties(),
                        currentAttribute,
                        options.type,
                        options.options
                    );
                } catch (error) {
                    if (!(error instanceof ErrorModelNonInitialized)) {
                        console.error(error);
                    }
                }

            });
            this._model.listenTo(this._model, "internalLinkSelected", function documentController_triggerInternalLinkSelected(event, options)
            {
                event.prevent = !currentWidget._triggerControllerEvent("actionClick", event,
                    currentWidget.getProperties(),
                    options
                );
            });
            this._model.listenTo(this._model, "downloadFile", function documentController_triggerDownloadFile(event, attrid, options)
            {
                try {
                    var currentAttribute = currentWidget.getAttribute(attrid);
                    event.prevent = !currentWidget._triggerAttributeControllerEvent("attributeDownloadFile", event,
                        currentAttribute,
                        currentWidget.getProperties(),
                        currentAttribute,
                        options.$el,
                        options.index
                    );
                } catch (error) {
                    if (!(error instanceof ErrorModelNonInitialized)) {
                        console.error(error);
                    }
                }

            });
            this._model.listenTo(this._model, "uploadFile", function documentController_triggerUploadFile(event, attrid, options)
            {
                try {
                    var currentAttribute = currentWidget.getAttribute(attrid);
                    event.prevent = !currentWidget._triggerAttributeControllerEvent("attributeUploadFile", event,
                        currentAttribute,
                        currentWidget.getProperties(),
                        currentAttribute,
                        options.$el,
                        options.index,
                        {
                            file: options.file,
                            hasUploadingFiles: currentWidget._model.hasUploadingFile()

                        }
                    );
                } catch (error) {
                    if (!(error instanceof ErrorModelNonInitialized)) {
                        console.error(error);
                    }
                }
            });
            this._model.listenTo(this._model, "uploadFileDone", function documentController_triggerUploadFile(event, attrid, options)
            {
                try {
                    var currentAttribute = currentWidget.getAttribute(attrid);
                    event.prevent = !currentWidget._triggerAttributeControllerEvent("attributeUploadFileDone", event,
                        currentAttribute,
                        currentWidget.getProperties(),
                        currentAttribute,
                        options.$el,
                        options.index,
                        {
                            file: options.file,
                            hasUploadingFiles: currentWidget._model.hasUploadingFile()
                        }
                    );
                } catch (error) {
                    if (!(error instanceof ErrorModelNonInitialized)) {
                        console.error(error);
                    }
                }
            });

            this._model.listenTo(this._model, "attributeBeforeTabSelect", function documentController_triggerBeforeSelectTab(event, attrid)
            {
                var currentAttribute = currentWidget.getAttribute(attrid);
                var prevent;

                prevent = !currentWidget._triggerAttributeControllerEvent("attributeBeforeTabSelect", event, currentAttribute,
                    currentWidget.getProperties(), currentAttribute, $(event.item));
                if (prevent) {
                    event.preventDefault();
                }
            });
            this._model.listenTo(this._model, "attributeTabChange", function documentController_triggerAfterSelectTab(event, attrid, $el, data)
            {
                var currentAttribute = currentWidget.getAttribute(attrid);

                currentWidget._triggerAttributeControllerEvent("attributeTabChange", event, currentAttribute,
                    currentWidget.getProperties(), currentAttribute, $el, data);
            });
            this._model.listenTo(this._model, "attributeAfterTabSelect", function documentController_triggerAfterSelectTab(event, attrid)
            {
                var currentAttribute = currentWidget.getAttribute(attrid);

                currentWidget._triggerAttributeControllerEvent("attributeAfterTabSelect", event, currentAttribute,
                    currentWidget.getProperties(), currentAttribute, $(event.item));
            });
            this._model.listenTo(this._model, "helperSearch", function documentController_triggerHelperSearch(event, attrid, options)
            {
                try {
                    var currentAttribute = currentWidget.getAttribute(attrid);
                    event.prevent = !currentWidget._triggerAttributeControllerEvent("attributeHelperSearch", event, currentAttribute,
                        currentWidget.getProperties(),
                        currentAttribute,
                        options
                    );
                } catch (error) {
                    if (!(error instanceof ErrorModelNonInitialized)) {
                        console.error(error);
                    }
                }
            });
            this._model.listenTo(this._model, "helperResponse", function documentController_triggerHelperResponse(event, attrid, options)
            {
                try {
                    var currentAttribute = currentWidget.getAttribute(attrid);
                    event.prevent = !currentWidget._triggerAttributeControllerEvent("attributeHelperResponse", event, currentAttribute,
                        currentWidget.getProperties(),
                        currentAttribute,
                        options
                    );
                } catch (error) {
                    if (!(error instanceof ErrorModelNonInitialized)) {
                        console.error(error);
                    }
                }
            });
            this._model.listenTo(this._model, "helperSelect", function documentController_triggerHelperSelect(event, attrid, options)
            {
                try {
                    var currentAttribute = currentWidget.getAttribute(attrid);
                    event.prevent = !currentWidget._triggerAttributeControllerEvent("attributeHelperSelect", event, currentAttribute,
                        currentWidget.getProperties(),
                        currentAttribute,
                        options
                    );
                } catch (error) {
                    if (!(error instanceof ErrorModelNonInitialized)) {
                        console.error(error);
                    }
                }
            });

            // listener to prevent default actions when anchorClick is triggered
            this._model.listenTo(this._model, "anchorClick", function documentController_triggerHelperSelect(event, attrid, options)
            {
                try {
                    var currentAttribute = currentWidget.getAttribute(attrid);
                    event.prevent = !currentWidget._triggerAttributeControllerEvent("attributeAnchorClick", event,
                        currentAttribute,
                        currentWidget.getProperties(),
                        currentAttribute,
                        options.$el,
                        options.index,
                        options.options
                    );
                } catch (error) {
                    if (!(error instanceof ErrorModelNonInitialized)) {
                        console.error(error);
                    }
                }
            });

            // Generic listener for addCreateDocumentButton docid render option
            this._model.listenTo(this._model, "createDialogListener", function documentController_triggercreateDialogDocumentOpen(event, attrid, options)
            {
                try {
                    var currentAttribute = currentWidget.getAttribute(attrid);
                    var triggername = "attributeCreateDialogDocument";
                    // Uppercase first letter
                    triggername += options.triggerId.charAt(0).toUpperCase() + options.triggerId.slice(1);

                    event.prevent = !currentWidget._triggerAttributeControllerEvent(triggername, event,
                        currentAttribute,
                        currentWidget.getProperties(),
                        currentAttribute,
                        options
                    );
                } catch (error) {
                    if (!(error instanceof ErrorModelNonInitialized)) {
                        console.error(error);
                    }
                }
            });
            this._model.listenTo(this._model, "constraint", function documentController_triggerConstraint(attribute, constraintController)
            {
                try {
                    var currentAttribute = currentWidget.getAttribute(attribute),
                        currentModel = currentWidget.getProperties(),
                        $element = $(currentWidget.element),
                        addConstraint = function documentController_addConstraint(currentConstraint)
                        {
                            if (_.isString(currentConstraint)) {
                                constraintController.addConstraintMessage(currentConstraint);
                            }
                            if (_.isObject(currentConstraint) && currentConstraint.message && _.isNumber(currentConstraint.index)) {
                                constraintController.addConstraintMessage(currentConstraint.message, currentConstraint.index);
                            }
                        };
                    _.each(currentWidget.activatedConstraint, function triggerCurrentConstraint(currentConstraint)
                    {
                        try {
                            if (currentConstraint.attributeCheck.apply($element, [currentAttribute, currentModel])) {
                                var response = currentConstraint.constraintCheck.call($element,
                                    currentModel,
                                    currentAttribute,
                                    currentAttribute.getValue("all")
                                );
                                if (_.isArray(response)) {
                                    _.each(response, addConstraint);
                                } else {
                                    addConstraint(response);
                                }
                            }
                        } catch (e) {
                            console.error(e);
                        }

                    });
                } catch (error) {
                    if (!(error instanceof ErrorModelNonInitialized)) {
                        console.error(error);
                    }
                }

            });
            this._model.listenTo(this._model, "showTransition", _.bind(currentWidget._initAndDisplayTransition, this));
            this._model.listenTo(this._model, "beforeParse", _.bind(function deleteCustomClient()
            {
                //Suppress customClientData after a sucessful transaction
                try {
                    currentWidget.getCustomClientData(true);
                } catch (e) {

                }
            }, this));
        },

        /**
         * Bind the view
         * Re-trigger the events
         *
         * @private
         */
        _initViewEvents: function documentController_initViewEvents()
        {
            var currentWidget = this;
            this.view.on("cleanNotification", function documentController_triggerCleanNotification()
            {
                currentWidget.$notification.dcpNotification("clear");
            });
            this.view.on('loading', function documentController_triggerLoading(data, nbItem)
            {
                currentWidget.$loading.dcpLoading('setPercent', data);
                if (nbItem) {
                    currentWidget.$loading.dcpLoading('setNbItem', nbItem);
                }
            });
            this.view.on('loaderShow', function documentController_triggerLoaderShow(text, pc)
            {
                console.time("xhr+render document view");
                currentWidget.$loading.dcpLoading('show', text, pc);
            });
            this.view.on('loaderHide', function documentController_triggerHide()
            {
                currentWidget.$loading.dcpLoading('hide');
            });
            this.view.on('partRender', function documentController_triggerPartRender()
            {
                currentWidget.$loading.dcpLoading('addItem');
            });
            this.view.on('renderDone', function documentController_triggerRenderDone()
            {
                console.timeEnd("xhr+render document view");
                currentWidget.$loading.dcpLoading("setPercent", 100);
                currentWidget.$loading.dcpLoading("setLabel", null);
                currentWidget._initializedView = true;
                currentWidget._triggerControllerEvent("ready",  null, currentWidget.getProperties());
                _.delay(function documentController_endRender()
                {
                    currentWidget.$loading.dcpLoading("hide", true);
                    console.timeEnd('main');
                });
            });
            this.view.on("showMessage", function documentController_triggerShowMessage(message)
            {
                var result = currentWidget._triggerControllerEvent("displayMessage", null,
                    currentWidget.getProperties(), message);
                if (result) {
                    currentWidget.$notification.dcpNotification("show", message.type, message);
                }
            });
            this.view.on("showSuccess", function documentController_triggerShowSuccess(message)
            {
                var result = currentWidget._triggerControllerEvent("displayMessage", null,
                    currentWidget.getProperties(), message);
                if (result) {
                    currentWidget.$notification.dcpNotification("showSuccess", message);
                }
            });
            this.view.on("reinit", function documentController_triggerReinit()
            {
                currentWidget._initModel(currentWidget._getModelValue());
                currentWidget._initView();
                currentWidget._model.fetchDocument();
            });
        },

        /**
         * Init the pushstate router
         *
         * @private
         */
        _initRouter: function documentController_initRouter(config)
        {
            if (this.router) {
                return this.router;
            }
            try {
                if (window.history && history.pushState) {
                    Backbone.history.start({pushState: true});
                } else {
                    //For browser without API history
                    Backbone.history.start();
                }
            } catch (e) {
                console.error(e);
            }
            this.router = new Router({
                document: this._model,
                useHistory: (!config || config.useHistory)
            });
        },

        /**
         * Init and display the change state pop-up
         *
         * @param nextState
         * @param transition
         * @param values
         * @param withoutInterface
         * @param reinitOptions
         */
        _initAndDisplayTransition: function documentController_initAndDisplayTransition(nextState, transition, values, withoutInterface, reinitOptions)
        {
            var $target = $('<div class="dcpTransition"/>'), transitionElements = {}, currentWidget = this, result,
                transitionInterface,
                documentServerProperties = this.getProperties();

            return new Promise(function documentController_changeStatePromise(resolve, reject)
            {
                result = !currentWidget._triggerControllerEvent("beforeDisplayChangeState", null,
                    currentWidget.getProperties(), new TransitionInterface(null, $target, nextState, transition));
                if (result) {
                    reject();
                    return this;
                }

                //Init transition model
                transitionElements.model = new TransitionModel({
                    documentId: currentWidget._model.id,
                    documentModel: currentWidget._model,
                    state: nextState,
                    transition: transition
                });

                //Init transition view
                if (withoutInterface !== true) {
                    transitionElements.view = new TransitionView({
                        model: transitionElements.model,
                        el: $target
                    });
                }

                transitionInterface = new TransitionInterface(transitionElements.model, $target, nextState, transition);

                if (transitionElements.view) {
                    //Propagate afterDisplayChange on renderDone
                    transitionElements.view.once("renderTransitionWindowDone", function documentController_propagateAfter()
                    {
                        currentWidget._triggerControllerEvent("afterDisplayTransition", null,
                            currentWidget.getProperties(), transitionInterface);
                    });
                }

                //Propagate the beforeTransition
                transitionElements.model.listenTo(transitionElements.model, "beforeChangeState", function documentController_propagateBeforeTransition(event)
                {
                    event.prevent = !currentWidget._triggerControllerEvent("beforeTransition", null,
                        currentWidget.getProperties(), transitionInterface);
                });

                //Propagate the beforeTransitionClose
                transitionElements.model.listenTo(transitionElements.model, "beforeChangeStateClose", function documentController_propagateTransitionClose(event)
                {
                    event.prevent = !currentWidget._triggerControllerEvent("beforeTransitionClose", null,
                        currentWidget.getProperties(), transitionInterface);
                });

                transitionElements.model.listenTo(transitionElements.model, "showError", function documentController_propagateTransitionError(error)
                {
                    event.prevent = !currentWidget._triggerControllerEvent("failTransition", null,
                        currentWidget.getProperties(), transitionInterface, error);
                    reject({documentProperties: documentServerProperties});
                });

                transitionElements.model.listenTo(transitionElements.model, 'success', function documentController_TransitionSuccess(messages)
                {
                    if (transitionElements.view) {
                        transitionElements.view.$el.hide();
                        currentWidget.view.once("renderDone", function documentController_transitionRender()
                        {
                            transitionElements.view.remove();
                            _.each(messages, function documentController_parseMessage(message)
                            {
                                currentWidget.view.trigger("showMessage", message);
                            });
                        });
                    }

                    //delete the pop up when the render of the pop up is done
                    currentWidget._triggerControllerEvent("successTransition", null,
                        currentWidget.getProperties(), transitionInterface);

                    reinitOptions = reinitOptions || {revision: -1};
                    if (!_.has(reinitOptions, "revision")) {
                        reinitOptions.revision = -1;
                    }

                    //Reinit the main model with last revision
                    currentWidget.reinitDocument(reinitOptions).then(function documentController_reinitDone()
                    {
                        resolve({documentProperties: documentServerProperties});
                    }, function documentController_reinitFail()
                    {
                        reject({documentProperties: documentServerProperties});
                    });

                });

                transitionElements.model.listenTo(currentWidget._model, "sync", function documentController_TransitionClose()
                {
                    this.trigger("close");
                });

                transitionElements.model.fetch({
                    "success": function transitionModel_setDefaultValues()
                    {
                        if (values) {
                            transitionElements.model.setValues(values);
                        }
                        if (withoutInterface === true) {
                            transitionElements.model._loadDocument(transitionElements.model).then(function documentController_TransitionSave()
                            {
                                transitionElements.model.save({}, {
                                    success: function transitionModel_afterSave()
                                    {
                                        transitionElements.model.trigger("success");
                                        resolve({documentProperties: documentServerProperties});
                                    },
                                    error: function transitionModel_error()
                                    {
                                        reject({documentProperties: documentServerProperties});
                                    }
                                });
                            }).catch(function transitionModel_error()
                            {
                                reject({documentProperties: documentServerProperties});
                            });
                        } else {
                            transitionElements.model._loadDocument(transitionElements.model).then(function documentController_TransitionDisplay()
                            {
                                transitionElements.model.trigger("dduiDocumentReady");
                            }).catch(function transitionModel_error()
                            {
                                reject({documentProperties: documentServerProperties});
                            });
                        }
                    },
                    "error": function transitionModel_error(theModel, response, options)
                    {
                        var errorTxt = {title: "Transition Error"};
                        if (options && options.errorThrown) {
                            errorTxt.message = options.errorThrown;
                        }
                        currentWidget.$notification.dcpNotification("showError", errorTxt);
                        transitionElements.model.trigger("showError", errorTxt);
                    }
                });
            });

        },

        /**
         * Get a backbone model of an attribute
         *
         * @param attributeId
         * @returns {*}
         */
        _getAttributeModel: function documentController_getAttributeModel(attributeId)
        {
            var attributes = this._model.get("attributes");
            var attribute;
            if (!attributes) {
                throw new Error('Attribute models not initialized yet : The attribute "' + attributeId + '" cannot be found.');
            }
            attribute = this._model.get("attributes").get(attributeId);
            if (!attribute) {
                return undefined;
            }
            return attribute;
        },

        _getMenuModel: function documentController_getMenuModel(menuId)
        {
            var menus = this._model.get("menus");

            var menu = menus.get(menuId);
            if (!menu && menus) {
                menus.each(function documentControllerGetMenuIterate(itemMenu)
                {
                    if (itemMenu.get("content")) {
                        _.each(itemMenu.get("content"), function documentControllerGetSubMenuIterate(subMenu)
                        {
                            if (subMenu.id === menuId) {
                                menu = new MenuModel(subMenu);
                            }
                        });
                    }
                });
            }
            return menu;
        },

        /**
         * Get all rendered attributes with their root dom node
         *
         * @returns {*}
         */
        _getRenderedAttributes: function documentController_getRenderedAttributes()
        {
            return this._model.get("attributes").chain().map(function documentController_getRenderedAttribute(currentAttribute)
            {
                return {
                    "view": currentAttribute.haveView(),
                    "id": currentAttribute.id
                };
            }).filter(function documentController_suppressNoView(currentAttribut)
            {
                return currentAttribut.view.haveView;
            }).value();
        },

        /**
         * Get max index of an array
         *
         * @param attributeArray
         * @returns {*}
         */
        _getMaxIndex: function documentController_getMaxIndex(attributeArray)
        {
            return _.size(attributeArray.get("content").max(function documentController_getMax(currentAttr)
            {
                return _.size(currentAttr.get("attributeValue"));
            }).get("attributeValue"));
        },

        /**
         * Activate constraint on the current document
         * Used on the fetch of a new document
         *
         */
        _initActivatedConstraint: function documentController_initActivatedConstraint()
        {
            var currentDocumentProperties = this.getProperties(), currentWidget = this;
            this.activatedConstraint = {};
            _.each(this.options.constraintList, function documentController_getActivatedConstraint(currentConstraint)
            {
                if (currentConstraint.documentCheck.call($(currentWidget.element), currentDocumentProperties)) {
                    currentWidget.activatedConstraint[currentConstraint.name] = currentConstraint;
                }
            });
        },

        /**
         * Activate events on the current document
         * Used on the fetch of a new document
         */
        _initActivatedEventListeners: function documentController_initActivatedEvents(options)
        {
            var currentDocumentProperties = this.getProperties(), currentWidget = this;
            options = options || {};
            this.activatedEventListener = {};
            _.each(this.options.eventListener, function documentController_getActivatedEvent(currentEvent)
            {
                if (!_.isFunction(currentEvent.documentCheck)) {
                    currentWidget.activatedEventListener[currentEvent.name] = currentEvent;
                    return;
                }
                if (currentEvent.documentCheck.call($(currentWidget.element), currentDocumentProperties)) {
                    currentWidget.activatedEventListener[currentEvent.name] = currentEvent;
                }
            });
            //Trigger new added ready event
            if (this._initializedView !== false && options.launchReady !== false) {
                this._triggerControllerEvent("ready",  null, currentDocumentProperties);
                _.each(this._getRenderedAttributes(), function documentController_triggerRenderedAttributes(currentAttribute)
                {
                    var objectAttribute = currentWidget.getAttribute(currentAttribute.id);
                    currentWidget._triggerAttributeControllerEvent("attributeReady", null, currentAttribute,
                        currentDocumentProperties,
                        objectAttribute,
                        currentAttribute.view.elements
                    );
                });
            }
        },

        /**
         * Add new event and autotrigger already done event for ready
         *
         * @param newEvent
         */
        _addAndInitNewEvents: function documentController_addAndInitNewEvents(newEvent)
        {
            var currentDocumentProperties, currentWidget = this, event, uniqueName, $element = $(currentWidget.element);
            uniqueName = (newEvent.externalEvent ? "external_" : "internal_") + newEvent.name;
            this.options.eventListener[uniqueName] = newEvent;

            if (!this._initializedModel) {
                //early event model is not ready (no trigger, or current register possible)
                return this;
            }
            currentDocumentProperties = this.getProperties();
            // Check if the event is for the current document
            if (!_.isFunction(newEvent.documentCheck) || newEvent.documentCheck.call($element, currentDocumentProperties)) {
                this.activatedEventListener[newEvent.name] = newEvent;
                // Check if we need to manually trigger this callback (late registered : only for ready events)
                if (this._initializedView !== false) {
                    if (newEvent.eventType === "ready") {
                        event = $.Event(newEvent.eventType);
                        event.target = currentWidget.element;
                        try {
                            // add element as function context
                            newEvent.eventCallback.call($element, event, currentDocumentProperties);
                        } catch (e) {
                            console.error(e);
                        }
                    }
                    if (newEvent.eventType === "attributeReady") {
                        event = $.Event(newEvent.eventType);
                        event.target = currentWidget.element;
                        _.each(this._getRenderedAttributes(), function documentController_triggerRenderedAttributes(currentAttribute)
                        {
                            var objectAttribute = currentWidget.getAttribute(currentAttribute.id);
                            if (!_.isFunction(newEvent.attributeCheck) || newEvent.attributeCheck.apply($element, [objectAttribute])) {
                                try {
                                    // add element as function context
                                    newEvent.eventCallback.call($element,
                                        event,
                                        currentDocumentProperties,
                                        objectAttribute,
                                        currentAttribute.view.elements);
                                } catch (e) {
                                    console.error(e);
                                }
                            }
                        });
                    }
                }
            }
        },

        /**
         * Trigger attribute event
         *
         * Similar at trigger document event with a constraint on attribute
         *
         * @param eventName
         * @param originalEvent
         * @param attributeInternalElement
         * @returns {boolean}
         */
        _triggerAttributeControllerEvent: function documentController_triggerAttributeControllerEvent(eventName, originalEvent, attributeInternalElement)
        {
            var currentWidget = this, args = Array.prototype.slice.call(arguments, 3), event = $.Event(eventName),
                externalEventArgument,
                $element = $(currentWidget.element);
            event.target = currentWidget.element;
            // internal event trigger
            if (originalEvent && originalEvent.preventDefault) {
                event.originalEvent = originalEvent;
            }
            args.unshift(event);
            _.chain(this.activatedEventListener).filter(function documentController__filterUsableEvents(currentEvent)
            {
                // Check by eventType (only call callback with good eventType)
                if (currentEvent.eventType === eventName) {
                    //Check with attributeCheck if the function exist
                    if (!_.isFunction(currentEvent.attributeCheck)) {
                        return true;
                    }
                    return currentEvent.attributeCheck.apply($element, [attributeInternalElement, currentWidget.getProperties()]);
                }
                return false;
            }).each(function documentController_applyCallBack(currentEvent)
            {
                try {
                    currentEvent.eventCallback.apply($element, args);
                } catch (e) {
                    if (window.dcp && window.dcp.logger) {
                        window.dcp.logger(e);
                    } else {
                        console.error(e);
                    }
                }
            });
            externalEventArgument = Array.prototype.slice.call(arguments, 0);
            externalEventArgument.splice(1, 1);
            currentWidget._triggerExternalEvent.apply(currentWidget, externalEventArgument);
            return !event.isDefaultPrevented();
        },

        /**
         * Trigger a controller event
         * That kind of event are only for this widget
         *
         * @param eventName
         * @param originalEvent
         * @returns {boolean}
         */
        _triggerControllerEvent: function documentController_triggerControllerEvent(eventName, originalEvent)
        {
            var currentWidget = this, args = Array.prototype.slice.call(arguments, 2), event = $.Event(eventName);
            event.target = currentWidget.element;
            if (originalEvent && originalEvent.preventDefault) {
                event.originalEvent = originalEvent;
            }
            // internal event trigger
            args.unshift(event);
            _.chain(this.activatedEventListener).filter(function documentController_getEventName(currentEvent)
            {
                return currentEvent.eventType === eventName;
            }).each(function documentController_triggerAnEvent(currentEvent)
            {
                try {
                    currentEvent.eventCallback.apply($(currentWidget.element), args);
                } catch (e) {
                    if (window.dcp.logger) {
                        window.dcp.logger(e);
                    } else {
                        console.error(e);
                    }
                }
            });
            currentWidget._triggerExternalEvent.apply(currentWidget, arguments);
            return !event.isDefaultPrevented();
        },

        /**
         * Trigger event as jQuery standard events (all events are prefixed by document)
         *
         * @param type
         */
        _triggerExternalEvent: function documentController_triggerExternalEvent(type)
        {
            var currentWidget = this, args = Array.prototype.slice.call(arguments, 1), event = $.Event(type);
            //prepare argument for widget event trigger (we want type, event, data)
            // add the eventObject
            args.unshift(event);
            // add the type
            args.unshift(type);
            // concatenate other argument in one element (to respect widget pattern)
            args[2] = args.slice(2);
            // suppress other arguments (since they have been concatened)
            args = args.slice(0, 3);
            //trigger external event
            currentWidget._trigger.apply(currentWidget, args);
        },

        /**
         * Check if event name is valid
         *
         * @param eventName string
         * @private
         */
        _checkEventName: function documentController_checkEventName(eventName)
        {
            if (_.isString(eventName) &&
                (eventName.indexOf("custom:") === 0 ||
                    _.find(eventList, function documentController_CheckEventType(currentEventType)
                    {
                        return currentEventType === eventName;
                    })
                )) {
                return true;
            }
            throw new Error("The event type " + eventName + " is not known. It must be one of " + eventList.sort().join(" ,"));
        },

        /**
         * Check if the view is initialized
         *
         * @private
         */
        _checkInitialisedView: function documentController_checkInitialised()
        {
            if (!this._initializedView) {
                throw new ErrorModelNonInitialized("The widget view is not initialized, use fetchDocument to initialise it.");
            }
        },

        /**
         * Check if the model is initialized
         *
         * @private
         */
        _checkInitialisedModel: function documentController_checkInitialisedModel()
        {
            if (!this._initializedModel) {
                throw new ErrorModelNonInitialized();
            }
        },

        _registerOutputPromise: function documentController_registerOutputPromise(documentPromise, options)
        {
            var currentWidget = this;
            return new Promise(function documentController_reinitPromise(resolve, reject)
            {
                documentPromise.then(function documentController_reinitDone(values)
                    {
                        if (options && _.isFunction(options.success)) {
                            try {
                                if (window.console.warn) {
                                    window.console.warn("Callback \"success\" is deprecated use promise instead");
                                }
                                options.success.call($(currentWidget.element),
                                    values.documentProperties || {},
                                    currentWidget.getProperties());
                            } catch (exception) {
                                if (window.dcp.logger) {
                                    window.dcp.logger(exception);
                                } else {
                                    console.error(exception);
                                }
                            }
                        }
                        resolve({
                            element: $(currentWidget.element),
                            previousDocument: values.documentProperties || {},
                            nextDocument: currentWidget.getProperties()
                        });
                    }, function documentController_reinitFail(values)
                    {
                        var errorArguments = values.arguments;
                        var errorMessage = {contentText: "Undefined error"};

                        if (values.arguments) {
                            try {
                                if (errorArguments && errorArguments[1] && errorArguments[1].responseJSON) {
                                    errorMessage = errorArguments[1].responseJSON.messages[0];
                                }
                            } catch (e) {

                            }
                            if (errorArguments && errorArguments[0] && errorArguments[0].eventPrevented) {
                                errorMessage = {contentText: "Event prevented"};
                            }
                            if (errorArguments && errorArguments[0] && errorArguments[0].errorMessage) {
                                errorMessage = errorArguments[0].errorMessage;
                            }
                        }
                        if (options && _.isFunction(options.error)) {
                            try {
                                if (window.console.warn) {
                                    window.console.warn("Callback \"error\" is deprecated use promise instead");
                                }
                                options.error.call(
                                    $(currentWidget.element),
                                    values.documentProperties || {},
                                    null, errorMessage);
                            } catch (exception) {
                                window.dcp.logger(exception);
                            }
                        }
                        reject({
                            element: $(currentWidget.element),
                            previousDocument: values.documentProperties || {},
                            nextDocument: null,
                            errorMessage: errorMessage
                        });
                    }
                );
            });
        },

        /***************************************************************************************************************
         * External function
         **************************************************************************************************************/
        /**
         * Reinit the current document (close it and re-open it) : keep the same view, revision, etc...
         *
         * @param values object {"initid" : int, "revision" : int, "viewId" : string, "customClientData" : mixed}
         * @param options object {"success": fct, "error", fct}
         */
        reinitDocument: function documentControllerReinitDocument(values, options)
        {
            var properties = this.getProperties();
            this._checkInitialisedModel();
            values = values || {};

            //Reinit model with server values
            _.defaults(values, {revision: properties.revision, viewId: properties.viewId, initid: properties.initid});

            return this.fetchDocument(values, options);
        },

        /**
         * Fetch a new document
         * @param values object {"initid" : int, "revision" : int, "viewId" : string, "customClientData" : mixed}
         * @param options object {"success": fct, "error", fct}
         */
        fetchDocument: function documentControllerFetchDocument(values, options)
        {
            var documentPromise, callBackPromise;
            var currentWidget = this;
            values = _.isUndefined(values) ? {} : values;
            options = options || {};

            if (!_.isObject(values)) {
                throw new Error('Fetch argument must be an object {"initid":, "revision": , "viewId": }');
            }

            if (!values.initid) {
                throw new Error('initid argument is mandatory');
            }

            if (!isNaN(values.initid)) {
                // Convert to numeric initid is possible
                values.initid = parseInt(values.initid);
            }

            // Use default values when fetch another document
            _.defaults(values, {revision: -1, viewId: "!defaultConsultation"});
            _.defaults(options, {force: false});

            _.each(_.pick(values, "initid", "revision", "viewId"), function dcpDocument_setNewOptions(value, key)
            {
                currentWidget.options[key] = value;
            });

            if (!this._model) {
                documentPromise = this._initializeWidget(options, values.customClientData);
            } else {
                if (values.customClientData) {
                    this._model._customClientData = values.customClientData;
                }

                if (this._model.isModified() && options.force === false) {
                    callBackPromise = this._model._promiseCallback();
                    this._model.trigger("loadDocument",
                        this._getModelValue(),
                        {
                            success: callBackPromise.success,
                            error: callBackPromise.error
                        }
                    );
                    documentPromise = callBackPromise.promise;
                } else {
                    documentPromise = this._model.fetchDocument(this._getModelValue());
                }
            }
            return this._registerOutputPromise(documentPromise, options);
        },

        /**
         * Save the current document
         * Reload the interface in the same mode
         * @param options object {"success": fct, "error", fct, "customClientData" : mixed}
         *
         */
        saveDocument: function documentControllerSave(options)
        {
            var documentPromise;
            options = options || {};
            this._checkInitialisedModel();
            if (options.customClientData) {
                this._model._customClientData = options.customClientData;
            }
            documentPromise = this._model.saveDocument();
            return this._registerOutputPromise(documentPromise, options);
        },

        /**
         * Change the workflow state of the document
         *
         * @param parameters
         * @param reinitOptions
         * @param options
         */
        changeStateDocument: function documentController_changeStateDocument(parameters, reinitOptions, options)
        {
            var documentPromise;
            this._checkInitialisedModel();
            if (!_.isObject(parameters)) {
                throw new Error('changeStateDocument first argument must be an object {"nextState":, "transition": , "values":, "unattended":, "" }');
            }
            if (!_.isString(parameters.nextState) || !_.isString(parameters.transition)) {
                throw new Error('nextState and transition arguments are mandatory');
            }
            documentPromise = this._initAndDisplayTransition(parameters.nextState, parameters.transition, parameters.values || null,
                parameters.unattended || false, reinitOptions);
            return this._registerOutputPromise(documentPromise, options);
        },

        /**
         * Delete the current document
         * Reload the interface in the same mode
         * @param options object {"success": fct, "error", fct, "customClientData" : mixed}
         */
        deleteDocument: function documentControllerDelete(options)
        {
            var documentPromise;
            options = options || {};
            this._checkInitialisedModel();
            if (options.customClientData) {
                this._model._customClientData = options.customClientData;
            }
            documentPromise = this._model.deleteDocument();
            return this._registerOutputPromise(documentPromise, options);
        },

        /**
         * Restore the current document
         * Reload the interface in the same mode
         * @param options object {"success": fct, "error", fct, "customClientData" : mixed}
         */
        restoreDocument: function documentControllerRestore(options)
        {
            var documentPromise;
            options = options || {};
            this._checkInitialisedModel();
            if (options.customClientData) {
                this._model._customClientData = options.customClientData;
            }
            documentPromise = this._model.restoreDocument();
            return this._registerOutputPromise(documentPromise, options);
        },

        /**
         * Get a property value
         *
         * @param property
         * @returns {*}
         */
        getProperty: function documentControllerGetDocumentProperty(property)
        {
            this._checkInitialisedModel();
            if (property === "isModified") {
                return this._model.isModified();
            }
            return this._model.getServerProperties()[property];
        },

        /**
         * Get all the properties
         * @returns {*}
         */
        getProperties: function documentControllerGetDocumentProperties()
        {
            var properties, ready = true;
            try {
                this._checkInitialisedModel();
            } catch (e) {
                ready = false;
                properties = {
                    "notLoaded": true
                };
            }
            if (ready) {
                properties = this._model.getServerProperties();
                properties.isModified = this._model.isModified();
                properties.url = window.location.href;
            }

            return properties;
        },

        /**
         * Check if an attribute exist
         *
         * @param attributeId
         * @return {boolean}
         */
        hasAttribute: function documentController_hasAttribute(attributeId)
        {
            this._checkInitialisedModel();
            var attribute = this._model.get("attributes").get(attributeId);
            return !!attribute;
        },

        /**
         * Get the attribute interface object
         * Return null if attribute not found
         * @param attributeId
         * @returns AttributeInterface|null
         */
        getAttribute: function documentControllerGetAttribute(attributeId)
        {
            this._checkInitialisedModel();
            var attributeModel = this._getAttributeModel(attributeId);
            if (!attributeModel) {
                return null;
            }
            return new AttributeInterface(this._getAttributeModel(attributeId));
        },

        /**
         * Get all the attributes of the current document
         *
         * @returns [AttributeInterface]
         */
        getAttributes: function documentControllerGetAttributes()
        {
            this._checkInitialisedModel();
            return this._model.get("attributes").map(function documentController_mapAttribute(currentAttribute)
                {
                    return new AttributeInterface(currentAttribute);
                }
            );
        },

        /**
         * Check if a menu exist
         *
         * @param menuId
         * @return {boolean}
         */
        hasMenu: function documentController_hasMenu(menuId)
        {
            this._checkInitialisedModel();
            var menu = this._getMenuModel(menuId);
            return !!menu;
        },

        /**
         * Get the menu interface object
         *
         * @param menuId
         * @returns MenuInterface
         */
        getMenu: function documentControllerGetMenu(menuId)
        {
            this._checkInitialisedModel();
            var menu = this._getMenuModel(menuId);
            if (!menu) {
                return null;
            }
            return new MenuInterface(menu);
        },

        /**
         * Get all the menu of the current document
         *
         * @returns [MenuInterface]
         */
        getMenus: function documentControllerGetMenus()
        {
            this._checkInitialisedModel();
            return this._model.get("menus").map(function documentController_mapMenu(currentMenu)
                {
                    return new MenuInterface(currentMenu);
                }
            );
        },


        /**
         * Select a tab
         *
         * @param tabId
         * @returns void
         */
        selectTab: function documentControllerSelectTab(tabId)
        {
            this._checkInitialisedModel();
            var attributeModel = this._getAttributeModel(tabId);
            if (!attributeModel) {
                throw new Error('The attribute "' + tabId + '" cannot be found.');
            }
            if (attributeModel.get("type") !== "tab") {
                throw new Error('The attribute "' + tabId + '" is not a tab.');
            }

            this._model.trigger("doSelectTab", tabId);
        },

        /**
         * Draw tab content
         *
         * @param tabId
         * @returns void
         */
        drawTab: function documentControllerDrawTab(tabId)
        {
            this._checkInitialisedModel();
            var attributeModel = this._getAttributeModel(tabId);
            if (!attributeModel) {
                throw new Error('The attribute "' + tabId + '" cannot be found.');
            }
            if (attributeModel.get("type") !== "tab") {
                throw new Error('The attribute "' + tabId + '" is not a tab.');
            }

            this._model.trigger("doDrawTab", tabId);
        },

        /**
         * Get an attribute value
         *
         * @param attributeId
         * @param type string (current|previous|initial|all) what kind of value (default : current)
         * @returns {*}
         */
        getValue: function documentControllerGetValue(attributeId, type)
        {
            var attribute;
            this._checkInitialisedModel();
            var attributeModel = this._getAttributeModel(attributeId);
            if (!attributeModel) {
                return null;
            }
            attribute = new AttributeInterface(attributeModel);
            return _.clone(attribute.getValue(type));
        },

        /**
         * Get all the values
         *
         * @returns {*|{}}
         */
        getValues: function documentControllerGetValues()
        {
            this._checkInitialisedModel();
            return this._model.getValues();
        },

        /**
         * Get customData from render view model
         * @returns {*}
         */
        getCustomServerData: function documentControllerGetServerCustomData()
        {
            this._checkInitialisedModel();
            return this._model.get("customServerData");
        },
        /**
         * Add customData from render view model
         * @returns {*}
         */
        addCustomClientData: function documentControllerAddCustomClientData(documentCheck, value)
        {
            var currentWidget = this;
            this._checkInitialisedModel();
            //First case no data, so documentCheck is data
            if (_.isUndefined(value)) {
                value = documentCheck;
                documentCheck = {};
            }
            //Second case documentCheck is a function and data is object
            if (_.isFunction(documentCheck) && _.isObject(value)) {
                documentCheck = {"documentCheck": documentCheck};
            }
            //Third case documentCheck is an object and data is object => check if documentCheck property exist
            if (_.isObject(value) && _.isObject(documentCheck)) {
                documentCheck = _.defaults(documentCheck, {
                    "documentCheck": function clientCustomOK()
                    {
                        return true;
                    },
                    once: true
                });
            } else {
                throw new Error("Constraint must be an value or a function and a value");
            }
            //Register the customClientData
            _.each(value, function documentControllerAddCustomClientDataEach(currentValue, currentKey)
            {
                currentWidget._customClientData[currentKey] = {
                    "value": currentValue,
                    "documentCheck": documentCheck.documentCheck,
                    "once": documentCheck.once
                };
            });
        },
        /**
         * Get customData from render view model
         * @returns {*}
         */
        setCustomClientData: function documentControllerSetCustomClientData(documentCheck, value)
        {
            console.error("this function (setCustomClientData) is deprecated");
            return this.addCustomClientData(documentCheck, value);
        },
        /**
         * Get customData from render view model
         * @returns {*}
         */
        getCustomClientData: function documentControllerSetCustomClientData(deleteOnce)
        {
            var values = {}, currentWidget = this, $element, properties, newCustomData = {};
            this._checkInitialisedModel();
            properties = this.getProperties();
            $element = $(currentWidget.element);
            _.each(currentWidget._customClientData, function analyzeCustomClient(currentCustom, key)
            {
                if (currentCustom.documentCheck.call($element, properties)) {
                    values[key] = currentCustom.value;
                    if (deleteOnce === true && !currentCustom.once) {
                        newCustomData[key] = currentCustom;
                    }
                } else {
                    if (deleteOnce === true) {
                        newCustomData[key] = currentCustom;
                    }
                }
            });
            if (deleteOnce === true) {
                currentWidget._customClientData = newCustomData;
            }
            return values;
        },

        /**
         * Delete a custom data
         * @returns {*}
         */
        removeCustomClientData: function documentControllerRemoveCustomClientData(key)
        {
            if (this._customClientData[key]) {
                delete this._customClientData[key];
            }
            return this;
        },
        /**
         * Set a value
         * Trigger a change event
         *
         * @param attributeId string attribute identifier
         * @param value object { "value" : *, "displayValue" : *}
         * @returns {*}
         */
        setValue: function documentControllerSetValue(attributeId, value)
        {
            this._checkInitialisedModel();
            var attributeModel = this._getAttributeModel(attributeId);
            if (!attributeModel) {
                throw new Error("Unable to find attribute " + attributeId);
            }
            var attributeInterface = new AttributeInterface(attributeModel);
            var index;
            var currentValueLength;
            var i;

            if (attributeModel.getParent().get("type") === "array") {
                attributeInterface.setValue(value, true); // Just verify value conditions
                if (!_.isArray(value)) {
                    index = value.index;
                } else {
                    index = value.length - 1;
                }
                currentValueLength = attributeInterface.getValue().length;
                attributeInterface.setValue(value);


                // Pad values of complete array with default values
                var arrayModel=attributeModel.getParent();
                var modifiedColumns={};
                arrayModel.get("content").each(function (aModel) {
                   var aValue=_.clone(aModel.get("attributeValue"));
                   var defaultValue=aModel.get("defaultValue");

                   if (!defaultValue) {
                       defaultValue=aModel.hasMultipleOption()?[]:{value:null, displayValue:""};
                   }

                    for (i = currentValueLength; i <= index; i++) {
                       if (_.isUndefined(aValue[i])) {
                           aValue[i]=defaultValue;
                           modifiedColumns[aModel.id]={model:aModel, values:aValue};
                       }
                    }
                });

                _.each(modifiedColumns, function documentControllerPadValues(modData) {
                     _.defer(function documentControllerPadValue() {
                         modData.model.set("attributeValue", modData.values);
                     });
                });

                return;
            }
            return attributeInterface.setValue(value);
        },

        /**
         * Add a row to an array
         *
         * @param attributeId string attribute array
         * @param values object { "attributeId" : { "value" : *, "displayValue" : * }, ...}
         */
        appendArrayRow: function documentControllerAddArrayRow(attributeId, values)
        {
            this._checkInitialisedModel();
            var attribute = this._getAttributeModel(attributeId);

            if (!attribute) {
                throw new Error("Unable to find attribute " + attributeId);
            }

            if (attribute.get("type") !== "array") {
                throw new Error("Attribute " + attributeId + " must be an attribute of type array");
            }
            if (!_.isObject(values)) {
                throw new Error("Values must be an object where each properties is an attribute of the array for " + attributeId);
            }
            attribute.get("content").each(function documentController_addACell(currentAttribute)
            {
                var newValue = values[currentAttribute.id];
                var currentValue = currentAttribute.getValue();
                if (_.isUndefined(newValue)) {
                    // Set default value if no value defined
                    currentAttribute.createIndexedValue(currentValue.length, false, ( _.isEmpty(values)));
                } else {
                    newValue = _.defaults(newValue, {value: "", displayValue: newValue.value});
                    currentAttribute.addValue(newValue);
                }
            });
        },

        /**
         * Add a row before another row
         *
         * @param attributeId string attribute array
         * @param values object { "attributeId" : { "value" : *, "displayValue" : * }, ...}
         * @param index int index of the row
         */
        insertBeforeArrayRow: function documentControllerInsertBeforeArrayRow(attributeId, values, index)
        {
            this._checkInitialisedModel();
            var attribute = this._getAttributeModel(attributeId), maxValue;
            if (!attribute) {
                throw new Error("Unable to find attribute " + attributeId);
            }
            if (attribute.get("type") !== "array") {
                throw new Error("Attribute " + attributeId + " must be an attribute of type array");
            }
            if (!_.isObject(values)) {
                throw new Error("Values must be an object where each properties is an attribute of the array for " + attributeId);
            }
            maxValue = this._getMaxIndex(attribute);
            if (index < 0 || index > maxValue) {
                throw new Error("Index must be between 0 and " + maxValue);
            }
            attribute.get("content").each(function documentController_addACell(currentAttribute)
            {
                var currentValue = values[currentAttribute.id];
                if (!_.isUndefined(currentValue)) {
                    currentValue = _.defaults(currentValue, {value: "", displayValue: currentValue.value});
                } else {
                    currentValue = currentAttribute.attributes.defaultValue;
                    if (!currentValue) {
                        currentValue = {value: "", displayValue: ""};
                    }
                }
                currentAttribute.addIndexedValue(currentValue, index);
            });
        },

        /**
         * Remove an array row
         * @param attributeId string attribute array
         * @param index int index of the row
         */
        removeArrayRow: function documentControllerRemoveArrayRow(attributeId, index)
        {
            this._checkInitialisedModel();
            var attribute = this._getAttributeModel(attributeId), maxIndex;
            if (!attribute) {
                throw new Error("Unable to find attribute " + attributeId);
            }
            if (attribute.get("type") !== "array") {
                throw Error("Attribute " + attributeId + " must be an attribute of type array");
            }
            maxIndex = this._getMaxIndex(attribute) - 1;
            if (index < 0 || index > maxIndex) {
                throw Error("Index must be between 0 and " + maxIndex + " for " + attributeId);
            }
            attribute.get("content").each(function documentController_removeACell(currentAttribute)
            {
                currentAttribute.removeIndexValue(index);
            });
            attribute.removeIndexedLine(index);
        },

        /**
         * Add a constraint to the widget
         *
         * @param options object { "name" : string, "documentCheck": function}
         * @param callback function callback called when the event is triggered
         * @returns {*}
         */
        addConstraint: function documentControlleraddConstraint(options, callback)
        {
            var currentConstraint, currentWidget = this, uniqueName;
            if (_.isUndefined(callback) && _.isFunction(options)) {
                callback = options;
                options = {};
            }
            if (_.isObject(options) && _.isUndefined(callback)) {
                if (!options.name) {
                    throw new Error("When a constraint is initiated with a single object, this object needs to have the name property ".JSON.stringify(options));
                }
            } else {
                _.defaults(options, {
                    "documentCheck": function documentController_defaultDocumentCheck()
                    {
                        return true;
                    },
                    "attributeCheck": function documentController_defaultAttributeCheck()
                    {
                        return true;
                    },
                    "constraintCheck": callback,
                    "name": _.uniqueId("constraint"),
                    "externalConstraint": false,
                    "once": false
                });
            }
            currentConstraint = options;
            if (!_.isFunction(currentConstraint.constraintCheck)) {
                throw new Error("An event need a callback");
            }
            //If constraint is once : wrap it an callback that execute callback and delete it
            if (currentConstraint.once === true) {
                currentConstraint.eventCallback = _.wrap(currentConstraint.constraintCheck, function documentController_onceWrapper(callback)
                {
                    try {
                        callback.apply(this, _.rest(arguments));
                    } catch (e) {
                        console.error(e);
                    }
                    currentWidget.removeConstraint(currentConstraint.name, currentConstraint.externalConstraint);
                });
            }
            uniqueName = (currentConstraint.externalConstraint ? "external_" : "internal_") + currentConstraint.name;
            this.options.constraintList[uniqueName] = currentConstraint;
            this._initActivatedConstraint();
            return currentConstraint.name;
        },

        /**
         * List the constraint of the widget
         *
         * @returns {*}
         */
        listConstraints: function documentControllerListConstraint()
        {
            return this.options.constraintList;
        },

        /**
         * Remove a constraint of the widget
         *
         * @param constraintName
         * @param allKind
         * @returns {*}
         */
        removeConstraint: function documentControllerRemoveConstraint(constraintName, allKind)
        {
            var removed = [], newConstraintList, constraintList,
                testRegExp = new RegExp("\\" + constraintName + "$");
// jscs:disable disallowImplicitTypeConversion
            allKind = !!allKind;
// jscs:enable disallowImplicitTypeConversion
            newConstraintList = _.filter(this.options.constraintList, function documentController_removeConstraint(currentConstrait)
            {
                if ((allKind || !currentConstrait.externalConstraint) && (currentConstrait.name === constraintName || testRegExp.test(currentConstrait.name))) {
                    removed.push(currentConstrait);
                    return false;
                }
                return true;
            });
            constraintList = {};
            _.each(newConstraintList, function documentController_rebuildConstraintList(currentConstraint)
            {
                var uniqueName = (currentConstraint.externalConstraint ? "external_" : "internal_") + currentConstraint.name;
                constraintList[uniqueName] = currentConstraint;
            });
            this.options.constraintList = constraintList;
            this._initActivatedConstraint();
            return removed;
        },

        /**
         * Add an event to the widget
         *
         * @param eventType string kind of event
         * @param options object { "name" : string, "documentCheck": function}
         * @param callback function callback called when the event is triggered
         * @returns {*|Window.options.name}
         */
        addEventListener: function documentControllerAddEvent(eventType, options, callback)
        {
            var currentEvent, currentWidget = this;
            //options is not mandatory and the callback can be the second parameters
            if (_.isUndefined(callback) && _.isFunction(options)) {
                callback = options;
                options = {};
            }
            // the first parameters can be the final object (chain removeEvent and addEvent)
            if (_.isObject(eventType) && _.isUndefined(options) && _.isUndefined(callback)) {
                currentEvent = eventType;
                if (!currentEvent.name) {
                    throw new Error("When an event is initiated with a single object, this object needs to have the name property " + JSON.stringify(currentEvent));
                }
            } else {
                currentEvent = _.defaults(options, {
                    "name": _.uniqueId("event_" + eventType),
                    "eventType": eventType,
                    "eventCallback": callback,
                    "externalEvent": false,
                    "once": false
                });
            }
            // the eventType must be one the list
            this._checkEventName(currentEvent.eventType);
            // callback is mandatory and must be a function
            if (!_.isFunction(currentEvent.eventCallback)) {
                throw new Error("An event needs a callback that is a function");
            }
            //If event is once : wrap it an callback that execute event and delete it
            if (currentEvent.once === true) {
                currentEvent.eventCallback = _.wrap(currentEvent.eventCallback, function documentController_onceWrapper(callback)
                {
                    currentWidget.removeEventListener(currentEvent.name, currentEvent.externalEvent);
                    try {
                        callback.apply(this, _.rest(arguments));
                    } catch (e) {
                        console.error(e);
                    }
                });
            }
            this._addAndInitNewEvents(currentEvent);
            // return the name of the event
            return currentEvent.name;
        },

        /**
         * List of the events of the current widget
         *
         * @returns {*}
         */
        listEventListeners: function documentControllerListEvents()
        {
            return this.options.eventListener;
        },

        /**
         * Remove an event of the current widget
         *
         * @param eventName string can be an event name or a namespace
         * @param allKind remove internal/external events
         * @returns {*}
         */
        removeEventListener: function documentControllerRemoveEvent(eventName, allKind)
        {
            var removed = [],
                testRegExp = new RegExp("\\" + eventName + "$"), newList, eventList;
// jscs:disable
            allKind = !!allKind;
// jscs:enable
            newList = _.filter(this.options.eventListener, function documentController_removeCurrentEvent(currentEvent)
            {
                if ((allKind || !currentEvent.externalEvent) && (currentEvent.name === eventName || testRegExp.test(currentEvent.name))) {
                    removed.push(currentEvent);
                    return false;
                }
                return true;
            });
            eventList = {};
            _.each(newList, function documentController__rebuildEventList(currentEvent)
            {
                var uniqueName = (currentEvent.externalEvent ? "external_" : "internal_") + currentEvent.name;
                eventList[uniqueName] = currentEvent;
            });
            this.options.eventListener = eventList;
            this._initActivatedEventListeners({"launchReady": false});
            return removed;
        },

        /**
         * Trigger an event
         *
         * @param eventName
         */
        triggerEvent: function documentController_triggerEvent(eventName)
        {
            var args=_.toArray(arguments);
            this._checkInitialisedModel();
            this._checkEventName(eventName);

            args.splice(1, 0, null); // Add null originalEvent
            return this._triggerControllerEvent.apply(this, args);
        },

        /**
         * Hide a visible attribute
         *
         * @param attributeId
         */
        hideAttribute: function documentControllerHideAttribute(attributeId)
        {
            this._checkInitialisedView();
            var attributeModel = this._getAttributeModel(attributeId);
            if (!attributeModel) {
                console.log("Unable find and hide the attribute " + attributeId);
                return;
            }
            attributeModel.trigger("hide");
        },
        /**
         * show a visible attribute (previously hidden)
         *
         * @param attributeId
         */
        showAttribute: function documentControllerShowAttribute(attributeId)
        {
            this._checkInitialisedView();
            var attributeModel = this._getAttributeModel(attributeId);
            if (!attributeModel) {
                console.log("Unable find and show the attribute " + attributeId);
                return;
            }
            attributeModel.trigger("show");
        },

        /**
         * Display a message to the user
         *
         * @param message
         */
        showMessage: function documentControllerShowMessage(message)
        {
            this._checkInitialisedView();
            if (_.isString(message)) {
                message = {
                    type: "info",
                    message: message
                };
            }
            if (_.isObject(message)) {
                message = _.defaults(message, {
                    type: "info"
                });
            }
            this.$notification.dcpNotification("show", message.type, message);
        },

        /**
         * Display loading bar
         *
         * @param message
         * @param px
         */
        maskDocument: function documentController(message, px)
        {
            this.$loading.dcpLoading('show');
            if (message) {
                this.$loading.dcpLoading('setTitle', message);
            }
            if (px) {
                this.$loading.dcpLoading('setPercent', px);
            }
        },

        /**
         * Hide loading bar
         */
        unmaskDocument: function documentController_unmaskDocument(force)
        {
            this.$loading.dcpLoading('hide', force);
        },

        /**
         * Add an error message to an attribute
         *
         * @param attributeId
         * @param message
         * @param index
         */
        setAttributeErrorMessage: function documentControllersetAttributeErrorMessage(attributeId, message, index)
        {
            this._checkInitialisedView();
            var attributeModel = this._getAttributeModel(attributeId);
            if (!attributeModel) {
                console.log("Unable find and show the attribute " + attributeId);
                return;
            }
            attributeModel.setErrorMessage(message, index);
        },

        /**
         * Clean the error message of an attribute
         *
         * @param attributeId
         * @param index
         */
        cleanAttributeErrorMessage: function documentControllercleanAttributeErrorMessage(attributeId, index)
        {
            this._checkInitialisedView();
            var attributeModel = this._getAttributeModel(attributeId);
            if (!attributeModel) {
                console.log("Unable find and show the attribute " + attributeId);
                return;
            }
            attributeModel.setErrorMessage(null, index);
        },

        injectCSS: function documentController_injectCSS(cssToInject)
        {
            this._checkInitialisedView();
            if (!_.isArray(cssToInject) && !_.isString(cssToInject)) {
                throw new Error("The css to inject must be an array string or a string");
            }
            if (_.isString(cssToInject)) {
                cssToInject = [cssToInject];
            }

            this._model.injectCSS(cssToInject);
        },

        injectJS: function documentController_injectCSS(jsToInject)
        {
            this._checkInitialisedView();
            if (!_.isArray(jsToInject) && !_.isString(jsToInject)) {
                throw new Error("The js to inject must be an array string or a string");
            }
            if (_.isString(jsToInject)) {
                jsToInject = [jsToInject];
            }

            return this._model.injectJS(jsToInject);
        },
        /**
         * tryToDestroy the widget
         *
         * @return Promise
         */
        tryToDestroy: function documentController_tryToDestroy()
        {
            var currentWidget = this;
            return new Promise(function documentController_promiseDestroy(resolve, reject)
            {
                var event = {prevent: false};
                if (!currentWidget._model) {
                    resolve();
                    return;
                }
                if (currentWidget._model &&
                    currentWidget._model.isModified() &&
                    !window.confirm(currentWidget._model.get("properties").get("title") + "\n" + i18n.___("The form has been modified without saving, do you want to close it ?", "ddui"))) {
                    reject("Unable to destroy because user refuses it");
                    return;
                }
                event.prevent = !currentWidget._triggerControllerEvent("beforeClose", null, currentWidget._model.getServerProperties());
                if (event.prevent) {
                    reject("Unable to destroy because before close refuses it");
                    return;
                }
                resolve();
            });
        }

    });

    return $.fn.documentController;
});
