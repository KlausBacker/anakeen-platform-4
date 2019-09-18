<template>
  <div class="condition-table-keywords-timestamp-group">
    <div v-show="isTextBox" class="condition-table-keywords-timestamp-textbox">
      <input
        type="text"
        class="condition-table-keywords-timestamp-textbox k-textbox"
        ref="keywordsTimestampTextBoxWrapper"
        @change="onInputChange"
      />
    </div>
    <div v-show="!isTextBox" class="condition-table-keywords-timestamp-datepicker">
      <div v-show="dateMode" class="condition-table-keywords-timestamp-timestamp">
        <input class="condition-table-keywords-timestamp-datetimepicker" ref="keywordsTimestampWrapper" />
      </div>
      <div v-show="!dateMode" class="condition-table-keywords-timestamp-combobox">
        <div class="condition-table-keywords-timestamp-function" ref="keywordsTimestampFunctionWrapper"></div>
      </div>
      <button class="condition-table-keywords-timestamp-funcBtn" ref="funcButton" @click="onFuncButtonClick">Σ</button>
    </div>
  </div>
</template>
<script>
import "@progress/kendo-ui/js/kendo.datetimepicker";
import "@progress/kendo-ui/js/kendo.combobox";
import "@progress/kendo-ui/js/kendo.button";

export default {
  name: "condition-table-keywords-timestamp",
  props: {
    operator: "",
    methods: null,
    initValue: ""
  },
  data() {
    return {
      dateTimePicker: null,
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
  methods: {
    onInputChange(event) {
      let value = event.target.value;
      this.$emit("keysChange", {
        smartFieldValue: value,
        parentValue: value
      });
    },
    onDateChange() {
      let timeDate = this.dateTimePicker.value();
      let sTimeDate = "";
      if (timeDate) {
        sTimeDate =
          timeDate.getFullYear() +
          "-" +
          this.searchPadNumber(timeDate.getMonth() + 1) +
          "-" +
          this.searchPadNumber(timeDate.getDate()) +
          " " +
          this.searchPadNumber(timeDate.getHours()) +
          ":" +
          this.searchPadNumber(timeDate.getMinutes()) +
          ":" +
          this.searchPadNumber(timeDate.getSeconds());
      }
      this.$emit("keysChange", {
        smartFieldValue: sTimeDate,
        parentValue: sTimeDate
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
      this.dateMode = !this.dateMode;
      this.dateMode ? this.onDateChange() : this.onFuncChange();
    },
    initData() {
      if (this.initValue) {
        if (this.isTextBox) {
          $(this.$refs.keywordsTimestampTextBoxWrapper).val(this.initValue);
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
            this.dateMode = false;
            this.methodsComboBox.select(function(item) {
              return item.method === methodInitValue;
            });
          } else {
            this.dateTimePicker.value(this.initValue);
          }
        }
      }
    },
    clearData() {
      this.dateTimePicker.value("");
      this.methodsComboBox.value("");
      $(this.$refs.keywordsTimestampTextBoxWrapper).val("");
    }
  },
  mounted() {
    this.dateTimePicker = $(this.$refs.keywordsTimestampWrapper)
      .kendoDateTimePicker({
        parseFormats: ["yyyy-MM-dd HH:mm:ss", "yyyy-MM-ddTHH:mm:ss", "yyyy-MM-ddTHH:mm"],
        timeFormat: "HH:mm",
        format: null, // standard format depends of the user's langage
        /* trigger a fonction that change the value of the date from the displayValue according to ISO 8601 */
        change: () => this.onDateChange()
      })
      .data("kendoDateTimePicker");
    this.methodsComboBox = $(this.$refs.keywordsTimestampFunctionWrapper)
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
  watch: {
    methods: function(newValue) {
      if (this.methodsComboBox) {
        this.methodsComboBox.setDataSource(newValue);
      }
    }
  }
};
</script>
<style>
.condition-table-keywords-timestamp-datetimepicker.k-input {
  width: 100%;
  margin-left: 4rem;
}

.condition-table-keywords-timestamp-date {
  width: 100%;
}
.condition-table-keywords-timestamp-combobox {
  width: 100%;
}

.k-datetimepicker.condition-table-keywords-timestamp-datetimepicker {
  width: 100%;
}
.condition-table-keywords-timestamp-function {
  width: 100%;
}

.condition-table-keywords-timestamp-group {
  width: 100%;
}

.condition-table-keywords-timestamp-datepicker {
  display: flex;
  width: 100%;
}

.condition-table-keywords-timestamp-timestamp {
  width: 100%;
}

.condition-table-keywords-timestamp-textbox {
  width: 100%;
}
</style>
