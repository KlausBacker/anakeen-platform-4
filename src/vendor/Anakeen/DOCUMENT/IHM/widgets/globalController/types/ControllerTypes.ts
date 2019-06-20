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
      noRouter: boolean;
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
  }

  export namespace BusEvents {
    export type ListenableEventCallable = (...args: any[]) => void;

    export type ListenableEvents = {
      [key: string]: Array<ListenableEventCallable>
    }

    export class Listenable {
      private static _events: ListenableEvents;

      constructor() {
        if (!Listenable._events) {
          Listenable._events = {};
        }
      }

      public on(eventName: string, callback: ListenableEventCallable) {

      }

      public off(eventName, callback?: ListenableEventCallable) {

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
  }
}
