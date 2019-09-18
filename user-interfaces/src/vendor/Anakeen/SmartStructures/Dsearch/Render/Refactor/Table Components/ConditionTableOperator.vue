<template>
  <div class="condition-table-operator" :class="name" v-show="isNotFirstRow">
    <div class="condition-table-operator-switch" ref="operatorWrapper"></div>
  </div>
</template>
<script>
import "@progress/kendo-ui/js/kendo.dropdownlist";
import BaseComponent from "./ConditionTableBaseComponent.vue";

export default {
  name: "condition-table-operator",
  extends: BaseComponent,
  props: {
    initValue: ""
  },
  data() {
    return {
      dropdownList: null,
      data: [{ value: "and", displayValue: "et" }, { value: "or", displayValue: "ou" }]
    };
  },
  computed: {
    isNotFirstRow: function() {
      return this.row > 0;
    }
  },
  mounted() {
    this.dropdownList = $(this.$refs.operatorWrapper)
      .kendoDropDownList({
        dataTextField: "displayValue",
        dataValueField: "value",
        dataSource: this.data,
        change: this.onValueChange
      })
      .data("kendoDropDownList");
    this.initData();
  },
  methods: {
    onValueChange() {
      const value = this.dropdownList.dataItem().value;
      const displayValue = this.dropdownList.dataItem().displayValue;
      this.$emit("valueChange", {
        column: this.name,
        row: this.row,
        smartFieldId: "se_ols",
        smartFieldValue: {
          value: value,
          displayValue: displayValue,
          index: this.row
        },
        parentValue: value
      });
    },
    initData() {
      if (this.initValue) {
        let that = this;
        this.dropdownList.select(function(item) {
          return (item.value = that.initValue);
        });
      } else {
        this.dropdownList.select(0);
      }
    }
  }
};
</script>
