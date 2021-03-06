<template>
  <div :class="errorClass" class="keywords-wrapper" ref="keywordsWrapper">
    <component
      v-show="shouldBeDisplayed"
      v-if="fieldType"
      :is="componentsConfiguration[fieldType].componentName"
      v-bind="componentsConfiguration[fieldType].props"
      @keysChange="onKeysChange"
      ref="keywordsComponent"
    ></component>
  </div>
</template>
<script>
import "@progress/kendo-ui/js/kendo.combobox";
import BaseComponent from "./ConditionTableBaseComponent.vue";
import KeywordsEnum from "./KeywordsComponents/KeywordsEnum";
import KeywordsDate from "./KeywordsComponents/KeywordsDate";
import KeywordsDocid from "./KeywordsComponents/KeywordsDocid";
import KeywordsTime from "./KeywordsComponents/KeywordsTime";
import KeywordsMoney from "./KeywordsComponents/KeywordsMoney";
import KeywordsTimestamp from "./KeywordsComponents/KeywordsTimestamp";
import KeywordsWid from "./KeywordsComponents/KeywordsWid";
import KeywordsDefault from "./KeywordsComponents/KeywordsDefault";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";

export default {
  name: "condition-table-keywords",
  components: {
    "condition-table-keywords-enum": KeywordsEnum,
    "condition-table-keywords-date": KeywordsDate,
    "condition-table-keywords-docid": KeywordsDocid,
    "condition-table-keywords-time": KeywordsTime,
    "condition-table-keywords-money": KeywordsMoney,
    "condition-table-keywords-timestamp": KeywordsTimestamp,
    "condition-table-keywords-wid": KeywordsWid,
    "condition-table-keywords-default": KeywordsDefault
  },
  extends: BaseComponent,
  mixins: [AnkI18NMixin],
  props: {
    controllerProxy: Function,
    field: null,
    operator: "",
    famid: Number,
    initValue: ""
  },
  data() {
    return {
      fieldType: "",
      initialFieldType: null,
      errorClass: ""
    };
  },
  computed: {
    componentsConfiguration() {
      return {
        enum: {
          componentName: "condition-table-keywords-enum",
          props: {
            famid: this.famid,
            field: this.field,
            operator: this.operator,
            initValue: this.initialFieldType === "enum" ? this.initValue : "",
            methods: this.field ? this.field.methods : []
          }
        },
        docid: {
          componentName: "condition-table-keywords-docid",
          props: {
            famid: this.famid,
            field: this.field,
            operator: this.operator,
            initValue: this.initialFieldType === "docid" ? this.initValue : "",
            methods: this.field ? this.field.methods : []
          }
        },
        account: {
          componentName: "condition-table-keywords-docid",
          props: {
            famid: this.famid,
            field: this.field,
            operator: this.operator,
            initValue: this.initialFieldType === "account" ? this.initValue : "",
            methods: this.field ? this.field.methods : []
          }
        },
        wid: {
          componentName: "condition-table-keywords-wid",
          props: {
            operator: this.operator,
            famid: this.famid,
            controllerProxy: this.controllerProxy,
            initValue: this.initialFieldType === "wid" ? this.initValue : ""
          }
        },
        date: {
          componentName: "condition-table-keywords-date",
          props: {
            operator: this.operator,
            methods: this.field ? this.field.methods : [],
            initValue: this.initialFieldType === "date" ? this.initValue : ""
          }
        },
        timestamp: {
          componentName: "condition-table-keywords-timestamp",
          props: {
            operator: this.operator,
            methods: this.field ? this.field.methods : [],
            initValue: this.initialFieldType === "timestamp" ? this.initValue : ""
          }
        },
        time: {
          componentName: "condition-table-keywords-time",
          props: {
            operator: this.operator,
            initValue: this.initialFieldType === "time" ? this.initValue : "",
            methods: this.field ? this.field.methods : []
          }
        },
        money: {
          componentName: "condition-table-keywords-money",
          props: {
            operator: this.operator,
            initValue: this.initialFieldType === "money" ? this.initValue : "",
            methods: this.field ? this.field.methods : []
          }
        },
        default: {
          componentName: "condition-table-keywords-default",
          props: {
            initValue: this.initialFieldType === "default" ? this.initValue : ""
          }
        }
      };
    },
    shouldBeDisplayed() {
      return !!this.field && this.operator && this.operator !== "is null" && this.operator !== "is not null";
    }
  },
  methods: {
    onKeysChange: function(event) {
      this.$emit("valueChange", {
        column: this.name,
        row: this.row,
        smartFieldId: "se_keys",
        smartFieldValue: {
          value: event.smartFieldValue,
          index: this.row
        },
        parentValue: event.parentValue
      });
    },
    clearKeysValue: function() {
      this.$emit("valueChange", {
        column: this.name,
        row: this.row,
        smartFieldId: "se_keys",
        smartFieldValue: {
          value: null,
          index: this.row
        },
        parentValue: null
      });
    },
    clearInnerData: function() {
      if (this.$refs.keywordsComponent) {
        this.$refs.keywordsComponent.clearData();
      }
    },
    checkValidity() {
      if (this.$refs.keywordsComponent) {
        const valid = this.$refs.keywordsComponent.isValid();
        let that = this;
        if (!valid && this.shouldBeDisplayed) {
          $(this.$refs.keywordsWrapper)
            .tooltip({
              placement: "bottom",
              html: true,
              animation: false,
              title: function wAttributeSetErrorTitle() {
                const rawMessage = $("<div/>")
                  .text(that.$t("dsearch.Select keys"))
                  .html();
                return (
                  `<div class=""><span class="btn fa fa-times button-close-error close-tooltip-keys">&nbsp;</span>` +
                  rawMessage +
                  "</div>"
                );
              },
              trigger: "manual"
            })
            .one("shown.bs.tooltip", function wErrorTooltip() {
              $($(".close-tooltip-keys")[that.row]).on("click", () => {
                $(that.$refs.keywordsWrapper).tooltip("hide");
                that.errorClass = "";
              });
              that.errorClass = "hasError";
            });
          $(this.$refs.keywordsWrapper).tooltip("show");
        } else {
          $(this.$refs.keywordsWrapper).tooltip("hide");
          this.errorClass = "";
        }
        return valid;
      } else {
        return true;
      }
    }
  },
  mounted() {
    this.controllerProxy("addEventListener", "beforeSave", event => {
      if (!this.checkValidity()) {
        event.preventDefault();
      }
    });
    this.controllerProxy("addEventListener", "ready", () => {
      this.checkValidity();
    });
  },
  watch: {
    field: function(newValue) {
      if (newValue) {
        const rawType = newValue.type.replace("[]", "");
        if (this.componentsConfiguration[rawType]) {
          this.fieldType = rawType;
        } else {
          this.fieldType = "default";
        }
      }
      if (this.initialFieldType === null) {
        this.initialFieldType = this.fieldType;
      }
      this.clearInnerData();
    },
    shouldBeDisplayed: function(newValue) {
      if (!newValue) {
        this.clearKeysValue();
      }
    },
    errorClass(newValue) {
      $(this.$refs.keywordsWrapper)
        .closest(".search-tabs")
        .find(".search-condition-tab--label")
        .toggleClass("hasError", !!newValue);
    }
  }
};
</script>
<style>
.keywords-wrapper.hasError,
.search-condition-tab--label.hasError {
  outline: solid 2px #ff542c;
}
</style>
