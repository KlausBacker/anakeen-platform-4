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
    <div v-show="!isTextBox" class="condition-table-keywords-docid-combobox">
      <div v-show="docidMode" class="condition-table-keywords-docid-docid">
        <div class="condition-table-keywords-docid-combobox" ref="keywordsDocidComboBoxWrapper"></div>
      </div>
      <div v-show="!docidMode" class="condition-table-keywords-docid-combobox">
        <div ref="keywordsDocidFunctionWrapper" class="condition-table-keywords-docid-function" />
      </div>
      <button ref="funcButton" class="condition-table-keywords-docid-funcBtn" @click="onFuncButtonClick">
        Σ
      </button>
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
    initValue: "",
    methods: null,
  },
  data() {
    return {
      textBoxOperators: ["~*", "!~*", "=~*"],
      comboBox: null,
      initHtmlTitle: "",
      myInitValue: "",
      methodsComboBox: null,
      docidMode: true
    };
  },
  watch: {
    methods: function(newValue) {
      if (this.methodsComboBox) {
        this.methodsComboBox.setDataSource(newValue);
      }
    }
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
        valid = this.$refs.keywordsDocidTextBoxWrapper.value !== "";
      } else {
        if (this.docidMode) {
          valid = !!this.comboBox.value();
        } else {
          valid = !!this.methodsComboBox.value();
        }
      }
      return valid;
    },
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
    onFuncChange() {
      const value = this.methodsComboBox.value();
      this.$emit("keysChange", {
        smartFieldValue: value,
        parentValue: value
      });
    },
    onFuncButtonClick() {
      this.docidMode = !this.docidMode;
      this.docidMode ? this.onComboBoxChange() : this.onFuncChange();
      $(this.$refs.funcButton).toggleClass("func-button-clicked");
    },
    initializeData() {
      if (this.initValue) {
        if (this.isTextBox) {
          $(this.$refs.keywordsDocidTextBoxWrapper).val(this.initValue);
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
            this.docidMode = false;
            this.methodsComboBox.select(function(item) {
              return item.method === methodInitValue;
            });
          } else {
            this.comboBox.value(this.initValue);
          }
        }
      }
    },
    initData() {
      if (this.myInitValue) {
        if (!this.isTextBox) {
          let that = this;
          this.comboBox.select(function(item) {
            return item.id === that.myInitValue;
          });
          this.onComboBoxChange();
        } else {
          $(this.$refs.keywordsDocidTextBoxWrapper).val(this.myInitValue);
        }
      }
      this.myInitValue = "";
      this.initHtmlTitle = "";
    },
    clearData() {
      this.comboBox.value("");
      this.methodsComboBox.value("");
      $(this.$refs.keywordsDocidTextBoxWrapper).val("");
    },
    fetchData() {
      let that = this;
      let dataSource = {
        serverFiltering: true,
        transport: {
          read: function(options) {
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

                let isInData = false;
                result.data.forEach(function eachResultRelationsIRelation(item) {
                  info.push({
                    id: item.id,
                    htmlTitle: item.htmlTitle
                  });
                  if (item.id === that.myInitValue) {
                    isInData = true;
                  }
                });
                if (that.myInitValue && !isInData) {
                  info.unshift({
                    id: that.myInitValue,
                    htmlTitle: that.initHtmlTitle
                  });
                }
                options.success(info);
                that.initData();
              },
              error: function errorRequestRelationsIRelation(result) {
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
    this.myInitValue = this.initValue;
    this.comboBox = $(this.$refs.keywordsDocidComboBoxWrapper)
      .kendoComboBox({
        width: 200,
        filter: "contains",
        dataValueField: "id",
        dataTextField: "htmlTitle",
        clearButton: false,
        change: this.onComboBoxChange
      })
      .data("kendoComboBox");
    this.methodsComboBox = $(this.$refs.keywordsDocidFunctionWrapper)
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
    console.log(this.methods);
    this.funcButton = $(this.$refs.funcButton)
      .kendoButton()
      .data("kendoButton");
    this.initializeData();
    if (this.myInitValue) {
      let that = this;
      $.ajax({
        type: "GET",
        url: "/api/v2/smart-elements/" + this.myInitValue + ".json?fields=document.properties.title",
        dataType: "json",
        success: function(result) {
          that.initHtmlTitle = result.data.document.properties.title;
          if (that.initHtmlTitle) {
            that.fetchData();
          } else {
            that.comboBox.one("open", that.fetchData);
          }
        }
      });
    } else {
      this.comboBox.one("open", this.fetchData);
    }
  },
  watch: {
    field: function() {
      this.$nextTick(this.fetchData);
    }
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
  display: flex;
  flex-direction: row;
}

.condition-table-keywords-docid-function {
  width: 100%;
}

.condition-table-keywords-docid-textbox {
  width: 100%;
}

.condition-table-keywords-docid-docid {
  width: 100%;
}
.func-button-clicked {
  background-color: #157efb;
  color: white;
}
</style>
