import { AnakeenController } from "./types/ControllerTypes";
import ControllerOptions = AnakeenController.Types.ControllerOptions;
import SmartElementProperties = AnakeenController.Types.SmartElementProperties;
import ViewData = AnakeenController.Types.ViewData;
import DOMReference = AnakeenController.Types.DOMReference;
import * as $ from "jquery";
import * as _ from "underscore";
import * as Backbone from "backbone";
import Router = require("../../routers/router.js");
import Model = require("../../models/mDocument");
import AttributeInterface = require("../../controllerObjects/attributeInterface");
import MenuInterface = require("../../controllerObjects/menuInterface");
import TransitionInterface = require("../../controllerObjects/transitionInterface");
import View = require("../../views/document/vDocument");
import TransitionModel = require("../../models/mTransition");
import TransitionView = require("../../views/workflow/vTransition");
import MenuModel = require("../../models/mMenu");
import i18n = require("../../i18n/documentCatalog");
import "../../widgets/widget";
import "../../widgets/window/wConfirm";
import "../../widgets/window/wLoading";
import "../../widgets/window/wNotification";

const DEFAULT_OPTIONS: ControllerOptions = {
  eventPrefix: "smart-element",
  initid: null,
  viewId: undefined,
  revision: undefined,
  constraintList: [],
  eventListener: [],
  noRouter: false,
  activatedConstraint: {},
  activatedEventListener: {},
  _initializedModel: false,
  _initializedView: false,
  customClientData: {}
};

class ErrorModelNonInitialized extends Error {
  constructor(message?) {
    super();
    this.message =
      message ||
      "The model is not initialized, use fetchDocument to initialise it.";
    this.name = "ErrorModelNonInitialized";
    this.stack = new Error().stack;
  }
}

interface ISmartElementModel extends Backbone.Model {
  _customClientData: {};
  _customRequestData: {};
  getModelProperties(): SmartElementProperties;
  fetchDocument(viewData?: ViewData, options?: any): Promise<any>;
  hasUploadingFile(): boolean;
  saveDocument(): Promise<any>;
  restoreDocument(): Promise<any>;
  deleteDocument(): Promise<any>;
  isModified(): boolean;
  getServerProperties(): any;
  getValues(): any;
  injectJS(jsToInject: string[]): Promise<any>;
  injectCSS(cssToInject: string[]): Promise<any>;
}

export default class SmartElementController {
  protected element: JQuery<DOMReference> = null;
  protected $smartElement: JQuery<DOMReference> = null;
  protected view: Backbone.View = null;
  protected model: ISmartElementModel = null;
  protected router: Backbone.Router = null;
  protected options: ControllerOptions = DEFAULT_OPTIONS;
  protected initialized: { model: boolean; view: boolean } = {
    model: false,
    view: false
  };
  protected activatedConstraint: {} = {};
  protected activatedEventListener: {} = {};
  protected _customClientData: {} = {};
  protected $loading: JQuery & { dcpLoading(...args): JQuery } = null;
  protected $notification: JQuery & { dcpNotification(...args): JQuery } = null;

  constructor(
    dom: DOMReference,
    viewData: ViewData,
    options?: ControllerOptions
  ) {
    this.options = options || DEFAULT_OPTIONS;
    if (viewData) {
      this.options.initid = viewData.initid;
      this.options.viewId = viewData.viewId;
      this.options.revision = viewData.revision;
    }
    // @ts-ignore
    this.element = $(dom);
    this.initialized = {
      model: false,
      view: false
    };
    if (!this.options.initid) {
      return;
    }
    this.initializeSmartElement({}, this.options.customClientData);
  }

  private initializeSmartElement(options, customClientData) {
    let promise;
    const initializeSuccess = (...args: any[]) => {
      this.initialized.model = true;
    };
    options = options || {};
    this.initExternalElements();
    this.initModel(this.getModelValue());
    this.initView();
    if (options.success) {
      options.success = _.wrap(options.success, (...args) => {
        const success = args[0];
        initializeSuccess.apply(this, _.rest(args));
        return success.apply(this, _.rest(args));
      });
    }
    if (customClientData) {
      this.model._customClientData = customClientData;
    }
    promise = this.model.fetchDocument(this.getModelValue(), options);
    if (!options.success) {
      promise.then(initializeSuccess);
    }

    this.initRouter({ useHistory: !this.options.noRouter });

    return promise;
  }

  /**
   * Return essential element of the current smart element
   *
   * @returns {Object}
   * @private
   */
  private getModelValue() {
    return _.pick(this.options, "initid", "viewId", "revision");
  }

  /**
   * Init the external elements (loading bar and notification widget)
   * @private
   */
  private initExternalElements() {
    // @ts-ignore
    this.$loading = $(".dcpLoading").dcpLoading();
    // @ts-ignore
    this.$notification = $("body").dcpNotification(window.dcp.notifications); // active notification
  }

  /**
   * Init the model and bind the events
   *
   * @param initialValue
   * @returns DocumentModel
   * @private
   */
  private initModel(initialValue) {
    let model;

    //Don't reinit the model
    if (!this.model) {
      model = new Model(initialValue);
      this.model = model;
      this.initModelEvents();
    } else {
      this.reinitModel();
    }
    return model;
  }

  /**
   * Clear and reinit the model with current widget values
   *
   * @private
   */
  private reinitModel() {
    this.model.set(this.getModelValue());
  }

  /**
   * Init the view and bind the events
   *
   * @returns DocumentView
   * @private
   */
  private initView() {
    let seView;
    ///Don't reinit view
    if (!this.view) {
      this.initDom();
      seView = new View({
        model: this.model,
        el: this.$smartElement[0]
      });
      this.view = seView;
      this.initViewEvents();
    }
    return this.view;
  }

  /**
   * Generate the dom where the view is inserted
   * @private
   */
  private initDom() {
    const $se = this.element.find(".dcpDocument");
    if (!this.$smartElement || $se.length === 0) {
      this.element.append('<div class="dcpDocument"></div>');
      this.$smartElement = this.element.find(".dcpDocument");
    }
  }

  /**
   * Bind the model event
   *
   * Re-trigger the event
   *
   * @private
   */
  private initModelEvents() {
    this.model.listenTo(this.model, "invalid", (model, error) => {
      const result = this.triggerControllerEvent(
        "displayError",
        null,
        this.getProperties(),
        error
      );
      if (result) {
        this.$notification.dcpNotification("showError", error);
      }
    });
    this.model.listenTo(this.model, "showError", error => {
      const result = this.triggerControllerEvent(
        "displayError",
        null,
        this.getProperties(),
        error
      );
      if (result) {
        this.$notification.dcpNotification("showError", error);
      }
    });
    this.model.listenTo(this.model, "showMessage", msg => {
      const result = this.triggerControllerEvent(
        "displayMessage",
        null,
        this.getProperties(),
        msg
      );
      if (result) {
        this.$notification.dcpNotification("show", msg.type, msg);
      }
    });
    this.model.listenTo(this.model, "reload", () => {
      // this._initModel(this._getModelValue());
      // this._initView();
      this.model.fetchDocument();
    });
    this.model.listenTo(this.model, "sync", () => {
      this.initialized.model = true;
      this.options.initid = this.model.id;
      this.options.viewId = this.model.get("viewId");
      this.options.revision = this.model.get("revision");
      this.element.data("document", this.getModelValue());
      this._initActivatedConstraint();
      this._initActivatedEventListeners({ launchReady: false });
    });
    this.model.listenTo(this.model, "beforeRender", event => {
      event.prevent = !this.triggerControllerEvent(
        "beforeRender",
        event,
        this.getProperties(),
        this.model.getModelProperties()
      );
    });
    this.model.listenTo(
      this.model,
      "beforeClose",
      (event, nextDocument, customClientData) => {
        if (this.initialized.view) {
          event.prevent = !this.triggerControllerEvent(
            "beforeClose",
            event,
            this.getProperties(),
            nextDocument,
            customClientData
          );
        }
      }
    );
    this.model.listenTo(this.model, "close", oldProperties => {
      if (this.initialized.view) {
        this.triggerControllerEvent(
          "close",
          null,
          this.getProperties(),
          oldProperties
        );
      }
      this.initialized.view = false;
    });
    this.model.listenTo(this.model, "getCustomClientData", () => {
      try {
        this.model._customClientData = this.getCustomClientData(false);
      } catch (e) {
        //no test here
      }
    });
    this.model.listenTo(this.model, "beforeSave", (event, customClientData) => {
      const requestOptions = {
        getRequestData: () => {
          return this.model.toJSON();
        },
        setRequestData: data => {
          this.model._customRequestData = data;
        }
      };
      event.prevent = !this.triggerControllerEvent(
        "beforeSave",
        event,
        this.getProperties(),
        requestOptions,
        customClientData
      );
    });
    this.model.listenTo(this.model, "afterSave", oldProperties => {
      this.triggerControllerEvent(
        "afterSave",
        null,
        this.getProperties(),
        oldProperties
      );
    });
    this.model.listenTo(this.model, "beforeRestore", event => {
      event.prevent = !this.triggerControllerEvent(
        "beforeRestore",
        event,
        this.getProperties()
      );
    });
    this.model.listenTo(this.model, "afterRestore", oldProperties => {
      this.triggerControllerEvent(
        "afterRestore",
        null,
        this.getProperties(),
        oldProperties
      );
    });
    this.model.listenTo(
      this.model,
      "beforeDelete",
      (event, customClientData) => {
        event.prevent = !this.triggerControllerEvent(
          "beforeDelete",
          event,
          this.getProperties(),
          this.model.getModelProperties(),
          customClientData
        );
      }
    );
    this.model.listenTo(this.model, "afterDelete", oldProperties => {
      this.triggerControllerEvent(
        "afterDelete",
        null,
        this.getProperties(),
        oldProperties
      );
    });
    this.model.listenTo(this.model, "validate", event => {
      event.prevent = !this.triggerControllerEvent(
        "validate",
        event,
        this.getProperties()
      );
    });
    this.model.listenTo(this.model, "changeValue", options => {
      try {
        const currentAttribute = this.getAttribute(options.attributeId);
        let index = 0;
        const values = currentAttribute.getValue("all");
        const mAttribute = this._getAttributeModel(options.attributeId);
        if (mAttribute.getParent().get("type") !== "array") {
          index = -1;
        } else {
          const changesIndex = [];
          _.each(values.current, (currentValue: any) => {
            let previous = values.previous[index];
            if (!previous) {
              changesIndex.push(index);
            } else {
              if (_.isArray(currentValue)) {
                currentValue = currentValue.join(",");
              }
              currentValue = _.has(currentValue, "value")
                ? currentValue.value
                : currentValue;
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
          index = changesIndex.length === 1 ? changesIndex[0] : -1;
        }
        this._triggerAttributeControllerEvent(
          "change",
          null,
          currentAttribute,
          this.getProperties(),
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
    this.model.listenTo(
      this.model,
      "beforeAttributeRender",
      (event, attributeId, $el, index) => {
        try {
          const currentAttribute = this.getAttribute(attributeId);
          event.prevent = !this._triggerAttributeControllerEvent(
            "attributeBeforeRender",
            event,
            currentAttribute,
            this.getProperties(),
            currentAttribute,
            $el,
            index
          );
        } catch (error) {
          if (!(error instanceof ErrorModelNonInitialized)) {
            console.error(error);
          }
        }
      }
    );
    this.model.listenTo(
      this.model,
      "attributeRender",
      (attributeId, $el, index) => {
        try {
          const currentAttribute = this.getAttribute(attributeId);
          this._triggerAttributeControllerEvent(
            "attributeReady",
            null,
            currentAttribute,
            this.getProperties(),
            currentAttribute,
            $el,
            index
          );
        } catch (error) {
          if (!(error instanceof ErrorModelNonInitialized)) {
            console.error(error);
          }
        }
      }
    );
    this.model.listenTo(this.model, "arrayModified", options => {
      try {
        const currentAttribute = this.getAttribute(options.attributeId);
        this._triggerAttributeControllerEvent(
          "attributeArrayChange",
          null,
          currentAttribute,
          this.getProperties(),
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
    this.model.listenTo(
      this.model,
      "internalLinkSelected",
      (event, options) => {
        event.prevent = !this.triggerControllerEvent(
          "actionClick",
          event,
          this.getProperties(),
          options
        );
      }
    );
    this.model.listenTo(
      this.model,
      "downloadFile",
      (event, attrid, options) => {
        try {
          const currentAttribute = this.getAttribute(attrid);
          event.prevent = !this._triggerAttributeControllerEvent(
            "attributeDownloadFile",
            event,
            currentAttribute,
            this.getProperties(),
            currentAttribute,
            options.$el,
            options.index
          );
        } catch (error) {
          if (!(error instanceof ErrorModelNonInitialized)) {
            console.error(error);
          }
        }
      }
    );
    this.model.listenTo(this.model, "uploadFile", (event, attrid, options) => {
      try {
        const currentAttribute = this.getAttribute(attrid);
        event.prevent = !this._triggerAttributeControllerEvent(
          "attributeUploadFile",
          event,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          options.$el,
          options.index,
          {
            file: options.file,
            hasUploadingFiles: this.model.hasUploadingFile()
          }
        );
      } catch (error) {
        if (!(error instanceof ErrorModelNonInitialized)) {
          console.error(error);
        }
      }
    });
    this.model.listenTo(
      this.model,
      "uploadFileDone",
      (event, attrid, options) => {
        try {
          const currentAttribute = this.getAttribute(attrid);
          event.prevent = !this._triggerAttributeControllerEvent(
            "attributeUploadFileDone",
            event,
            currentAttribute,
            this.getProperties(),
            currentAttribute,
            options.$el,
            options.index,
            {
              file: options.file,
              hasUploadingFiles: this.model.hasUploadingFile()
            }
          );
        } catch (error) {
          if (!(error instanceof ErrorModelNonInitialized)) {
            console.error(error);
          }
        }
      }
    );

    this.model.listenTo(
      this.model,
      "attributeBeforeTabSelect",
      (event, attrid) => {
        const currentAttribute = this.getAttribute(attrid);
        let prevent;

        prevent = !this._triggerAttributeControllerEvent(
          "attributeBeforeTabSelect",
          event,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          $(event.item)
        );
        if (prevent) {
          event.preventDefault();
        }
      }
    );
    this.model.listenTo(
      this.model,
      "attributeTabChange",
      (event, attrid, $el, data) => {
        const currentAttribute = this.getAttribute(attrid);

        this._triggerAttributeControllerEvent(
          "attributeTabChange",
          event,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          $el,
          data
        );
      }
    );
    this.model.listenTo(
      this.model,
      "attributeAfterTabSelect",
      (event, attrid) => {
        const currentAttribute = this.getAttribute(attrid);

        this._triggerAttributeControllerEvent(
          "attributeAfterTabSelect",
          event,
          currentAttribute,
          this.getProperties(),
          currentAttribute,
          $(event.item)
        );
      }
    );
    this.model.listenTo(
      this.model,
      "helperSearch",
      (event, attrid, options) => {
        try {
          const currentAttribute = this.getAttribute(attrid);
          event.prevent = !this._triggerAttributeControllerEvent(
            "attributeHelperSearch",
            event,
            currentAttribute,
            this.getProperties(),
            currentAttribute,
            options
          );
        } catch (error) {
          if (!(error instanceof ErrorModelNonInitialized)) {
            console.error(error);
          }
        }
      }
    );
    this.model.listenTo(
      this.model,
      "helperResponse",
      (event, attrid, options) => {
        try {
          const currentAttribute = this.getAttribute(attrid);
          event.prevent = !this._triggerAttributeControllerEvent(
            "attributeHelperResponse",
            event,
            currentAttribute,
            this.getProperties(),
            currentAttribute,
            options
          );
        } catch (error) {
          if (!(error instanceof ErrorModelNonInitialized)) {
            console.error(error);
          }
        }
      }
    );
    this.model.listenTo(
      this.model,
      "helperSelect",
      (event, attrid, options) => {
        try {
          const currentAttribute = this.getAttribute(attrid);
          event.prevent = !this._triggerAttributeControllerEvent(
            "attributeHelperSelect",
            event,
            currentAttribute,
            this.getProperties(),
            currentAttribute,
            options
          );
        } catch (error) {
          if (!(error instanceof ErrorModelNonInitialized)) {
            console.error(error);
          }
        }
      }
    );

    // listener to prevent default actions when anchorClick is triggered
    this.model.listenTo(this.model, "anchorClick", (event, attrid, options) => {
      try {
        const currentAttribute = this.getAttribute(attrid);
        event.prevent = !this._triggerAttributeControllerEvent(
          "attributeAnchorClick",
          event,
          currentAttribute,
          this.getProperties(),
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
    this.model.listenTo(
      this.model,
      "createDialogListener",
      (event, attrid, options) => {
        try {
          const currentAttribute = this.getAttribute(attrid);
          let triggername = "attributeCreateDialogDocument";
          // Uppercase first letter
          triggername +=
            options.triggerId.charAt(0).toUpperCase() +
            options.triggerId.slice(1);

          event.prevent = !this._triggerAttributeControllerEvent(
            triggername,
            event,
            currentAttribute,
            this.getProperties(),
            currentAttribute,
            options
          );
        } catch (error) {
          if (!(error instanceof ErrorModelNonInitialized)) {
            console.error(error);
          }
        }
      }
    );
    this.model.listenTo(
      this.model,
      "constraint",
      (attribute, constraintController) => {
        try {
          const currentAttribute = this.getAttribute(attribute);
          const currentModel = this.getProperties();
          const $element = $(this.element);
          const addConstraint = currentConstraint => {
            if (_.isString(currentConstraint)) {
              constraintController.addConstraintMessage(currentConstraint);
            }
            if (
              _.isObject(currentConstraint) &&
              currentConstraint.message &&
              _.isNumber(currentConstraint.index)
            ) {
              constraintController.addConstraintMessage(
                currentConstraint.message,
                currentConstraint.index
              );
            }
          };
          _.each(this.activatedConstraint, (currentConstraint: any) => {
            try {
              if (
                currentConstraint.attributeCheck.apply($element, [
                  currentAttribute,
                  currentModel
                ])
              ) {
                const response = currentConstraint.constraintCheck.call(
                  $element,
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
      }
    );
    this.model.listenTo(
      this.model,
      "showTransition",
      _.bind(this._initAndDisplayTransition, this)
    );
    this.model.listenTo(
      this.model,
      "beforeParse",
      _.bind(() => {
        //Suppress customClientData after a sucessful transaction
        try {
          this.getCustomClientData(true);
        } catch (e) {
          //no test here
        }
      }, this)
    );
  }

  /**
   * Bind the view
   * Re-trigger the events
   *
   * @private
   */
  private initViewEvents() {
    this.view.on("cleanNotification", () => {
      this.$notification.dcpNotification("clear");
    });
    this.view.on("loading", (data, nbItem) => {
      this.$loading.dcpLoading("setPercent", data);
      if (nbItem) {
        this.$loading.dcpLoading("setNbItem", nbItem);
      }
    });
    this.view.on("loaderShow", (text, pc) => {
      console.time("xhr+render document view");
      this.$loading.dcpLoading("show", text, pc);
    });
    this.view.on("loaderHide", () => {
      this.$loading.dcpLoading("hide");
    });
    this.view.on("partRender", () => {
      this.$loading.dcpLoading("addItem");
    });
    this.view.on("renderDone", () => {
      console.timeEnd("xhr+render document view");
      this.$loading.dcpLoading("setPercent", 100);
      this.$loading.dcpLoading("setLabel", null);
      this.initialized.view = true;
      this.triggerControllerEvent("ready", null, this.getProperties());
      _.delay(() => {
        this.$loading.dcpLoading("hide", true);
        console.timeEnd("main");
      });
    });
    this.view.on("showMessage", message => {
      const result = this.triggerControllerEvent(
        "displayMessage",
        null,
        this.getProperties(),
        message
      );
      if (result) {
        this.$notification.dcpNotification("show", message.type, message);
      }
    });
    this.view.on("showSuccess", message => {
      if (message) {
        message.type = message.type ? message.type : "success";
      }
      const result = this.triggerControllerEvent(
        "displayMessage",
        null,
        this.getProperties(),
        message
      );
      if (result) {
        this.$notification.dcpNotification("showSuccess", message);
      }
    });
    this.view.on("reinit", () => {
      this.initModel(this.getModelValue());
      this.initView();
      this.model.fetchDocument();
    });
  }

  /**
   * Init the pushstate router
   *
   * @private
   */
  private initRouter(config) {
    if (this.router) {
      return this.router;
    }
    try {
      if (window.history && history.pushState) {
        Backbone.history.start({ pushState: true });
      } else {
        //For browser without API history
        Backbone.history.start();
      }
    } catch (e) {
      console.error(e);
    }
    this.router = new Router({
      document: this.model,
      useHistory: !config || config.useHistory
    });
  }

  /**
   * Init and display the change state pop-up
   *
   * @param nextState
   * @param transition
   * @param values
   * @param withoutInterface
   * @param transitionElementsCallBack
   * @param reinitOptions
   */
  private _initAndDisplayTransition(
    nextState,
    transition,
    values,
    withoutInterface,
    transitionElementsCallBack,
    reinitOptions
  ) {
    const $target = $('<div class="dcpTransition"/>');
    const transitionElements: any = {};
    let result;
    let transitionInterface;
    const documentServerProperties = this.getProperties();

    return new Promise((resolve, reject) => {
      result = !this.triggerControllerEvent(
        "beforeDisplayChangeState",
        null,
        this.getProperties(),
        new TransitionInterface(null, $target, nextState, transition)
      );
      if (result) {
        reject();
        return this;
      }

      //Init transition model
      transitionElements.model = new TransitionModel({
        documentId: this.model.id,
        documentModel: this.model,
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

      transitionInterface = new TransitionInterface(
        transitionElements.model,
        $target,
        nextState,
        transition
      );

      if (transitionElements.view) {
        //Propagate afterDisplayChange on renderDone
        transitionElements.view.once("renderTransitionWindowDone", () => {
          this.triggerControllerEvent(
            "afterDisplayTransition",
            null,
            this.getProperties(),
            transitionInterface
          );
        });
      }

      //Propagate the beforeTransition
      transitionElements.model.listenTo(
        transitionElements.model,
        "beforeChangeState",
        event => {
          event.prevent = !this.triggerControllerEvent(
            "beforeTransition",
            null,
            this.getProperties(),
            transitionInterface
          );
        }
      );

      //Propagate the beforeTransitionClose
      transitionElements.model.listenTo(
        transitionElements.model,
        "beforeChangeStateClose",
        event => {
          event.prevent = !this.triggerControllerEvent(
            "beforeTransitionClose",
            null,
            this.getProperties(),
            transitionInterface
          );
        }
      );

      transitionElements.model.listenTo(
        transitionElements.model,
        "showError",
        error => {
          this.triggerControllerEvent(
            "failTransition",
            null,
            this.getProperties(),
            transitionInterface,
            error
          );
          reject({ documentProperties: documentServerProperties });
        }
      );

      transitionElements.model.listenTo(
        transitionElements.model,
        "success",
        messages => {
          if (transitionElements.view) {
            transitionElements.view.$el.hide();
            this.view.once("renderDone", () => {
              transitionElements.view.remove();
              _.each(messages, message => {
                this.view.trigger("showMessage", message);
              });
            });
          }

          //delete the pop up when the render of the pop up is done
          this.triggerControllerEvent(
            "successTransition",
            null,
            this.getProperties(),
            transitionInterface
          );

          reinitOptions = reinitOptions || { revision: -1 };
          if (!_.has(reinitOptions, "revision")) {
            reinitOptions.revision = -1;
          }

          //Reinit the main model with last revision
          this.reinitSmartElement(reinitOptions).then(
            () => {
              resolve({ documentProperties: documentServerProperties });
            },
            () => {
              reject({ documentProperties: documentServerProperties });
            }
          );
        }
      );

      transitionElements.model.listenTo(
        this.model,
        "sync",
        function documentController_TransitionClose() {
          // @ts-ignore
          this.trigger("close");
        }
      );

      transitionElements.model.fetch({
        success: () => {
          if (withoutInterface === true) {
            transitionElements.model
              ._loadDocument(transitionElements.model)
              .then(() => {
                if (values) {
                  transitionElements.model.setValues(values);
                }
                if (_.isFunction(transitionElementsCallBack)) {
                  try {
                    transitionElementsCallBack(transitionElements);
                  } catch (e) {
                    //nothing to do;
                  }
                }
              })
              .then(() => {
                transitionElements.model.save(
                  {},
                  {
                    success: () => {
                      transitionElements.model.trigger("success");
                      resolve({
                        documentProperties: documentServerProperties
                      });
                    },
                    error: () => {
                      reject({
                        documentProperties: documentServerProperties
                      });
                    }
                  }
                );
              })
              .catch(function transitionModel_error() {
                reject({ documentProperties: documentServerProperties });
              });
          } else {
            transitionElements.model
              ._loadDocument(transitionElements.model)
              .then(() => {
                if (values) {
                  transitionElements.model.setValues(values);
                }
                if (_.isFunction(transitionElementsCallBack)) {
                  try {
                    transitionElementsCallBack(transitionElements);
                  } catch (e) {
                    //nothing to do;
                  }
                }
              })
              .then(() => {
                transitionElements.model.trigger("dduiDocumentReady");
              })
              .catch(() => {
                reject({ documentProperties: documentServerProperties });
              });
          }
        },
        error: (theModel, response, options) => {
          const errorTxt: { title: string; message?: string } = {
            title: "Transition Error"
          };
          if (options && options.errorThrown) {
            errorTxt.message = options.errorThrown;
          }
          this.$notification.dcpNotification("showError", errorTxt);
          transitionElements.model.trigger("showError", errorTxt);
        }
      });
    });
  }

  /**
   * Get a backbone model of an attribute
   *
   * @param attributeId
   * @returns {*}
   */
  private _getAttributeModel(attributeId) {
    const attributes = this.model.get("attributes");
    let attribute;
    if (!attributes) {
      throw new Error(
        'Attribute models not initialized yet : The attribute "' +
          attributeId +
          '" cannot be found.'
      );
    }
    attribute = this.model.get("attributes").get(attributeId);
    if (!attribute) {
      return undefined;
    }
    return attribute;
  }

  private _getMenuModel(menuId) {
    const menus = this.model.get("menus");

    let menu = menus.get(menuId);
    if (!menu && menus) {
      menus.each(itemMenu => {
        if (itemMenu.get("content")) {
          _.each(itemMenu.get("content"), (subMenu: any) => {
            if (subMenu.id === menuId) {
              menu = new MenuModel(subMenu);
            }
          });
        }
      });
    }
    return menu;
  }

  /**
   * Get all rendered attributes with their root dom node
   *
   * @returns {*}
   */
  private _getRenderedAttributes() {
    return this.model
      .get("attributes")
      .chain()
      .map(currentAttribute => {
        return {
          view: currentAttribute.haveView(),
          id: currentAttribute.id
        };
      })
      .filter(currentAttribut => {
        return currentAttribut.view.haveView;
      })
      .value();
  }

  /**
   * Get max index of an array
   *
   * @param attributeArray
   * @returns {*}
   */
  private _getMaxIndex(attributeArray) {
    return _.size(
      attributeArray
        .get("content")
        .max(currentAttr => {
          return _.size(currentAttr.get("attributeValue"));
        })
        .get("attributeValue")
    );
  }

  /**
   * Activate constraint on the current document
   * Used on the fetch of a new document
   *
   */
  private _initActivatedConstraint() {
    const currentDocumentProperties = this.getProperties();
    this.activatedConstraint = {};
    _.each(this.options.constraintList, (currentConstraint: any) => {
      if (
        currentConstraint.documentCheck.call(
          $(this.element),
          currentDocumentProperties
        )
      ) {
        this.activatedConstraint[currentConstraint.name] = currentConstraint;
      }
    });
  }

  /**
   * Activate events on the current document
   * Used on the fetch of a new document
   */
  private _initActivatedEventListeners(options) {
    const currentDocumentProperties = this.getProperties();
    options = options || {};
    this.activatedEventListener = {};
    _.each(this.options.eventListener, (currentEvent: any) => {
      if (!_.isFunction(currentEvent.documentCheck)) {
        this.activatedEventListener[currentEvent.name] = currentEvent;
        return;
      }
      if (
        currentEvent.documentCheck.call(
          $(this.element),
          currentDocumentProperties
        )
      ) {
        this.activatedEventListener[currentEvent.name] = currentEvent;
      }
    });
    //Trigger new added ready event
    if (this.initialized.view && options.launchReady) {
      this.triggerControllerEvent("ready", null, currentDocumentProperties);
      _.each(this._getRenderedAttributes(), (currentAttribute: any) => {
        const objectAttribute = this.getAttribute(currentAttribute.id);
        this._triggerAttributeControllerEvent(
          "attributeReady",
          null,
          currentAttribute,
          currentDocumentProperties,
          objectAttribute,
          currentAttribute.view.elements
        );
      });
    }
  }

  /**
   * Add new event and autotrigger already done event for ready
   *
   * @param newEvent
   */
  private _addAndInitNewEvents(newEvent) {
    let currentDocumentProperties;
    let event;
    let uniqueName;
    const $element = $(this.element);
    uniqueName =
      (newEvent.externalEvent ? "external_" : "internal_") + newEvent.name;
    this.options.eventListener[uniqueName] = newEvent;

    if (!this.initialized.model) {
      //early event model is not ready (no trigger, or current register possible)
      return this;
    }
    currentDocumentProperties = this.getProperties();
    // Check if the event is for the current document
    if (
      !_.isFunction(newEvent.documentCheck) ||
      newEvent.documentCheck.call($element, currentDocumentProperties)
    ) {
      this.activatedEventListener[newEvent.name] = newEvent;
      // Check if we need to manually trigger this callback (late registered : only for ready events)
      if (this.initialized.view) {
        if (newEvent.eventType === "ready") {
          event = $.Event(newEvent.eventType);
          event.target = this.element;
          try {
            // add element as function context
            newEvent.eventCallback.call(
              $element,
              event,
              currentDocumentProperties
            );
          } catch (e) {
            console.error(e);
          }
        }
        if (newEvent.eventType === "attributeReady") {
          event = $.Event(newEvent.eventType);
          event.target = this.element;
          _.each(this._getRenderedAttributes(), (currentAttribute: any) => {
            const objectAttribute = this.getAttribute(currentAttribute.id);
            if (
              !_.isFunction(newEvent.attributeCheck) ||
              newEvent.attributeCheck.apply($element, [objectAttribute])
            ) {
              try {
                // add element as function context
                newEvent.eventCallback.call(
                  $element,
                  event,
                  currentDocumentProperties,
                  objectAttribute,
                  currentAttribute.view.elements
                );
              } catch (e) {
                console.error(e);
              }
            }
          });
        }
      }
    }
  }

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
  private _triggerAttributeControllerEvent(
    eventName,
    originalEvent,
    attributeInternalElement,
    ...args
  ) {
    const event: any = $.Event(eventName);
    let externalEventArgument;
    const $element = $(this.element);
    event.target = this.element;
    // internal event trigger
    if (originalEvent && originalEvent.preventDefault) {
      event.originalEvent = originalEvent;
    }
    args.unshift(event);
    _.chain(this.activatedEventListener)
      .filter((currentEvent: any) => {
        // Check by eventType (only call callback with good eventType)
        if (currentEvent.eventType === eventName) {
          //Check with attributeCheck if the function exist
          if (!_.isFunction(currentEvent.attributeCheck)) {
            return true;
          }
          return currentEvent.attributeCheck.apply($element, [
            attributeInternalElement,
            this.getProperties()
          ]);
        }
        return false;
      })
      .each((currentEvent: any) => {
        try {
          currentEvent.eventCallback.apply($element, args);
        } catch (e) {
          // @ts-ignore
          if (window.dcp && window.dcp.logger) {
            // @ts-ignore
            window.dcp.logger(e);
          } else {
            console.error(e);
          }
        }
      });
    externalEventArgument = Array.prototype.slice.call(arguments, 0);
    externalEventArgument.splice(1, 1);
    this.triggerExternalEvent.apply(this, externalEventArgument);
    return !event.isDefaultPrevented();
  }

  /**
   * Trigger a controller event
   * That kind of event are only for this widget
   *
   * @param eventName
   * @param originalEvent
   * @param args
   * @returns {boolean}
   */
  private triggerControllerEvent(eventName, originalEvent, ...args: any[]) {
    const event: JQuery.Event & {
      target: JQuery<DOMReference>;
      originalEvent: JQuery.Event;
    } = $.Event(eventName);
    event.target = this.element;
    if (originalEvent && originalEvent.preventDefault) {
      event.originalEvent = originalEvent;
    }
    // internal event trigger
    args.unshift(event);
    _.chain(this.activatedEventListener)
      .filter((currentEvent: any) => {
        return currentEvent.eventType === eventName;
      })
      .each((currentEvent: any) => {
        try {
          currentEvent.eventCallback.apply($(this.element), args);
        } catch (e) {
          // @ts-ignore
          if (window.dcp.logger) {
            // @ts-ignore
            window.dcp.logger(e);
          } else {
            console.error(e);
          }
        }
      });
    // @ts-ignore
    this.triggerExternalEvent.call(this, ...arguments);
    return !event.isDefaultPrevented();
  }

  /**
   * Trigger event as jQuery standard events (all events are prefixed by document)
   *
   * @param type
   * @param args
   */
  private triggerExternalEvent(type, ...args) {
    const event = $.Event(type);
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
    // TODO Trigger external event
    // this._trigger.apply(this, args);
  }

  /**
   * Check if event name is valid
   *
   * @param eventName string
   * @private
   */
  private checkEventName(eventName) {
    if (
      _.isString(eventName) &&
      (eventName.indexOf("custom:") === 0 ||
        _.find(AnakeenController.SmartElement.EVENTS_LIST, currentEventType => {
          return currentEventType === eventName;
        }))
    ) {
      return true;
    }
    throw new Error(
      "The event type " +
        eventName +
        " is not known. It must be one of " +
      AnakeenController.SmartElement.EVENTS_LIST.sort().join(" ,")
    );
  }

  /**
   * Check if the view is initialized
   *
   * @private
   */
  private checkInitialisedView() {
    if (!this.initialized.view) {
      throw new ErrorModelNonInitialized(
        "The view is not initialized, use fetchDocument to initialise it."
      );
    }
  }

  /**
   * Check if the model is initialized
   *
   * @private
   */
  private checkInitialisedModel() {
    if (!this.initialized.model) {
      throw new ErrorModelNonInitialized();
    }
  }

  private _registerOutputPromise(documentPromise, options) {
    return new Promise((resolve, reject) => {
      documentPromise.then(
        values => {
          if (options && _.isFunction(options.success)) {
            try {
              if (window.console.warn) {
                window.console.warn(
                  'Callback "success" is deprecated use promise instead'
                );
              }
              options.success.call(
                $(this.element),
                values.documentProperties || {},
                this.getProperties()
              );
            } catch (exception) {
              // @ts-ignore
              if (window.dcp.logger) {
                // @ts-ignore
                window.dcp.logger(exception);
              } else {
                console.error(exception);
              }
            }
          }
          resolve({
            element: $(this.element),
            previousDocument: values.documentProperties || {},
            nextDocument: this.getProperties()
          });
        },
        values => {
          const errorArguments = values.arguments;
          let errorMessage = { contentText: "Undefined error" };

          if (values.arguments) {
            try {
              if (
                errorArguments &&
                errorArguments[1] &&
                errorArguments[1].responseJSON
              ) {
                errorMessage = errorArguments[1].responseJSON.messages[0];
              }
            } catch (e) {
              //no error here
            }
            if (
              errorArguments &&
              errorArguments[0] &&
              errorArguments[0].eventPrevented
            ) {
              errorMessage = { contentText: "Event prevented" };
            }
            if (
              errorArguments &&
              errorArguments[0] &&
              errorArguments[0].errorMessage
            ) {
              errorMessage = errorArguments[0].errorMessage;
            }
          }
          if (options && _.isFunction(options.error)) {
            try {
              if (window.console.warn) {
                window.console.warn(
                  'Callback "error" is deprecated use promise instead'
                );
              }
              options.error.call(
                $(this.element),
                values.documentProperties || {},
                null,
                errorMessage
              );
            } catch (exception) {
              // @ts-ignore
              window.dcp.logger(exception);
            }
          }
          reject({
            element: $(this.element),
            previousDocument: values.documentProperties || {},
            nextDocument: null,
            errorMessage: errorMessage
          });
        }
      );
    });
  }

  /***************************************************************************************************************
   * External function
   **************************************************************************************************************/
  /**
   * Reinit the current document (close it and re-open it) : keep the same view, revision, etc...
   *
   * @param values object {"initid" : int, "revision" : int, "viewId" : string, "customClientData" : mixed}
   * @param options object {"success": fct, "error", fct}
   */
  public reinitSmartElement(values, options?) {
    const properties = this.getProperties();
    this.checkInitialisedModel();
    values = values || {};

    //Reinit model with server values
    _.defaults(values, {
      revision: properties.revision,
      viewId: properties.viewId,
      initid: properties.initid
    });

    return this.fetchSmartElement(values, options);
  }

  /**
   * Fetch a new document
   * @param values object {"initid" : int, "revision" : int, "viewId" : string, "customClientData" : mixed}
   * @param options object {"success": fct, "error", fct}
   */
  public fetchSmartElement(values, options) {
    let documentPromise;
    values = _.isUndefined(values) ? {} : values;
    options = options || {};

    if (!_.isObject(values)) {
      throw new Error(
        'Fetch argument must be an object {"initid":, "revision": , "viewId": }'
      );
    }

    if (!values.initid) {
      throw new Error("initid argument is mandatory");
    }

    if (!isNaN(values.initid)) {
      // Convert to numeric initid is possible
      values.initid = parseInt(values.initid);
    }

    // Use default values when fetch another document
    _.defaults(values, { revision: -1, viewId: "!defaultConsultation" });
    _.defaults(options, { force: false });

    _.each(_.pick(values, "initid", "revision", "viewId"), (value, key) => {
      this.options[key] = value;
    });

    if (!this.model) {
      documentPromise = this.initializeSmartElement(
        options,
        values.customClientData
      );
    } else {
      if (values.customClientData) {
        this.model._customClientData = values.customClientData;
      }
      documentPromise = this.model.fetchDocument(this.getModelValue(), options);
    }
    return this._registerOutputPromise(documentPromise, options);
  }

  /**
   * Save the current document
   * Reload the interface in the same mode
   * @param options object {"success": fct, "error", fct, "customClientData" : mixed}
   *
   */
  public saveSmartElement(options) {
    let documentPromise;
    options = options || {};
    this.checkInitialisedModel();
    if (options.customClientData) {
      this.model._customClientData = options.customClientData;
    }
    documentPromise = this.model.saveDocument();
    return this._registerOutputPromise(documentPromise, options);
  }

  /**
   * Change the workflow state of the document
   *
   * @param parameters
   * @param reinitOptions
   * @param options
   */
  public changeStateSmartElement(parameters, reinitOptions, options) {
    let documentPromise;
    this.checkInitialisedModel();
    if (!_.isObject(parameters)) {
      throw new Error(
        'changeStateDocument first argument must be an object {"nextState":, "transition": , "values":, "unattended":, "" }'
      );
    }
    if (
      !_.isString(parameters.nextState) ||
      !_.isString(parameters.transition)
    ) {
      throw new Error("nextState and transition arguments are mandatory");
    }
    documentPromise = this._initAndDisplayTransition(
      parameters.nextState,
      parameters.transition,
      parameters.values || null,
      parameters.unattended || false,
      parameters.transitionElementsCallBack || false,
      reinitOptions
    );
    return this._registerOutputPromise(documentPromise, options);
  }

  /**
   * Delete the current document
   * Reload the interface in the same mode
   * @param options object {"success": fct, "error", fct, "customClientData" : mixed}
   */
  public deleteSmartElement(options) {
    let documentPromise;
    options = options || {};
    this.checkInitialisedModel();
    if (options.customClientData) {
      this.model._customClientData = options.customClientData;
    }
    documentPromise = this.model.deleteDocument();
    return this._registerOutputPromise(documentPromise, options);
  }

  /**
   * Restore the current document
   * Reload the interface in the same mode
   * @param options object {"success": fct, "error", fct, "customClientData" : mixed}
   */
  public restoreSmartElement(options) {
    let documentPromise;
    options = options || {};
    this.checkInitialisedModel();
    if (options.customClientData) {
      this.model._customClientData = options.customClientData;
    }
    documentPromise = this.model.restoreDocument();
    return this._registerOutputPromise(documentPromise, options);
  }

  /**
   * Get a property value
   *
   * @param property
   * @returns {*}
   */
  public getProperty(property) {
    this.checkInitialisedModel();
    if (property === "isModified") {
      return this.model.isModified();
    }
    return this.model.getServerProperties()[property];
  }

  /**
   * Get all the properties
   * @returns {*}
   */
  public getProperties() {
    let properties;
    let ready = true;
    try {
      this.checkInitialisedModel();
    } catch (e) {
      ready = false;
      properties = {
        notLoaded: true
      };
    }
    if (ready) {
      properties = this.model.getServerProperties();
      properties.isModified = this.model.isModified();
      properties.url = window.location.href;
    }

    return properties;
  }

  /**
   * Check if an attribute exist
   *
   * @param attributeId
   * @return {boolean}
   */
  public hasAttribute(attributeId) {
    this.checkInitialisedModel();
    const attribute = this.model.get("attributes").get(attributeId);
    return !!attribute;
  }

  /**
   * Get the attribute interface object
   * Return null if attribute not found
   * @param attributeId
   * @returns AttributeInterface|null
   */
  public getAttribute(attributeId) {
    this.checkInitialisedModel();
    const attributeModel = this._getAttributeModel(attributeId);
    if (!attributeModel) {
      return null;
    }
    return new AttributeInterface(this._getAttributeModel(attributeId));
  }

  /**
   * Get all the attributes of the current document
   *
   * @returns [AttributeInterface]
   */
  public getAttributes() {
    this.checkInitialisedModel();
    return this.model.get("attributes").map(currentAttribute => {
      return new AttributeInterface(currentAttribute);
    });
  }

  /**
   * Check if a menu exist
   *
   * @param menuId
   * @return {boolean}
   */
  public hasMenu(menuId) {
    this.checkInitialisedModel();
    const menu = this._getMenuModel(menuId);
    return !!menu;
  }

  /**
   * Get the menu interface object
   *
   * @param menuId
   * @returns MenuInterface
   */
  public getMenu(menuId) {
    this.checkInitialisedModel();
    const menu = this._getMenuModel(menuId);
    if (!menu) {
      return null;
    }
    return new MenuInterface(menu);
  }

  /**
   * Get all the menu of the current document
   *
   * @returns [MenuInterface]
   */
  public getMenus() {
    this.checkInitialisedModel();
    return this.model.get("menus").map(currentMenu => {
      return new MenuInterface(currentMenu);
    });
  }

  /**
   * Select a tab
   *
   * @param tabId
   * @returns void
   */
  public selectTab(tabId) {
    this.checkInitialisedModel();
    const attributeModel = this._getAttributeModel(tabId);
    if (!attributeModel) {
      throw new Error('The attribute "' + tabId + '" cannot be found.');
    }
    if (attributeModel.get("type") !== "tab") {
      throw new Error('The attribute "' + tabId + '" is not a tab.');
    }

    this.model.trigger("doSelectTab", tabId);
  }

  /**
   * Draw tab content
   *
   * @param tabId
   * @returns void
   */
  public drawTab(tabId) {
    this.checkInitialisedModel();
    const attributeModel = this._getAttributeModel(tabId);
    if (!attributeModel) {
      throw new Error('The attribute "' + tabId + '" cannot be found.');
    }
    if (attributeModel.get("type") !== "tab") {
      throw new Error('The attribute "' + tabId + '" is not a tab.');
    }

    this.model.trigger("doDrawTab", tabId);
  }

  /**
   * Get an attribute value
   *
   * @param attributeId
   * @param type string (current|previous|initial|all) what kind of value (default : current)
   * @returns {*}
   */
  public getValue(attributeId, type) {
    let attribute;
    this.checkInitialisedModel();
    const attributeModel = this._getAttributeModel(attributeId);
    if (!attributeModel) {
      return null;
    }
    attribute = new AttributeInterface(attributeModel);
    return _.clone(attribute.getValue(type));
  }

  /**
   * Get all the values
   *
   * @returns {*|{}}
   */
  public getValues() {
    this.checkInitialisedModel();
    return this.model.getValues();
  }

  /**
   * Get customData from render view model
   * @returns {*}
   */
  public getCustomServerData() {
    this.checkInitialisedModel();
    return this.model.get("customServerData");
  }
  /**
   * Add customData from render view model
   * @returns {*}
   */
  public addCustomClientData(documentCheck, value) {
    this.checkInitialisedModel();
    //First case no data, so documentCheck is data
    if (_.isUndefined(value)) {
      value = documentCheck;
      documentCheck = {};
    }
    //Second case documentCheck is a function and data is object
    if (_.isFunction(documentCheck) && _.isObject(value)) {
      documentCheck = { documentCheck: documentCheck };
    }
    //Third case documentCheck is an object and data is object => check if documentCheck property exist
    if (_.isObject(value) && _.isObject(documentCheck)) {
      documentCheck = _.defaults(documentCheck, {
        documentCheck: () => {
          return true;
        },
        once: true
      });
    } else {
      throw new Error("Constraint must be an value or a function and a value");
    }
    //Register the customClientData
    _.each(value, (currentValue, currentKey) => {
      this._customClientData[currentKey] = {
        value: currentValue,
        documentCheck: documentCheck.documentCheck,
        once: documentCheck.once
      };
    });
  }
  /**
   * Get customData from render view model
   * @returns {*}
   */
  public setCustomClientData(documentCheck, value) {
    console.error("this function (setCustomClientData) is deprecated");
    return this.addCustomClientData(documentCheck, value);
  }
  /**
   * Get customData from render view model
   * @returns {*}
   */
  public getCustomClientData(deleteOnce) {
    const values = {};
    let $element;
    let properties;
    const newCustomData = {};
    this.checkInitialisedModel();
    properties = this.getProperties();
    $element = $(this.element);
    _.each(this._customClientData, (currentCustom: any, key) => {
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
      this._customClientData = newCustomData;
    }
    return values;
  }

  /**
   * Delete a custom data
   * @returns {*}
   */
  public removeCustomClientData(key) {
    if (this._customClientData[key]) {
      delete this._customClientData[key];
    }
    return this;
  }
  /**
   * Set a value
   * Trigger a change event
   *
   * @param attributeId string attribute identifier
   * @param value object { "value" : *, "displayValue" : *}
   * @returns {*}
   */
  public setValue(attributeId, value) {
    this.checkInitialisedModel();
    const attributeModel = this._getAttributeModel(attributeId);
    if (!attributeModel) {
      throw new Error("Unable to find attribute " + attributeId);
    }
    const attributeInterface = new AttributeInterface(attributeModel);
    let index;
    let currentValueLength;
    let i;

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
      const arrayModel = attributeModel.getParent();
      const modifiedColumns = {};
      arrayModel.get("content").each(aModel => {
        const aValue = _.clone(aModel.get("attributeValue"));
        let defaultValue = aModel.get("defaultValue");

        if (!defaultValue) {
          defaultValue = aModel.hasMultipleOption()
            ? []
            : { value: null, displayValue: "" };
        }

        for (i = currentValueLength; i <= index; i++) {
          if (_.isUndefined(aValue[i])) {
            aValue[i] = defaultValue;
            modifiedColumns[aModel.id] = { model: aModel, values: aValue };
          }
        }
      });

      _.each(modifiedColumns, (modData: any) => {
        _.defer(() => {
          modData.model.set("attributeValue", modData.values);
        });
      });

      return;
    }
    return attributeInterface.setValue(value);
  }

  /**
   * Add a row to an array
   *
   * @param attributeId string attribute array
   * @param values object { "attributeId" : { "value" : *, "displayValue" : * }, ...}
   */
  public appendArrayRow(attributeId, values) {
    this.checkInitialisedModel();
    const attribute = this._getAttributeModel(attributeId);

    if (!attribute) {
      throw new Error("Unable to find attribute " + attributeId);
    }

    if (attribute.get("type") !== "array") {
      throw new Error(
        "Attribute " + attributeId + " must be an attribute of type array"
      );
    }
    if (!_.isObject(values)) {
      throw new Error(
        "Values must be an object where each properties is an attribute of the array for " +
          attributeId
      );
    }
    attribute.get("content").each(currentAttribute => {
      let newValue = values[currentAttribute.id];
      const currentValue = currentAttribute.getValue();
      if (_.isUndefined(newValue)) {
        // Set default value if no value defined
        currentAttribute.createIndexedValue(
          currentValue.length,
          false,
          _.isEmpty(values)
        );
      } else {
        newValue = _.defaults(newValue, {
          value: "",
          displayValue: newValue.value
        });
        currentAttribute.addValue(newValue);
      }
    });
  }

  /**
   * Add a row before another row
   *
   * @param attributeId string attribute array
   * @param values object { "attributeId" : { "value" : *, "displayValue" : * }, ...}
   * @param index int index of the row
   */
  public insertBeforeArrayRow(attributeId, values, index) {
    this.checkInitialisedModel();
    const attribute = this._getAttributeModel(attributeId);
    let maxValue;
    if (!attribute) {
      throw new Error("Unable to find attribute " + attributeId);
    }
    if (attribute.get("type") !== "array") {
      throw new Error(
        "Attribute " + attributeId + " must be an attribute of type array"
      );
    }
    if (!_.isObject(values)) {
      throw new Error(
        "Values must be an object where each properties is an attribute of the array for " +
          attributeId
      );
    }
    maxValue = this._getMaxIndex(attribute);
    if (index < 0 || index > maxValue) {
      throw new Error("Index must be between 0 and " + maxValue);
    }
    attribute.get("content").each(currentAttribute => {
      let currentValue = values[currentAttribute.id];
      if (!_.isUndefined(currentValue)) {
        currentValue = _.defaults(currentValue, {
          value: "",
          displayValue: currentValue.value
        });
      } else {
        currentValue = currentAttribute.attributes.defaultValue;
        if (!currentValue) {
          currentValue = { value: "", displayValue: "" };
        }
      }
      currentAttribute.addIndexedValue(currentValue, index);
    });
  }

  /**
   * Remove an array row
   * @param attributeId string attribute array
   * @param index int index of the row
   */
  public removeArrayRow(attributeId, index) {
    this.checkInitialisedModel();
    const attribute = this._getAttributeModel(attributeId);
    let maxIndex;
    if (!attribute) {
      throw new Error("Unable to find attribute " + attributeId);
    }
    if (attribute.get("type") !== "array") {
      throw Error(
        "Attribute " + attributeId + " must be an attribute of type array"
      );
    }
    maxIndex = this._getMaxIndex(attribute) - 1;
    if (index < 0 || index > maxIndex) {
      throw Error(
        "Index must be between 0 and " + maxIndex + " for " + attributeId
      );
    }
    attribute.get("content").each(currentAttribute => {
      currentAttribute.removeIndexValue(index);
    });
    attribute.removeIndexedLine(index);
  }

  /**
   * Add a constraint to the widget
   *
   * @param options object { "name" : string, "documentCheck": function}
   * @param callback function callback called when the event is triggered
   * @returns {*}
   */
  public addConstraint(options, callback) {
    let currentConstraint;
    const currentWidget = this;
    let uniqueName;
    if (_.isUndefined(callback) && _.isFunction(options)) {
      callback = options;
      options = {};
    }
    if (_.isObject(options) && _.isUndefined(callback)) {
      if (!options.name) {
        throw new Error(
          "When a constraint is initiated with a single object, this object needs to have the name property " +
            JSON.stringify(options)
        );
      }
    } else {
      _.defaults(options, {
        documentCheck: () => true,
        attributeCheck: () => true,
        constraintCheck: callback,
        name: _.uniqueId("constraint"),
        externalConstraint: false,
        once: false
      });
    }
    currentConstraint = options;
    if (!_.isFunction(currentConstraint.constraintCheck)) {
      throw new Error("An event need a callback");
    }
    //If constraint is once : wrap it an callback that execute callback and delete it
    if (currentConstraint.once === true) {
      currentConstraint.eventCallback = _.wrap(
        currentConstraint.constraintCheck,
        function documentController_onceWrapper(callback) {
          try {
            // @ts-ignore
            callback.apply(this, _.rest(arguments));
          } catch (e) {
            console.error(e);
          }
          currentWidget.removeConstraint(
            currentConstraint.name,
            currentConstraint.externalConstraint
          );
        }
      );
    }
    uniqueName =
      (currentConstraint.externalConstraint ? "external_" : "internal_") +
      currentConstraint.name;
    this.options.constraintList[uniqueName] = currentConstraint;
    this._initActivatedConstraint();
    return currentConstraint.name;
  }

  /**
   * List the constraint of the widget
   *
   * @returns {*}
   */
  public listConstraints() {
    return this.options.constraintList;
  }

  /**
   * Remove a constraint of the widget
   *
   * @param constraintName
   * @param allKind
   * @returns {*}
   */
  public removeConstraint(constraintName, allKind) {
    const removed = [];
    let newConstraintList;
    let constraintList;
    const testRegExp = new RegExp("\\" + constraintName + "$");
    // jscs:disable disallowImplicitTypeConversion
    allKind = !!allKind;
    // jscs:enable disallowImplicitTypeConversion
    newConstraintList = _.filter(
      this.options.constraintList,
      (currentConstraint: any) => {
        if (
          (allKind || !currentConstraint.externalConstraint) &&
          (currentConstraint.name === constraintName ||
            testRegExp.test(currentConstraint.name))
        ) {
          removed.push(currentConstraint);
          return false;
        }
        return true;
      }
    );
    constraintList = {};
    _.each(newConstraintList, (currentConstraint: any) => {
      const uniqueName =
        (currentConstraint.externalConstraint ? "external_" : "internal_") +
        currentConstraint.name;
      constraintList[uniqueName] = currentConstraint;
    });
    this.options.constraintList = constraintList;
    this._initActivatedConstraint();
    return removed;
  }

  /**
   * Add an event to the widget
   *
   * @param eventType string kind of event
   * @param options object { "name" : string, "documentCheck": function}
   * @param callback function callback called when the event is triggered
   * @returns {*|Window.options.name}
   */
  public addEventListener(eventType, options, callback) {
    let currentEvent;
    //options is not mandatory and the callback can be the second parameters
    if (_.isUndefined(callback) && _.isFunction(options)) {
      callback = options;
      options = {};
    }
    // the first parameters can be the final object (chain removeEvent and addEvent)
    if (
      _.isObject(eventType) &&
      _.isUndefined(options) &&
      _.isUndefined(callback)
    ) {
      currentEvent = eventType;
      if (!currentEvent.name) {
        throw new Error(
          "When an event is initiated with a single object, this object needs to have the name property " +
            JSON.stringify(currentEvent)
        );
      }
    } else {
      currentEvent = _.defaults(options, {
        name: _.uniqueId("event_" + eventType),
        eventType: eventType,
        eventCallback: callback,
        externalEvent: false,
        once: false
      });
    }
    // the eventType must be one the list
    this.checkEventName(currentEvent.eventType);
    // callback is mandatory and must be a function
    if (!_.isFunction(currentEvent.eventCallback)) {
      throw new Error("An event needs a callback that is a function");
    }
    //If event is once : wrap it an callback that execute event and delete it
    if (currentEvent.once === true) {
      currentEvent.eventCallback = _.wrap(
        currentEvent.eventCallback,
        callback => {
          this.removeEventListener(
            currentEvent.name,
            currentEvent.externalEvent
          );
          try {
            // @ts-ignore
            callback.apply(this, _.rest(arguments));
          } catch (e) {
            console.error(e);
          }
        }
      );
    }
    this._addAndInitNewEvents(currentEvent);
    // return the name of the event
    return currentEvent.name;
  }

  /**
   * List of the events of the current widget
   *
   * @returns {*}
   */
  public listEventListeners() {
    return this.options.eventListener;
  }

  /**
   * Remove an event of the current widget
   *
   * @param eventName string can be an event name or a namespace
   * @param allKind remove internal/external events
   * @returns {*}
   */
  public removeEventListener(eventName, allKind) {
    const removed = [];
    const testRegExp = new RegExp("\\" + eventName + "$");
    let newList;
    let eventList;
    // jscs:disable
    allKind = !!allKind;
    // jscs:enable
    newList = _.filter(this.options.eventListener, (currentEvent: any) => {
      if (
        (allKind || !currentEvent.externalEvent) &&
        (currentEvent.name === eventName || testRegExp.test(currentEvent.name))
      ) {
        removed.push(currentEvent);
        return false;
      }
      return true;
    });
    eventList = {};
    _.each(newList, (currentEvent: any) => {
      const uniqueName =
        (currentEvent.externalEvent ? "external_" : "internal_") +
        currentEvent.name;
      eventList[uniqueName] = currentEvent;
    });
    this.options.eventListener = eventList;
    this._initActivatedEventListeners({ launchReady: false });
    return removed;
  }

  /**
   * Trigger an event
   *
   * @param eventName
   */
  public triggerEvent(eventName) {
    const args = _.toArray(arguments);
    this.checkInitialisedModel();
    this.checkEventName(eventName);

    args.splice(1, 0, null); // Add null originalEvent
    // @ts-ignore
    return this.triggerControllerEvent.apply(this, args);
  }

  /**
   * Hide a visible attribute
   *
   * @param attributeId
   */
  public hideAttribute(attributeId) {
    this.checkInitialisedView();
    const attributeModel = this._getAttributeModel(attributeId);
    if (!attributeModel) {
      console.error("Unable find and hide the attribute " + attributeId);
      return;
    }
    attributeModel.trigger("hide");
  }
  /**
   * show a visible attribute (previously hidden)
   *
   * @param attributeId
   */
  public showAttribute(attributeId) {
    this.checkInitialisedView();
    var attributeModel = this._getAttributeModel(attributeId);
    if (!attributeModel) {
      console.error("Unable find and show the attribute " + attributeId);
      return;
    }
    attributeModel.trigger("show");
  }

  /**
   * Display a message to the user
   *
   * @param message
   */
  public showMessage(message) {
    this.checkInitialisedView();
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
  }

  /**
   * Display loading bar
   *
   * @param message
   * @param px
   */
  public maskDocument(message, px) {
    this.$loading.dcpLoading("show");
    if (message) {
      this.$loading.dcpLoading("setTitle", message);
    }
    if (px) {
      this.$loading.dcpLoading("setPercent", px);
    }
  }

  /**
   * Hide loading bar
   */
  public unmaskDocument(force) {
    this.$loading.dcpLoading("hide", force);
  }

  /**
   * Add an error message to an attribute
   *
   * @param attributeId
   * @param message
   * @param index
   */
  public setAttributeErrorMessage(attributeId, message, index) {
    this.checkInitialisedView();
    const attributeModel = this._getAttributeModel(attributeId);
    if (!attributeModel) {
      console.error("Unable find and show the attribute " + attributeId);
      return;
    }
    attributeModel.setErrorMessage(message, index);
  }

  /**
   * Clean the error message of an attribute
   *
   * @param attributeId
   * @param index
   */
  public cleanAttributeErrorMessage(attributeId, index) {
    this.checkInitialisedView();
    const attributeModel = this._getAttributeModel(attributeId);
    if (!attributeModel) {
      console.error("Unable find and show the attribute " + attributeId);
      return;
    }
    attributeModel.setErrorMessage(null, index);
  }

  public injectCSS(cssToInject) {
    this.checkInitialisedView();
    if (!_.isArray(cssToInject) && !_.isString(cssToInject)) {
      throw new Error("The css to inject must be an array string or a string");
    }
    if (_.isString(cssToInject)) {
      cssToInject = [cssToInject];
    }

    this.model.injectCSS(cssToInject);
  }

  public injectJS(jsToInject) {
    this.checkInitialisedView();
    if (!_.isArray(jsToInject) && !_.isString(jsToInject)) {
      throw new Error("The js to inject must be an array string or a string");
    }
    if (_.isString(jsToInject)) {
      jsToInject = [jsToInject];
    }

    return this.model.injectJS(jsToInject);
  }
  /**
   * tryToDestroy the widget
   *
   * @return Promise
   */
  public tryToDestroy() {
    return new Promise((resolve, reject) => {
      const event = { prevent: false };
      if (!this.model) {
        resolve();
        return;
      }
      if (
        this.model &&
        this.model.isModified() &&
        !window.confirm(
          this.model.get("properties").get("title") +
            "\n" +
            i18n.___(
              "The form has been modified without saving, do you want to close it ?",
              "ddui"
            )
        )
      ) {
        reject("Unable to destroy because user refuses it");
        return;
      }
      event.prevent = !this.triggerControllerEvent(
        "beforeClose",
        null,
        this.model.getServerProperties()
      );
      if (event.prevent) {
        reject("Unable to destroy because before close refuses it");
        return;
      }
      resolve();
    });
  }
}
