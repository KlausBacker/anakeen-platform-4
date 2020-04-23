<template>
  <div class="condition-table-keywords-time-group">
    <div v-show="isTextBox" class="condition-table-keywords-time-textbox">
      <input
        ref="keywordsTimeTextBoxWrapper"
        type="text"
        class="condition-table-keywords-time-textbox k-textbox"
        @change="onInputChange"
      />
    </div>
    <div v-show="!isTextBox" class="condition-table-keywords-time-timepicker">
      <div v-show="timeMode" class="condition-table-keywords-time-time">
        <input ref="keywordsTimeWrapper" class="condition-table-keywords-time-timepicker" />
      </div>
      <div v-show="!timeMode" class="condition-table-keywords-time-combobox">
        <div ref="keywordsTimeFunctionWrapper" class="condition-table-keywords-time-function" />
      </div>
      <button ref="funcButton" class="condition-table-keywords-time-funcBtn" @click="onFuncButtonClick">
        Σ
      </button>
    </div>
  </div>
</template>
<script>
import "@progress/kendo-ui/js/kendo.timepicker";
import $ from "jquery";

export default {
  name: "condition-table-keywords-time",
  props: {
    operator: "",
    methods: null,
    initValue: ""
  },
  data() {
    return {
      timePicker: null,
      funcButton: null,
      methodsComboBox: null,
      timeMode: true,
      textBoxOperators: ["~*", "!~*"]
    };
  },
  computed: {
    isTextBox() {
      return this.textBoxOperators.indexOf(this.operator) !== -1;
    }
  },
  watch: {
    methods: function(newValue) {
      if (this.methodsComboBox) {
        this.methodsComboBox.setDataSource(newValue);
      }
    }
  },
  mounted() {
    this.timePicker = $(this.$refs.keywordsTimeWrapper)
      .kendoTimePicker({
        parseFormats: ["HH:mm"],
        format: "h:mm tt",
        /* trigger a fonction that change the value of the date from the displayValue according to ISO 8601 */
        change: () => this.onTimeChange()
      })
      .data("kendoTimePicker");
    this.methodsComboBox = $(this.$refs.keywordsTimeFunctionWrapper)
      .kendoComboBox({
        width: 200,
        filter: "contains",
        clearButton: false,
        minLength: 0,
        dataValueField: "method",
        dataTextField: "label",
        template: "#: label #",
        change: () => this.onFuncChange(),
        dataSource: this.methods
      })
      .data("kendoComboBox");
    this.funcButton = $(this.$refs.funcButton)
      .kendoButton()
      .data("kendoButton");
    this.initData();
  },
  methods: {
    isValid() {
      let valid;
      if (this.isTextBox) {
        valid = this.$refs.keywordsTimeTextBoxWrapper.value !== "";
      } else {
        if (this.timeMode) {
          valid = !!this.timePicker.value();
        } else {
          valid = !!this.methodsComboBox.value();
        }
      }
      return valid;
    },
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
    onFuncChange() {
      const value = this.methodsComboBox.value();
      this.$emit("keysChange", {
        smartFieldValue: value,
        parentValue: value
      });
    },
    searchPadNumber(number) {
      let result = number;
      if (number < 10) {
        result = "0" + number;
      }
      return result;
    },
    onFuncButtonClick() {
      this.timeMode = !this.timeMode;
      this.timeMode ? this.onTimeChange() : this.onFuncChange();
      $(this.$refs.funcButton).toggleClass("func-button-clicked");
    },
    initData() {
      if (this.initValue) {
        if (this.isTextBox) {
          $(this.$refs.keywordsTimeTextBoxWrapper).val(this.initValue);
        } else {
          let methodInitValue;
          for (let prop in this.methods) {
            if (Object.prototype.hasOwnProperty.call(this.methods, prop)) {
              const propMethodValue = this.methods[prop].method;
              if (propMethodValue === this.initValue) {
                methodInitValue = propMethodValue;
              }
            }
          }
          if (methodInitValue) {
            this.timeMode = false;
            this.methodsComboBox.select(function(item) {
              return item.method === methodInitValue;
            });
          } else {
            this.timePicker.value(this.initValue);
          }
        }
      }
    },
    clearData() {
      this.timePicker.value("");
      this.methodsComboBox.value("");
      $(this.$refs.keywordsTimeTextBoxWrapper).val("");
    }
  }
};
</script>
<style>
.condition-table-keywords-time-combobox {
  width: 100%;
}

.condition-table-keywords-time-function {
  width: 100%;
}

.condition-table-keywords-time-group {
  width: 100%;
}

.condition-table-keywords-time-timepicker {
  display: flex;
  width: 100%;
}

.condition-table-keywords-time-time {
  width: 100%;
}

.condition-table-keywords-time-textbox {
  width: 100%;
}
.func-button-clicked {
  background-color: #157efb;
  color: white;
}
</style>
