export module AnakeenController {
  export namespace Types {
    export type DOMReference = Element | HTMLElement | JQuery | JQuery.Selector;

    export type ViewData = {
      initid: String | Number;
      viewId: String;
      revision: Number;
    };

    export type ControllerOptions = ViewData & {
      eventPrefix: string;
      router: { noRouter: boolean } | false;
      constraintList: any;
      eventListener: any;
      activatedConstraint: {};
      activatedEventListener: {};
      _initializedModel: boolean;
      _initializedView: boolean;
      customClientData: {};
    };

    export type SmartElementProperties = ViewData & {
      renderMode: "create" | "edit" | "view";
    };

    export type ControllerUID = string;
  }
  export namespace BusEvents {
    export type ListenableEventCallableArgs = any[];

    export type ListenableEventCallable = (
      ...args: ListenableEventCallableArgs
    ) => void;

    export type ListenableEventOptions = {
      callback: ListenableEventCallable;
    };

    export type ListenableEvent = ListenableEventOptions;

    export type ListenableEvents = {
      [key: string]: Array<ListenableEvent>;
    };

    export class Listenable {
      private _events: ListenableEvents;

      constructor() {
        this._events = {};
      }

      public getEventsList(): ListenableEvents {
        return this._events;
      }

      public on(
        eventName: string,
        eventCb: ListenableEventCallable | ListenableEvent
      ) {
        if (eventCb) {
          this._events[eventName] = this._events[eventName] || [];
          this._events[eventName].push(Listenable._getEventCallback(eventCb));
        }
      }

      public once(
        eventName: string,
        eventCb: ListenableEventCallable | ListenableEvent
      ) {
        const wrapperCallback = (...args: ListenableEventCallableArgs) => {
          const originalCb = Listenable._getEventCallback(eventCb);
          originalCb.callback(...args);
          this.off(eventName, wrapperCallback);
        };
        this.on(eventName, wrapperCallback);
      }

      public off(eventName, callback?: ListenableEventCallable): Array<ListenableEvent> {
        if (!this._events[eventName]) {
          return;
        }
        if (callback) {
          const eventCb = Listenable._getEventCallback(callback);
          const findIndex = (items, cb) => {
            let i = 0;
            while (i < items.length) {
              if (cb(items[i])) {
                return i;
              }
              i++;
            }
            return -1;
          };
          const index = findIndex(
            this._events[eventName],
            e => e.callback === eventCb.callback
          );
          if (index > -1) {
            return this._events[eventName].splice(index, 1);
          }
        } else {
          const events = this._events[eventName];
          delete this._events[eventName];
          return events;
        }
      }

      public emit(eventName, ...args: ListenableEventCallableArgs) {
        if (!this._events[eventName]) {
          return;
        }
        this._events[eventName].forEach(cb => {
          cb.callback(...args);
        });
      }

      private static _getEventCallback(
        eventCb: ListenableEventCallable | ListenableEvent
      ): ListenableEvent {
        if (eventCb) {
          if (typeof eventCb === "function") {
            return {
              callback: eventCb
            };
          } else if (typeof eventCb === "object" && eventCb.callback) {
            return eventCb;
          }
        }
        return null;
      }
    }
  }

  export namespace SmartElement {
    export const EVENTS_LIST = [
      "beforeRender",
      "ready",
      "change",
      "displayMessage",
      "displayError",
      "validate",
      "attributeBeforeRender",
      "attributeReady",
      "attributeHelperSearch",
      "attributeHelperResponse",
      "attributeHelperSelect",
      "attributeArrayChange",
      "actionClick",
      "attributeAnchorClick",
      "beforeClose",
      "close",
      "beforeSave",
      "afterSave",
      "attributeDownloadFile",
      "attributeUploadFile",
      "attributeUploadFileDone",
      "beforeDelete",
      "afterDelete",
      "beforeRestore",
      "afterRestore",
      "failTransition",
      "successTransition",
      "attributeBeforeTabSelect",
      "attributeAfterTabSelect",
      "attributeTabChange",
      "beforeDisplayTransition",
      "afterDisplayTransition",
      "beforeTransition",
      "beforeTransitionClose",
      "destroy",
      "attributeCreateDialogDocumentBeforeSetFormValues",
      "attributeCreateDialogDocumentBeforeSetTargetValue",
      "attributeCreateDialogDocumentReady",
      "attributeCreateDialogDocumentBeforeClose",
      "attributeCreateDialogDocumentBeforeDestroy"
    ];

    export interface ISmartElementAPI {
      /**
       * Reinit the current document (close it and re-open it) : keep the same view, revision, etc...
       *
       * @param values object {"initid" : int, "revision" : int, "viewId" : string, "customClientData" : mixed}
       * @param options object {"success": fct, "error", fct}
       */
      reinitSmartElement(values, options?);

      /**
       * Fetch a new document
       * @param values object {"initid" : int, "revision" : int, "viewId" : string, "customClientData" : mixed}
       * @param options object {"success": fct, "error", fct}
       */
      fetchSmartElement(values, options);

      /**
       * Save the current document
       * Reload the interface in the same mode
       * @param options object {"success": fct, "error", fct, "customClientData" : mixed}
       *
       */
      saveSmartElement(options);

      /**
       * Change the workflow state of the document
       *
       * @param parameters
       * @param reinitOptions
       * @param options
       */
      changeStateSmartElement(parameters, reinitOptions, options);

      /**
       * Delete the current document
       * Reload the interface in the same mode
       * @param options object {"success": fct, "error", fct, "customClientData" : mixed}
       */
      deleteSmartElement(options);

      /**
       * Restore the current document
       * Reload the interface in the same mode
       * @param options object {"success": fct, "error", fct, "customClientData" : mixed}
       */
      restoreSmartElement(options);

      /**
       * Get a property value
       *
       * @param property
       * @returns {*}
       */
      getProperty(property);

      /**
       * Get all the properties
       * @returns {*}
       */
      getProperties();

      /**
       * Check if an attribute exist
       *
       * @param attributeId
       * @return {boolean}
       */
      hasAttribute(attributeId);

      /**
       * Get the attribute interface object
       * Return null if attribute not found
       * @param attributeId
       * @returns AttributeInterface|null
       */
      getAttribute(attributeId);

      /**
       * Get all the attributes of the current document
       *
       * @returns [AttributeInterface]
       */
      getAttributes();

      /**
       * Check if a menu exist
       *
       * @param menuId
       * @return {boolean}
       */
      hasMenu(menuId);

      /**
       * Get the menu interface object
       *
       * @param menuId
       * @returns MenuInterface
       */
      getMenu(menuId);

      /**
       * Get all the menu of the current document
       *
       * @returns [MenuInterface]
       */
      getMenus();

      /**
       * Select a tab
       *
       * @param tabId
       * @returns void
       */
      selectTab(tabId);

      /**
       * Draw tab content
       *
       * @param tabId
       * @returns void
       */
      drawTab(tabId);

      /**
       * Get an attribute value
       *
       * @param attributeId
       * @param type string (current|previous|initial|all) what kind of value (default : current)
       * @returns {*}
       */
      getValue(attributeId, type);

      /**
       * Get all the values
       *
       * @returns {*|{}}
       */
      getValues();

      /**
       * Get customData from render view model
       * @returns {*}
       */
      getCustomServerData();

      /**
       * Add customData from render view model
       * @returns {*}
       */
      addCustomClientData(documentCheck, value);

      /**
       * Get customData from render view model
       * @returns {*}
       */
      setCustomClientData(documentCheck, value);

      /**
       * Get customData from render view model
       * @returns {*}
       */
      getCustomClientData(deleteOnce);

      /**
       * Delete a custom data
       * @returns {*}
       */
      removeCustomClientData(key);

      /**
       * Set a value
       * Trigger a change event
       *
       * @param attributeId string attribute identifier
       * @param value object { "value" : *, "displayValue" : *}
       * @returns {*}
       */
      setValue(attributeId, value);

      /**
       * Add a row to an array
       *
       * @param attributeId string attribute array
       * @param values object { "attributeId" : { "value" : *, "displayValue" : * }, ...}
       */
      appendArrayRow(attributeId, values);

      /**
       * Add a row before another row
       *
       * @param attributeId string attribute array
       * @param values object { "attributeId" : { "value" : *, "displayValue" : * }, ...}
       * @param index int index of the row
       */
      insertBeforeArrayRow(attributeId, values, index);

      /**
       * Remove an array row
       * @param attributeId string attribute array
       * @param index int index of the row
       */
      removeArrayRow(attributeId, index);

      /**
       * Add a constraint to the widget
       *
       * @param options object { "name" : string, "documentCheck": function}
       * @param callback function callback called when the event is triggered
       * @returns {*}
       */
      addConstraint(options, callback);

      /**
       * List the constraint of the widget
       *
       * @returns {*}
       */
      listConstraints();

      /**
       * Remove a constraint of the widget
       *
       * @param constraintName
       * @param allKind
       * @returns {*}
       */
      removeConstraint(constraintName, allKind);

      /**
       * Add an event to the widget
       *
       * @param eventType string kind of event
       * @param options object { "name" : string, "documentCheck": function}
       * @param callback function callback called when the event is triggered
       * @returns {*|Window.options.name}
       */
      addEventListener(eventType, options, callback);

      /**
       * List of the events of the current widget
       *
       * @returns {*}
       */
      listEventListeners();

      /**
       * Remove an event of the current widget
       *
       * @param eventName string can be an event name or a namespace
       * @param allKind remove internal/external events
       * @returns {*}
       */
      removeEventListener(eventName, allKind);

      /**
       * Trigger an event
       *
       * @param eventName
       */
      triggerEvent(eventName);

      /**
       * Hide a visible attribute
       *
       * @param attributeId
       */
      hideAttribute(attributeId);

      /**
       * show a visible attribute (previously hidden)
       *
       * @param attributeId
       */
      showAttribute(attributeId);

      /**
       * Display a message to the user
       *
       * @param message
       */
      showMessage(message);

      /**
       * Display loading bar
       *
       * @param message
       * @param px
       */
      maskDocument(message, px);

      /**
       * Hide loading bar
       */
      unmaskDocument(force);

      /**
       * Add an error message to an attribute
       *
       * @param attributeId
       * @param message
       * @param index
       */
      setAttributeErrorMessage(attributeId, message, index);

      /**
       * Clean the error message of an attribute
       *
       * @param attributeId
       * @param index
       */
      cleanAttributeErrorMessage(attributeId, index);

      injectCSS(cssToInject);

      injectJS(jsToInject);

      /**
       * tryToDestroy the widget
       *
       * @return Promise
       */
      tryToDestroy();
    }
  }
}
