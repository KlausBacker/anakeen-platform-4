import ConditionTableOperator from "./Table Components/ConditionTableOperator";
import ConditionTableLeftP from "./Table Components/ConditionTableLeftP";
import ConditionTableFields from "./Table Components/ConditionTableFields";
import ConditionTableFunctions from "./Table Components/ConditionTableFunctions";
import ConditionTableKeywords from "./Table Components/ConditionTableKeywords";
import ConditionTableRightP from "./Table Components/ConditionTableRightP";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import $ from "jquery";

function uuidv4() {
  let dt = new Date().getTime();
  const uuid = "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function(c) {
    const r = (dt + Math.random() * 16) % 16 | 0;
    dt = Math.floor(dt / 16);
    return (c === "x" ? r : (r & 0x3) | 0x8).toString(16);
  });
  return uuid;
}

export default {
  name: "search-conditions",
  mixins: [AnkI18NMixin],
  components: {
    "condition-table-operator": ConditionTableOperator,
    "condition-table-leftp": ConditionTableLeftP,
    "condition-table-fields": ConditionTableFields,
    "condition-table-functions": ConditionTableFunctions,
    "condition-table-keywords": ConditionTableKeywords,
    "condition-table-rightp": ConditionTableRightP
  },
  props: {
    controllerProxy: {
      type: Function,
      default: () => () => {}
    }
  },
  data() {
    return {
      isLoading: false,
      allOperators: [],
      famid: null,
      conditionRuleType: "and",
      conditions: [],
      tempParentheses: {
        leftp: [],
        rightp: []
      },
      andTitle: "",
      orTitle: "",
      customTitle: ""
    };
  },
  computed: {
    isPerso: function() {
      return this.conditionRuleType === "perso";
    },
    columns: function() {
      return [
        {
          name: "operator",
          title: this.$t("dsearch.se_ols"),
          class: "conditions-column-head-operator",
          componentName: "condition-table-operator",
          propertyName: "list",
          visible: this.isPerso
        },
        {
          name: "leftp",
          title: "(",
          class: "conditions-column-head-leftp",
          componentName: "condition-table-leftp",
          propertyName: "labelChecked",
          visible: this.isPerso
        },
        {
          name: "fields",
          title: this.$t("dsearch.se_attrids"),
          class: "conditions-column-head-fields",
          componentName: "condition-table-fields",
          propertyName: "list",
          visible: true
        },
        {
          name: "functions",
          title: this.$t("dsearch.se_funcs"),
          class: "conditions-column-head-functions",
          componentName: "condition-table-functions",
          propertyName: "list",
          visible: true
        },
        {
          name: "keywords",
          title: this.$t("dsearch.se_keys"),
          class: "conditions-column-head-keywords",
          componentName: "condition-table-keywords",
          propertyName: "list",
          visible: true
        },
        {
          name: "rightp",
          title: ")",
          class: "conditions-column-head-rightp",
          componentName: "condition-table-rightp",
          propertyName: "labelChecked",
          visible: this.isPerso
        }
      ];
    }
  },
  created() {
    this.$on("localeLoaded", this.initTranslation);
    const that = this;
    $.getJSON("/api/v2/smartstructures/dsearch/operators/", function requestOperatorsSReady(data) {
      that.allOperators = [...data.data];
    });
  },

  mounted() {
    this.isLoading = true;
    this.controllerProxy(
      "addEventListener",
      "smartFieldChange",
      {
        name: "searchAttributesFamilyChanged.sAttr",
        check: function isDSearch(document) {
          return document.type === "search";
        },
        smartFieldCheck: function isFamily(attribute) {
          return attribute.id === "se_famid";
        }
      },
      (event, smartElement, smartField, values) => {
        this.famid = parseInt(values.current.value);
        this.updateFamIds();
      }
    );
    this.loadSmartElement();
    this.isLoading = false;
  },
  methods: {
    initTranslation() {
      this.andTitle = this.$t("dsearch.se_ol-and");
      this.orTitle = this.$t("dsearch.se_ol-or");
      this.customTitle = this.$t("dsearch.se_ol-perso");
    },
    onConditionRuleTypeChange(event) {
      this.manageTemporarySavedParentheses();
      this.conditionRuleType = event.target.value;
      this.controllerProxy("setValue", "se_ol", { value: this.conditionRuleType });
      this.loadSmartElement();
    },
    onAddLineButtonClick() {
      this.conditions.push(this.createDefaultCondition());
    },
    deleteCondition(event, row) {
      if (this.tempParentheses.leftp.length > 0) {
        this.tempParentheses.leftp.splice(row, 1);
        this.tempParentheses.rightp.splice(row, 1);
      }
      this.conditions.splice(row, 1);
      this.controllerProxy("removeArrayRow", "se_t_detail", row);
      this.loadSmartElement();
    },
    updateFamIds() {
      this.conditions.forEach(condition => {
        condition.fields.famid = this.famid;
        condition.keywords.famid = this.famid;
      });
    },
    manageTemporarySavedParentheses() {
      if (this.isPerso !== true) {
        // Temporary save parentheses
        if (this.tempParentheses.leftp.length === 0) {
          this.tempParentheses.leftp.push(...this.controllerProxy("getValue", "se_leftp"));
          this.tempParentheses.rightp.push(...this.controllerProxy("getValue", "se_rightp"));
        }
        // Then puting them to null
        this.controllerProxy("setValue", "se_leftp", Array(this.conditions.length).fill({ value: null }));
        this.controllerProxy("setValue", "se_rightp", Array(this.conditions.length).fill({ value: null }));
      } else if (this.tempParentheses.leftp.length > 0) {
        // Apply temporary saved parentheses
        this.controllerProxy("setValue", "se_leftp", this.tempParentheses.leftp);
        this.controllerProxy("setValue", "se_rightp", this.tempParentheses.rightp);
        this.tempParentheses.leftp = [];
        this.tempParentheses.rightp = [];
      }
    },
    loadSmartElement() {
      // If function is called with an already initialized SmartElement
      if (this.conditions.length > 0) {
        this.conditions = [];
      }
      this.famid = parseInt(this.controllerProxy("getValue", "se_famid").value); //init Famid
      const se_olValue = this.controllerProxy("getValue", "se_ol").value;
      if (se_olValue) {
        this.conditionRuleType = se_olValue;
      }

      const operator = this.controllerProxy("getValue", "se_ols");
      const leftp = this.controllerProxy("getValue", "se_leftp");
      const fields = this.controllerProxy("getValue", "se_attrids");
      const funcs = this.controllerProxy("getValue", "se_funcs");
      const keys = this.controllerProxy("getValue", "se_keys");
      const rightp = this.controllerProxy("getValue", "se_rightp");
      const length = fields.length;

      for (let row = 0; row < length; row++) {
        let condition = this.createDefaultCondition();
        const funcInitValue = funcs[row].value;

        condition.operator.initValue = operator[row].value;
        condition.leftp.initValue = leftp[row].value;
        condition.fields.initValue = fields[row].value;
        condition.functions.initValue = funcInitValue;
        condition.functions.operatorsList = this.allOperators;
        condition.keywords.initValue = keys[row].value;
        condition.keywords.operator = funcInitValue;
        condition.rightp.initValue = rightp[row].value;
        this.conditions.push(condition);
      }
    },
    updateConditions(event) {
      switch (event.column) {
        case "operator":
          break;
        case "leftp":
          this.conditions[event.row].leftp.initValue = event.smartFieldValue.value;
          break;
        case "fields":
          this.conditions[event.row].functions.field = event.parentValue;
          this.conditions[event.row].keywords.field = event.parentValue;
          break;
        case "functions":
          this.conditions[event.row].keywords.operator = event.parentValue;
          break;
        case "keywords":
          break;
        case "rightp":
          this.conditions[event.row].rightp.initValue = event.smartFieldValue.value;
          break;
      }
      this.controllerProxy("setValue", event.smartFieldId, event.smartFieldValue);
    },
    createDefaultCondition: function() {
      return {
        uid: uuidv4(),
        operator: {
          initValue: ""
        },
        leftp: {
          initValue: ""
        },
        fields: {
          controllerProxy: this.controllerProxy,
          famid: this.famid,
          initValue: ""
        },
        functions: {
          field: null,
          initValue: ""
        },
        keywords: {
          controllerProxy: this.controllerProxy,
          field: null,
          operator: "",
          famid: this.famid,
          initValue: ""
        },
        rightp: {
          initValue: ""
        }
      };
    }
  }
};
