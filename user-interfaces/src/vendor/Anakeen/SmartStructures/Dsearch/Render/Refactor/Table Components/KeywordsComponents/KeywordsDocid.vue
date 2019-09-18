<template>
  <div class="condition-table-keywords-docid">
    <div v-show="isTextBox" class="condition-table-keywords-docid-textbox">
      <input
        type="text"
        class="condition-table-keywords-docid-textbox k-textbox"
        ref="keywordsDocidTextBoxWrapper"
        @change="onInputChange"
      />
    </div>
    <div v-show="isComboBox" class="condition-table-keywords-docid-combobox">
      <div class="condition-table-keywords-docid-combobox" ref="keywordsDocidComboBoxWrapper"></div>
    </div>
  </div>
</template>
<script>
import "@progress/kendo-ui/js/kendo.combobox";

export default {
  name: "condition-table-keywords-docid",
  props: {
    famid: Number,
    field: null,
    operator: "",
    initValue: ""
  },
  data() {
    return {
      textBoxOperators: ["~*", "!~*", "=~*"],
      comboBoxOperators: ["~y", "=", "!="],
      comboBox: null
    };
  },
  computed: {
    isTextBox() {
      return this.textBoxOperators.indexOf(this.operator) !== -1;
    },
    isComboBox() {
      return this.comboBoxOperators.indexOf(this.operator) !== -1;
    }
  },
  methods: {
    onComboBoxChange: function() {
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
        if (this.isComboBox) {
          let that = this;
          this.comboBox.select(function(item) {
            return item.id === that.initValue;
          });
        } else if (this.isTextBox) {
          $(this.$refs.keywordsDocidTextBoxWrapper).val(this.initValue);
        }
      }
    },
    clearData() {
      this.comboBox.value("");
      $(this.$refs.keywordsDocidTextBoxWrapper).val("");
    }
  },
  mounted() {
    let that = this;
    this.$nextTick(() => {
      this.comboBox = $(this.$refs.keywordsDocidComboBoxWrapper)
        .kendoComboBox({
          width: 200,
          filter: "contains",
          dataValueField: "id",
          dataTextField: "htmlTitle",
          clearButton: false,
          change: this.onComboBoxChange,
          dataSource: {
            serverFiltering: true,
            transport: {
              /**
               * function to get data
               * @param options param to return success or error data
               */
              read: function readDatasIRelation(options) {
                let filter = "";
                if (options.data.filter !== undefined) {
                  if (options.data.filter.filters[0] !== undefined) {
                    filter = options.data.filter.filters[0].value;
                  }
                }
                $.ajax({
                  type: "GET",
                  url:
                    "/api/v2/smartstructures/dsearch/relations/" +
                    that.famid +
                    "/" +
                    that.field.id +
                    "?slice=25&offset=0&keyword=" +
                    filter,
                  dataType: "json",
                  success: function succesRequestRelationsIRelation(result) {
                    let info = [];
                    result.data.forEach(function eachResultRelationsIRelation(item) {
                      info.push({
                        id: item.id,
                        htmlTitle: item.htmlTitle
                      });
                    });
                    options.success(info);
                    that.initData();
                  },
                  error: function errorRequestRelationsIRelation(result) {
                    options.error(result);
                  }
                });
              }
            }
          }
        })
        .data("kendoComboBox");
    });
  }
};
</script>
<style>
.condition-table-keywords-docid {
  width: 100%;
}

.condition-table-keywords-docid-textbox {
  width: 100%;
}

.condition-table-keywords-docid-combobox {
  width: 100%;
}
</style>
