import ConditionTableOperator from "./Table Components/ConditionTableOperator";
import ConditionTableLeftP from "./Table Components/ConditionTableLeftP";
import ConditionTableFields from "./Table Components/ConditionTableFields";
import ConditionTableFunctions from "./Table Components/ConditionTableFunctions";
import ConditionTableKeywords from "./Table Components/ConditionTableKeywords";
import ConditionTableRightP from "./Table Components/ConditionTableRightP";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";

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
      famid: null,
      conditionRuleType: "and",
      conditions: [],
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
  },

  mounted() {
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
  },
  methods: {
    initTranslation() {
      this.andTitle = this.$t("dsearch.se_ol-and");
      this.orTitle = this.$t("dsearch.se_ol-or");
      this.customTitle = this.$t("dsearch.se_ol-perso");
    },
    onConditionRuleTypeChange(event) {
      this.conditionRuleType = event.target.value;
      this.controllerProxy("setValue", "se_ol", { value: this.conditionRuleType });
    },
    onAddLineButtonClick() {
      this.conditions.push(this.createDefaultCondition());
    },
    deleteCondition(event, row) {
      this.conditions.splice(row, 1);
      this.controllerProxy("removeArrayRow", "se_t_detail", row);
    },
    updateFamIds() {
      this.conditions.forEach(condition => {
        condition.fields.famid = this.famid;
        condition.keywords.famid = this.famid;
      });
    },
    loadSmartElement() {
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
          break;
      }
      this.controllerProxy("setValue", event.smartFieldId, event.smartFieldValue);
    },
    createDefaultCondition: function() {
      return {
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
