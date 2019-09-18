<template>
  <div class="condition-table-keywords-time-group">
    <div v-show="isTextBox" class="condition-table-keywords-time-textbox">
      <input
        type="text"
        class="condition-table-keywords-time-textbox k-textbox"
        ref="keywordsTimeTextBoxWrapper"
        @change="onInputChange"
      />
    </div>
    <div v-show="!isTextBox" class="condition-table-keywords-time">
      <input class="condition-table-keywords-time-wrapper" ref="keywordsTimeWrapper" />
    </div>
  </div>
</template>
<script>
import "@progress/kendo-ui/js/kendo.timepicker";

export default {
  name: "condition-table-keywords-time",
  props: {
    operator: "",
    initValue: ""
  },
  data() {
    return {
      timePicker: null,
      textBoxOperators: ["~*", "!~*"]
    };
  },
  computed: {
    isTextBox() {
      return this.textBoxOperators.indexOf(this.operator) !== -1;
    }
  },
  methods: {
    onInputChange(event) {
      let value = event.target.value;
      this.$emit("keysChange", {
        smartFieldValue: value,
        parentValue: value
      });
    },
    onTimeChange() {
      let timeDate = this.timePicker.value();
      let time = "";
      if (timeDate) {
        time = this.searchPadNumber(timeDate.getHours()) + ":" + this.searchPadNumber(timeDate.getMinutes());
      }
      this.$emit("keysChange", {
        smartFieldValue: time,
        parentValue: time
      });
    },
    searchPadNumber(number) {
      let result = number;
      if (number < 10) {
        result = "0" + number;
      }
      return result;
    },
    initData() {
      if (this.initValue) {
        if (this.isTextBox) {
          $(this.$refs.keywordsTimeTextBoxWrapper).val(this.initValue);
        } else {
          this.timePicker.value(this.initValue);
        }
      }
    },
    clearData() {
      this.timePicker.value("");
      $(this.$refs.keywordsTimeTextBoxWrapper).val("");
    }
  },
  mounted() {
    this.timePicker = $(this.$refs.keywordsTimeWrapper)
      .kendoTimePicker({
        timeDataFormat: ["HH:mm", "HH:mm:ss"],
        format: null, // standard format depends of the user's langage
        change: this.onTimeChange
      })
      .data("kendoTimePicker");
    this.initData();
  }
};
</script>
<style>
.condition-table-keywords-time {
  width: 100%;
}

.condition-table-keywords-time-wrapper {
  width: 100%;
}

.condition-table-keywords-time-wrapper.k-input {
  margin-left: 1.5rem;
}

.condition-table-keywords-time-textbox.k-textbox {
  width: 100%;
}
</style>
