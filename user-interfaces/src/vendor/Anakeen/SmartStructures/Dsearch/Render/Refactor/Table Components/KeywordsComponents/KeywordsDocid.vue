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
      comboBox: null,
      initHtmlTitle: "",
      myInitValue: ""
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
        valid = this.$refs.keywordsDocidTextBoxWrapper.value !== "";
      } else {
        valid = this.comboBox ? !!this.comboBox.value() : false;
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
    this.myInitValue = this.initValue
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
}
</style>
