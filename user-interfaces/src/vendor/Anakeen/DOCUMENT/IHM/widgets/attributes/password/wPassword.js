import $ from "jquery";
import "../text/wText";

$.widget("dcp.dcpPassword", $.dcp.dcpText, {
  options: {
    type: "password",

    renderOptions: {
      hideValue: "*****"
    },
    labels: {}
  },

  _initDom: function wasswordInitDom() {
    if (this.options.attributeValue.value) {
      this.options.attributeValue.displayValue = this.options.renderOptions.hideValue;
    }

    this._super();
    if (this.getMode() === "write") {
      this._getFocusInput().attr("type", "password");
    }
  },
  /**
   * Hide password to displayValue
   * @param value
   */
  setValue: function wpasswordSetValue(value) {
    if (value.value) {
      value.displayValue = this.options.renderOptions.hideValue;
    }

    this._super(value);
  },
  getType: function wIntGetType() {
    return "password";
  }
});
