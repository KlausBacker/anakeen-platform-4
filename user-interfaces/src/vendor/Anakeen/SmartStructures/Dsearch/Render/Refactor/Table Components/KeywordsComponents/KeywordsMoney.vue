<template>
  <div class="condition-table-keywords-money-group">
    <div v-show="isTextBox" class="condition-table-keywords-money-textbox">
      <input
        ref="keywordsMoneyTextBoxWrapper"
        type="text"
        class="condition-table-keywords-money-textbox k-textbox"
        @change="onInputChange"
      />
    </div>
    <div v-show="!isTextBox" class="condition-table-keywords-money-moneypicker">
      <div v-show="moneyMode" class="condition-table-keywords-money-money">
        <input type="number" ref="keywordsMoneyWrapper" class="condition-table-keywords-money-moneypicker" />
      </div>
      <div v-show="!moneyMode" class="condition-table-keywords-money-combobox">
        <div ref="keywordsMoneyFunctionWrapper" class="condition-table-keywords-money-function" />
      </div>
      <button ref="funcButton" class="condition-table-keywords-money-funcBtn" @click="onFuncButtonClick">
        Σ
      </button>
    </div>
  </div>
</template>
<script>
import "@progress/kendo-ui/js/kendo.numerictextbox";
import "@progress/kendo-ui/js/kendo.combobox";
import "@progress/kendo-ui/js/kendo.button";

export default {
  name: "condition-table-keywords-money",
  props: {
    operator: "",
    methods: null,
    initValue: ""
  },
  data() {
    return {
      moneyPicker: null,
      funcButton: null,
      methodsComboBox: null,
      moneyMode: true,
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
    this.moneyPicker = $(this.$refs.keywordsMoneyWrapper)
      .kendoNumericTextBox({
        format: "c",
        decimals: 3,
        change: () => this.onMoneyChange()
      })
      .data("kendoNumericTextBox");
    this.methodsComboBox = $(this.$refs.keywordsMoneyFunctionWrapper)
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
        valid = this.$refs.keywordsMoneyTextBoxWrapper.value !== "";
      } else {
        if (this.moneyMode) {
          valid = !!this.moneyPicker.value();
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
    onMoneyChange() {
      let money = this.moneyPicker.value();
      this.$emit("keysChange", {
        smartFieldValue: money,
        parentValue: money
      });
    },
    onFuncChange() {
      const value = this.methodsComboBox.value();
      this.$emit("keysChange", {
        smartFieldValue: value,
        parentValue: value
      });
    },
    onFuncButtonClick() {
      this.moneyMode = !this.moneyMode;
      this.moneyMode ? this.onMoneyChange() : this.onFuncChange();
      $(this.$refs.funcButton).toggleClass("func-button-clicked");
    },
    initData() {
      if (this.initValue) {
        if (this.isTextBox) {
          $(this.$refs.keywordsMoneyTextBoxWrapper).val(this.initValue);
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
            this.moneyMode = false;
            this.methodsComboBox.select(function(item) {
              return item.method === methodInitValue;
            });
          } else {
            this.moneyPicker.value(this.initValue);
          }
        }
      }
    },
    clearData() {
      this.moneyPicker.value("");
      this.methodsComboBox.value("");
      $(this.$refs.keywordsMoneyTextBoxWrapper).val("");
    }
  }
};
</script>
<style>
.condition-table-keywords-money-moneypicker.k-input {
  width: 100%;
  margin-left: 4rem;
}

.condition-table-keywords-money-money {
  width: 100%;
}
.condition-table-keywords-money-combobox {
  width: 100%;
}

.k-moneypicker.condition-table-keywords-money-moneypicker {
  width: 100%;
}
.condition-table-keywords-money-function {
  width: 100%;
}

.condition-table-keywords-money-group {
  width: 100%;
}

.condition-table-keywords-money-moneypicker {
  display: flex;
  width: 100%;
}

.condition-table-keywords-money-money {
  display: flex;
  flex-direction: column;
}
.condition-table-keywords-money-textbox {
  width: 100%;
}
.func-button-clicked {
  background-color: #157efb;
  color: white;
}
</style>
