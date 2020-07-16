<template>
  <div class="input-group margin-bottom-sm">
    <input
      ref="keysInput"
      type="text"
      class="condition-table-keywords-default k-textbox"
      name="se_keys"
      @change="onInputChange"
    />
  </div>
</template>
<script>
import $ from "jquery";

export default {
  name: "condition-table-keywords-default",
  props: {
    initValue: ""
  },
  mounted() {
    this.initData();
  },
  methods: {
    isValid() {
      if (this.operator === "is null" || this.operator === "is not null") {
        return true;
      }
      return this.$refs.keysInput.value !== "";
    },
    onInputChange(event) {
      let value = event.target.value;
      this.$emit("keysChange", {
        smartFieldValue: value,
        parentValue: value
      });
    },
    initData() {
      if (this.initValue) {
        $(this.$refs.keysInput).val(this.initValue);
      }
    },
    clearData() {
      $(this.$refs.keysInput).val("");
    }
  }
};
</script>
<style>
.condition-table-keywords-default {
  width: 100%;
}
</style>
