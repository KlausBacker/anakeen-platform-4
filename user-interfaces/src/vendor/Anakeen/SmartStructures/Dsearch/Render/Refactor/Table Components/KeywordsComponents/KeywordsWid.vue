<template>
  <div class="condition-table-keywords-wid" ref="keywordsWidWrapper"></div>
</template>
<script>
import "@progress/kendo-ui/js/kendo.combobox";

export default {
  name: "condition-table-keywords-wid",
  props: {
    operator: "",
    famid: Number,
    controllerProxy: Function,
    initValue: ""
  },
  data() {
    return {
      comboBox: null,
      workflows: [],
      dataWorkflow: []
    };
  },
  methods: {
    isValid() {
      return !!this.comboBox.value();
    },
    onComboBoxChange() {
      const value = this.comboBox.value();
      this.$emit("keysChange", {
        smartFieldValue: value,
        parentValue: value
      });
    },
    initData() {
      if (this.initValue) {
        let that = this;
        this.comboBox.select(function(item) {
          return item.id === that.initValue;
        });
        this.initValue = "";
      }
    },
    clearData() {
      this.comboBox.value("");
    },
    fetchData() {
      let that = this;
      this.workflows = [];
      this.dataWorkflow = [];

      $.getJSON("/api/v2/smart-elements/" + this.famid + "/workflows/states/?allStates=1", function requestWorkflows(
        data
      ) {
        $.each(data.data.states, function eachStatesSReady(key, value) {
          that.workflows.push(value);
        });
      }).done(function() {
        const typeRevision = that.controllerProxy("getValue", "se_latest").value;
        that.workflows.forEach(item => {
          let myObject;
          if (typeRevision === "fixed" || typeRevision === "allfixed" || typeRevision === "lastfixed") {
            myObject = {
              id: item.id,
              label: item.label
            };
          } else if (item.activity !== "") {
            if (typeRevision === "yes") {
              myObject = {
                id: item.id,
                label: item.activity
              };
            } else {
              myObject = {
                id: item.id,
                label: item.label + "/" + item.activity
              };
            }
          } else {
            if (typeRevision === "yes") {
              myObject = {
                id: item.id,
                label: item.label
              };
            } else {
              myObject = {
                id: item.id,
                label: item.label
              };
            }
          }
          myObject.color = item.color;
          that.dataWorkflow.push(myObject);
        });
        that.comboBox.setDataSource(that.dataWorkflow);
        that.initData();
      });
    }
  },
  mounted() {
    this.comboBox = $(this.$refs.keywordsWidWrapper)
      .kendoComboBox({
        width: 200,
        filter: "contains",
        clearButton: false,
        minLength: 0,
        dataValueField: "id",
        dataTextField: "label",
        template: `<span style='background-color: #: color #; width: 100%;'>#: label #</span>`,
        change: this.onComboBoxChange
      })
      .data("kendoComboBox");
    this.comboBox.one("open", this.fetchData);
  },
  watch: {
    famid: function() {
      this.$nextTick(this.fetchData);
    }
  }
};
</script>
<style>
.condition-table-keywords-wid {
  width: 100%;
}
.condition-table-keywords-wid.k-widget.k-combobox {
  display: block;
}
</style>
