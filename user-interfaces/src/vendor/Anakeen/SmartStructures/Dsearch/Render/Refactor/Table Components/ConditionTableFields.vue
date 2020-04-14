<template>
  <div class="condition-table-fields" :class="errorClass" ref="fieldsWrapper">
    <div class="condition-table-fields-combobox-wrapper" ref="comboboxWrapper"></div>
  </div>
</template>
<script>
import "@progress/kendo-ui/js/kendo.combobox";
import BaseComponent from "./ConditionTableBaseComponent.vue";
import searchAttributes from "../../searchAttributes";

import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";

export default {
  name: "condition-table-fields",
  extends: BaseComponent,
  mixins: [AnkI18NMixin],
  props: {
    controllerProxy: {
      type: Function,
      default: () => () => {}
    },
    famid: {
      type: Number,
      default: 0
    },
    initValue: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      wf: Object,
      fieldOptions: [],
      comboBox: null,
      tooltip: Object,
      selectedField: Object,
      errorClass: ""
    };
  },
  methods: {
    initData: function() {
      let that = this;
      this.comboBox.select(function(item) {
        return item.id === that.initValue;
      });
    },
    addSelectedItemParentLabel: function(comboBox) {
      comboBox.input
        .parent()
        .find(".condition-table-fields--item-parent")
        .remove();
      const selectedValue = comboBox.value();
      if (selectedValue) {
        const item = this.fieldOptions.find(v => v.id === selectedValue);
        if (item) {
          const parentLabel = item.label_parent;
          comboBox.input.parent().append(`<div class='condition-table-fields--item-parent'>${parentLabel}</div>`);
        }
      }
    },
    buildComboBox: function() {
      let that = this;
      this.comboBox = $(this.$refs.comboboxWrapper)
        .kendoComboBox({
          width: 200,
          placeholder: "Choisir attribut",
          clearButton: false,
          filter: "contains",
          minLength: 0,
          dataValueField: "id",
          dataTextField: "label",
          dataSource: {
            data: this.fieldOptions,
            group: { field: "label_parent" }
          },
          change: function(evt) {
            that.addSelectedItemParentLabel(evt.sender);
            that.onValueChange();
            that.checkValidity();
          }
        })
        .data("kendoComboBox");
      this.initData();
      this.addSelectedItemParentLabel(this.comboBox);
    },
    findIfWorkflow: function(data) {
      const $lastAttribute = data[data.length - 1];
      const $revAttribute = this.controllerProxy("getValue", "se_latest");
      let myObject;
      if ($lastAttribute.type === "wid") {
        data.pop();
        if ($revAttribute.value === "yes") {
          myObject = {
            id: this.wf.id,
            label: this.wf.label[0],
            label_parent: this.wf.label_parent,
            type: "wid",
            methods: this.wf.methods
          };
          data.push(myObject);
        } else if ($revAttribute.value === "no") {
          myObject = {
            id: this.wf.id,
            label: this.wf.label[1],
            label_parent: this.wf.label_parent,
            type: "wid",
            methods: this.wf.methods
          };
          data.push(myObject);
        } else {
          myObject = {
            id: this.wf.id,
            label: this.wf.label[2],
            label_parent: this.wf.label_parent,
            type: "wid",
            methods: this.wf.methods
          };
          data.push(myObject);
        }
        return true;
      }
      return false;
    },
    onValueChange: function() {
      this.selectedField = this.comboBox.dataItem();
      const value = this.selectedField ? this.selectedField.id : "";
      this.$emit("valueChange", {
        column: this.name,
        row: this.row,
        smartFieldId: "se_attrids",
        smartFieldValue: {
          value: value,
          index: this.row
        },
        parentValue: this.selectedField
      });
    },
    checkValidity() {
      let that = this;
      let valid = this.comboBox.selectedIndex !== -1 || this.comboBox.value() === "";
      if (!valid) {
        $(this.$refs.fieldsWrapper)
          .tooltip({
            placement: "bottom",
            html: true,
            animation: false,
            // container: "span.condition-table-fields-combobox-wrapper",
            title: function wAttributeSetErrorTitle() {
              const rawMessage = $("<div/>")
                .text(that.$t("dsearch.Invalid smart field"))
                .html();
              return (
                `<div class=""><span class="btn fa fa-times button-close-error close-tooltip-fields">&nbsp;</span>` +
                rawMessage +
                "</div>"
              );
            },
            trigger: "manual"
          })
          .one("shown.bs.tooltip", function wErrorTooltip() {
            $($(".close-tooltip-fields")[that.row]).on("click", () => {
              $(that.$refs.fieldsWrapper).tooltip("hide");
              that.errorClass = "";
            });
            that.errorClass = "hasError";
          });
        $(this.$refs.fieldsWrapper).tooltip("show");
      } else {
        $(this.$refs.fieldsWrapper).tooltip("hide");
        this.errorClass = "";
      }
      return valid;
    }
  },
  watch: {
    famid: {
      handler: function(newValue) {
        let that = this;
        this.fieldOptions = [];
        searchAttributes(newValue)
          .then(function requestAttributesAttributesReady(data) {
            $.each(data.data, function eachDataAttributesReady(key, val) {
              let myObject = {
                id: val.id,
                label: val.label,
                label_parent: val.parent.label,
                type: val.type,
                methods: val.methods
              };
              if (val.type !== "array") {
                that.fieldOptions.push(myObject);
              }
            });

            if (that.fieldOptions[that.fieldOptions.length - 1].type === "wid") {
              that.wf = that.fieldOptions[that.fieldOptions.length - 1];
            } else {
              that.wf = null;
            }
            that.findIfWorkflow(that.fieldOptions);
          })
          .then(function doneAttributesReady() {
            if (!that.comboBox) {
              that.buildComboBox();
            }
            const dataSource = new kendo.data.DataSource({
              data: that.fieldOptions,
              group: { field: "label_parent" }
            });
            that.comboBox.setDataSource(dataSource);
            if (that.checkValidity()) {
              that.onValueChange();
            }
          });
      },
      immediate: true
    }
  }
};
</script>
<style lang="scss">
.condition-table-fields-combobox-wrapper {
  width: 100%;
  .condition-table-fields--item-parent {
    // Set same style as kendo group combobox label
    color: #f1f1f1;
    background: #353535;
    position: absolute;
    top: 0;
    right: 0;
    padding: 0 0.5em;
    font-size: 0.714rem;
    line-height: 1rem;
    text-transform: uppercase;
    &::before {
      display: block;
      content: " ";
      border-width: 0.5rem;
      border-style: solid;
      position: absolute;
      left: -1rem;
      bottom: 0;
      border-color: #353535 #353535 transparent transparent;
    }
  }
}

.condition-table-fields.hasError {
  outline: solid 2px #ff542c;
}
</style>
