<template>
  <input type="checkbox" class="condition-table-rightp" :class="name" ref="rightPWrapper" />
</template>
<script>
import "@progress/kendo-ui/js/kendo.switch";
import BaseComponent from "./ConditionTableBaseComponent.vue";

export default {
  name: "condition-table-rightp",
  extends: BaseComponent,
  props: {
    initValue: ""
  },
  data() {
    return {
      switch: null
    };
  },
  mounted() {
    this.switch = $(this.$refs.rightPWrapper)
      .kendoSwitch({
        messages: {
          checked: ")",
          unchecked: ""
        },
        change: this.onValueChange
      })
      .data("kendoSwitch");
    this.initData();
  },
  methods: {
    onValueChange() {
      const checked = this.switch.value();
      let result = { value: "no", displayValue: null, index: this.row };
      if (checked) {
        result.value = "yes";
        result.displayValue = ")";
      }
      this.$emit("valueChange", {
        column: this.name,
        row: this.row,
        smartFieldId: "se_rightp",
        smartFieldValue: result,
        parentValue: result
      });
    },
    initData() {
      if (this.initValue && this.initValue === "yes") {
        this.switch.check(this.initValue);
      }
    }
  }
};
</script>
<style></style>
