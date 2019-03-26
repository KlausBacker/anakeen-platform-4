declare namespace kendo {
  export const jQuery: JQueryStatic
}

declare namespace kendo.ui {
  class KendoPopup extends kendo.ui.Popup {
      toggle(): void;
  }
}

interface JQuery {
  data(key: "kendoPopup"): kendo.ui.KendoPopup;
}