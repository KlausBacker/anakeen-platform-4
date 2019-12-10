/* eslint-disable no-redeclare,no-unused-vars */
/* tslint:disable:no-namespace interface-name */
declare namespace kendo {
  export const jQuery: JQueryStatic;
}

declare namespace kendo.ui {
  class KendoPopup extends kendo.ui.Popup {
    public toggle(): void;
  }
}

interface JQuery {
  data(key: "kendoPopup"): kendo.ui.KendoPopup;
}
