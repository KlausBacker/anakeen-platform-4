<template>
  <div class="condition-table-keywords-enum">
    <div v-show="isTextBox" class="condition-table-keywords-enum-textbox-wrapper">
      <input
        type="text"
        class="condition-table-keywords-enum-textbox k-textbox"
        ref="keywordsEnumTextBoxWrapper"
        @change="onInputChange"
      />
    </div>
    <div v-show="!isTextBox" class="condition-table-keywords-enum-combobox-wrapper">
      <div v-show="enumMode" class="condition-table-keywords-enum-enum">
        <div class="condition-table-keywords-enum-combobox" ref="keywordsEnumWrapper"></div>
      </div>
      <div v-show="!enumMode" class="condition-table-keywords-enum-combobox">
        <div ref="keywordsEnumFunctionWrapper" class="condition-table-keywords-enum-function" />
      </div>
      <button ref="funcButton" class="condition-table-keywords-enum-funcBtn" @click="onFuncButtonClick">
        Σ
      </button>
    </div>
  </div>
</template>
<script>
import "@progress/kendo-ui/js/kendo.combobox";

export default {
  name: "condition-table-keywords-enum",
  props: {
    famid: Number,
    field: null,
    operator: "",
    initValue: "",
    methods: null
  },
  data() {
    return {
      textBoxOperators: ["~*", "!~*"],
      comboBox: null,
      methodsComboBox: null,
      enumMode: true
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
  methods: {
    isValid() {
      let valid;
      if (this.isTextBox) {
        valid = this.$refs.keywordsEnumTextBoxWrapper.value !== "";
      } else {
        if (this.enumMode) {
          valid = !!this.comboBox.value();
        } else {
          valid = !!this.methodsComboBox.value();
        }
      }
      return valid;
    },
    onComboBoxChange() {
      const value = this.comboBox.value();
      this.$emit("keysChange", {
        smartFieldValue: value,
        parentValue: value
      });
    },
    onInputChange(event) {
      let value = event.target.value;
      this.$emit("keysChange", {
        smartFieldValue: value,
        parentValue: value
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
      this.enumMode = !this.enumMode;
      this.enumMode ? this.onComboBoxChange() : this.onFuncChange();
      $(this.$refs.funcButton).toggleClass("func-button-clicked");
    },
    initData() {
      if (this.initValue) {
        if (this.isTextBox) {
          $(this.$refs.keywordsEnumTextBoxWrapper).val(this.initValue);
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
            this.enumMode = false;
            this.methodsComboBox.select(function(item) {
              return item.method === methodInitValue;
            });
          } else {
            this.comboBox.value(this.initValue);
          }
        }
      }
    },
    clearData() {
      this.comboBox.value("");
      this.methodsComboBox.value("");
      $(this.$refs.keywordsEnumTextBoxWrapper).val("");
    },
    updateDataSource() {
      let that = this;
      let dataSource = {
        serverFiltering: true,
        transport: {
          read: function readDatasIEnum(options) {
            let filter = "";
            if (options.data.filter !== undefined && options.data.filter.filters[0] !== undefined) {
              filter = {
                keyword: options.data.filter.filters[0].value,
                operator: options.data.filter.filters[0].operator
              };
            }
            $.ajax({
              type: "GET",
              url: "/api/v2/smart-structures/" + that.famid + "/enumerates/" + that.field.id,
              data: filter,
              dataType: "json",
              success: function succesRequestEnumsIEnum(result) {
                let info = [];
                result.data.enumItems.forEach(function eachResultEnumsIEnum(enumItem) {
                  info.push({
                    value: enumItem.key,
                    displayValue: enumItem.label
                  });
                });
                options.success(info);
                that.initData();
              },
              error: function errorRequestEnumsIEnum(result) {
                options.error(result);
              }
            });
          }
        }
      };
      this.comboBox.setDataSource(dataSource);
    }
  },
  mounted() {
    this.comboBox = $(this.$refs.keywordsEnumWrapper)
      .kendoComboBox({
        width: 200,
        filter: "contains",
        clearButton: false,
        dataValueField: "value",
        dataTextField: "displayValue",
        change: this.onComboBoxChange
      })
      .data("kendoComboBox");
    this.methodsComboBox = $(this.$refs.keywordsEnumFunctionWrapper)
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
    field: function() {
      this.$nextTick(this.updateDataSource);
    }
  }
};
</script>
<style>
.condition-table-keywords-enum {
  width: 100%;
}

.condition-table-keywords-enum-textbox {
  width: 100%;
}

.condition-table-keywords-enum-combobox {
  width: 100%;
}
.condition-table-keywords-enum-combobox-wrapper {
  display: flex;
  flex-direction: row;
}
.condition-table-keywords-enum-function {
  width: 100%;
}

.condition-table-keywords-enum-enum {
  width: 100%;
}

.condition-table-keywords-enum-textbox {
  width: 100%;
}
.func-button-clicked {
  background-color: #157efb;
  color: white;
}
</style>
