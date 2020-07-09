import AttributeStructuralModel from "./mAttributeStructural";

export default AttributeStructuralModel.extend({
  typeModel: "ddui:attributeArray",
  removeIndexedLine: function mAttributeArrayRemoveIndexLine(index) {
    this.trigger("removeWidgetLine", { index: index }, { silent: true });
    this.trigger("array", "remove", this, index);
  },

  addIndexedLine: function mAttributeArrayaddIndexedLine(index) {
    this.trigger("addWidgetLine", { index: index });
    this.trigger("array", "add", this, index);
  }
});
