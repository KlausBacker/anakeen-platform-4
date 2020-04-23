<template>
  <div class="condition-table-functions" :class="name" v-show="field">
    <div class="condition-table-functions-dropdown-wrapper" ref="functionsWrapper"></div>
  </div>
</template>
<script>
import "@progress/kendo-ui/js/kendo.dropdownlist";
import BaseComponent from "./ConditionTableBaseComponent.vue";
import $ from "jquery";

export default {
  name: "condition-table-functions",
  extends: BaseComponent,
  props: {
    field: Object,
    initValue: String
  },
  data() {
    return {
      operators: [],
      allOperators: [],
      dropdownList: null
    };
  },
  methods: {
    onValueChange: function() {
      const opValue = this.dropdownList.value() ? this.dropdownList.value() : this.initValue;
      this.$emit("valueChange", {
        column: this.name,
        row: this.row,
        smartFieldId: "se_funcs",
        smartFieldValue: {
          value: opValue,
          index: this.row
        },
        parentValue: opValue
      });
    },
    selectInitValue() {
      let that = this;
      if (this.initValue) {
        this.dropdownList.select(function(item) {
          return item.opId === that.initValue;
        });
      } else {
        this.dropdownList.select(0);
      }
      this.onValueChange();
    },
    buildDropdownList: function() {
      if (this.$refs.functionsWrapper) {
        this.dropdownList = $(this.$refs.functionsWrapper)
          .kendoDropDownList({
            dataSource: this.operators,
            dataTextField: "opTitle",
            dataValueField: "opId",
            template: '<span class="k-state-default">#= opTitle #</span>',
            valueTemplate: "<span> #= opTitle# </span>",
            index: 0,
            change: this.onValueChange
          })
          .data("kendoDropDownList");
        this.selectInitValue();
      }
    },
    buildComponent() {
      let that = this;
      if (this.field) {
        $.each(that.allOperators, function eachDataInitOperators(key, value) {
          if (value.id !== "><") {
            let myObject;
            const type = that.field.type;
            if (value.compatibleTypes === null) {
              myObject = {
                opId: value.id,
                opTitle: value.title
              };
              if (value.typedTitle[type] !== undefined) {
                myObject.opTitle = value.typedTitle[type];
              }
              that.operators.push(myObject);
            } else if (
              //check if the type is compatible with the operator
              value.compatibleTypes.indexOf(type) !== -1
            ) {
              myObject = {
                opId: value.id,
                opTitle: value.title
              };
              if (value.typedTitle[type] !== undefined) {
                myObject.opTitle = value.typedTitle[type];
              }
              that.operators.push(myObject);
            }
          }
        });
      }
      if (this.dropdownList) {
        this.dropdownList.setDataSource(this.operators);
        this.dropdownList.select(0);
        this.onValueChange();
      } else {
        this.buildDropdownList();
      }
    }
  },
  watch: {
    operatorsList: {
      immediate: true,
      handler: function(newValue) {
        if (newValue && Array.isArray(newValue)) {
          newValue.forEach(value => {
            this.allOperators.push(value);
          });
        }
        if (this.field) {
          this.buildComponent();
        }
      }
    },
    field: function(newValue) {
      if (newValue && this.allOperators.length) {
        this.operators = [];
        this.$nextTick(this.buildComponent);
      }
    }
  }
};
</script>
<style>
.condition-table-functions-dropdown-wrapper {
  width: 100%;
}
</style>
