<template>
  <div class="condition-table-keywords-date-group">
    <div v-show="isTextBox" class="condition-table-keywords-date-textbox">
      <input
        ref="keywordsDateTextBoxWrapper"
        type="text"
        class="condition-table-keywords-date-textbox k-textbox"
        @change="onInputChange"
      />
    </div>
    <div v-show="!isTextBox" class="condition-table-keywords-date-datepicker">
      <div v-show="dateMode" class="condition-table-keywords-date-mode">
        <input ref="keywordsDateWrapper" class="condition-table-keywords-date-picker" />
      </div>
      <div v-show="!dateMode" class="condition-table-keywords-date-datepicker">
        <div ref="keywordsDateFunctionWrapper" class="condition-table-keywords-date-function"></div>
      </div>
      <button ref="funcButton" class="condition-table-keywords-date-funcBtn" @click="onFuncButtonClick">Σ</button>
    </div>
  </div>
</template>
<script>
import "@progress/kendo-ui/js/kendo.datepicker";
import "@progress/kendo-ui/js/kendo.combobox";
import "@progress/kendo-ui/js/kendo.button";
import $ from "jquery";

export default {
  name: "ConditionTableKeywordsDate",
  props: {
    operator: "",
    methods: null,
    initValue: ""
  },
  data() {
    return {
      datePicker: null,
      funcButton: null,
      methodsComboBox: null,
      dateMode: true,
      textBoxOperators: ["~*", "!~*"]
    };
  },
  computed: {
    isTextBox() {
      return this.textBoxOperators.indexOf(this.operator) !== -1;
    }
  },
  watch: {
    dateMode(newValue) {
      $(this.$refs.funcButton).toggleClass("func-button-clicked", !newValue);
    }
  },
  mounted() {
    this.funcButton = $(this.$refs.funcButton)
      .kendoButton()
      .data("kendoButton");
    this.datePicker = $(this.$refs.keywordsDateWrapper)
      .kendoDatePicker({
        parseFormats: ["yyyy-MM-dd"],
        format: null, // standard format depends of the user's langage
        /* trigger a fonction that change the value of the date from the displayValue according to ISO 8601 */
        change: () => this.onDateChange()
      })
      .data("kendoDatePicker");
    this.methodsComboBox = $(this.$refs.keywordsDateFunctionWrapper)
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
    this.initData();
  },
  methods: {
    isValid() {
      if (this.operator === "is null" || this.operator === "is not null") {
        return true;
      }

      let valid;
      if (this.isTextBox) {
        valid = this.$refs.keywordsDateTextBoxWrapper.value !== "";
      } else {
        if (this.dateMode) {
          valid = !!this.datePicker.value();
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
    onDateChange() {
      let date = this.datePicker.value();
      let formattedValue = "";
      if (date) {
        let jour;
        if (date.getDate() / 10 < 1) {
          jour = "0" + date.getDate();
        } else jour = date.getDate();
        let mois;
        if ((date.getMonth() + 1) / 10 < 1) {
          mois = date.getMonth() + 1;
          mois = "0" + mois;
        } else mois = date.getMonth() + 1;
        formattedValue = date.getFullYear() + "-" + mois + "-" + jour;
      }
      this.$emit("keysChange", {
        smartFieldValue: formattedValue,
        parentValue: formattedValue
      });
    },
    onFuncChange() {
      let value = this.methodsComboBox.value();
      this.$emit("keysChange", {
        smartFieldValue: value,
        parentValue: value
      });
    },
    onFuncButtonClick() {
      this.dateMode = !this.dateMode;
      this.dateMode ? this.onDateChange() : this.onFuncChange();
    },
    initData() {
      if (this.initValue) {
        if (this.isTextBox) {
          $(this.$refs.keywordsDateTextBoxWrapper).val(this.initValue);
        } else {
          let methodInitValue;
          // Check if it's method or date
          const checkDate = Date.parse(this.initValue);
          if (isNaN(checkDate)) {
            // It's not a valid date string, so it's method
            methodInitValue = this.initValue;
          }

          if (methodInitValue) {
            this.dateMode = false;
            const existingMethod = this.methods.filter(m => m.method === methodInitValue);
            if (existingMethod && existingMethod.length) {
              // If it's provided method, select it
              this.methodsComboBox.select(function(item) {
                return item.method === methodInitValue;
              });
            } else {
              // If it's custom method, set value
              this.methodsComboBox.value(methodInitValue);
            }
          } else {
            this.datePicker.value(this.initValue);
          }
        }
      }
    },
    clearData() {
      this.datePicker.value("");
      this.methodsComboBox.value("");
      $(this.$refs.keywordsDateTextBoxWrapper).val("");
    }
  }
};
</script>
<style>
.condition-table-keywords-date-mode {
  width: 100%;
}

.condition-table-keywords-date-function {
  width: 100%;
}

.condition-table-keywords-date-group {
  width: 100%;
}
.condition-table-keywords-date-picker {
  width: 100%;
}
.condition-table-keywords-date-datepicker {
  display: flex;
  width: 100%;
}

.condition-table-keywords-date-textbox.k-textbox {
  width: 100%;
}
.func-button-clicked {
  background-color: #157efb;
  color: white;
}
</style>
