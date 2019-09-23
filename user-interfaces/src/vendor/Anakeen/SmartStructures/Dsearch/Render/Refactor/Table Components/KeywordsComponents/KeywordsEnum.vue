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
    <div class="condition-table-keywords-enum-combobox-wrapper" v-show="!isTextBox">
      <div class="condition-table-keywords-enum-combobox" ref="keywordsEnumWrapper"></div>
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
    initValue: ""
  },
  data() {
    return {
      textBoxOperators: ["~*", "!~*"],
      comboBox: null
    };
  },
  computed: {
    isTextBox() {
      return this.textBoxOperators.indexOf(this.operator) !== -1;
    }
  },
  methods: {
    isValid() {
      let valid;
      if (this.isTextBox) {
        valid = this.$refs.keywordsEnumTextBoxWrapper.value !== "";
      } else {
        valid = !!this.comboBox.value();
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
    initData() {
      if (this.initValue) {
        if (!this.isTextBox) {
          let that = this;
          this.comboBox.select(function(item) {
            return item.value === that.initValue;
          });
        } else {
          $(this.$refs.keywordsEnumTextBoxWrapper).val(this.initValue);
        }
      }
    },
    clearData() {
      this.comboBox.value("");
      $(this.$refs.keywordsEnumTextBoxWrapper).val("");
    }
  },
  mounted() {
    let that = this;
    this.comboBox = $(this.$refs.keywordsEnumWrapper)
      .kendoComboBox({
        width: 200,
        filter: "contains",
        clearButton: false,
        dataValueField: "value",
        dataTextField: "displayValue",
        change: this.onComboBoxChange,
        dataSource: {
          serverFiltering: true,
          transport: {
            /**
             * function to get data
             * @param options param to return success or error data
             */
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
        }
      })
      .data("kendoComboBox");
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
</style>
