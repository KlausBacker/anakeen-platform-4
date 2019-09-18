import ConditionTableOperator from "./Table Components/ConditionTableOperator";
import ConditionTableLeftP from "./Table Components/ConditionTableLeftP";
import ConditionTableFields from "./Table Components/ConditionTableFields";
import ConditionTableFunctions from "./Table Components/ConditionTableFunctions";
import ConditionTableKeywords from "./Table Components/ConditionTableKeywords";
import ConditionTableRightP from "./Table Components/ConditionTableRightP";

export default {
  name: "search-conditions",
  components: {
    "condition-table-operator": ConditionTableOperator,
    "condition-table-leftp": ConditionTableLeftP,
    "condition-table-fields": ConditionTableFields,
    "condition-table-functions": ConditionTableFunctions,
    "condition-table-keywords": ConditionTableKeywords,
    "condition-table-rightp": ConditionTableRightP
  },
  props: {
    controller: Object
  },
  data() {
    return {
      famid: null,
      conditionRuleType: "and",
      conditions: []
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
          title: "Opérateur",
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
          title: "Smart Fields",
          class: "conditions-column-head-fields",
          componentName: "condition-table-fields",
          propertyName: "list",
          visible: true
        },
        {
          name: "functions",
          title: "Functions",
          class: "conditions-column-head-functions",
          componentName: "condition-table-functions",
          propertyName: "list",
          visible: true
        },
        {
          name: "keywords",
          title: "Mots-clefs",
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
  mounted() {
    this.controller.addEventListener(
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
    onConditionRuleTypeChange(event) {
      this.conditionRuleType = event.target.value;
      this.controller.setValue("se_ol", { value: this.conditionRuleType });
    },
    onAddLineButtonClick() {
      this.conditions.push(this.createDefaultCondition());
    },
    deleteCondition(event, row) {
      this.conditions.splice(row, 1);
      this.controller.removeArrayRow("se_t_detail", row);
    },
    updateFamIds() {
      this.conditions.forEach(condition => {
        condition.fields.famid = this.famid;
        condition.keywords.famid = this.famid;
      });
    },
    loadSmartElement() {
      this.famid = parseInt(this.controller.getValue("se_famid").value); //init Famid
      const se_olValue = this.controller.getValue("se_ol").value;
      if (se_olValue) {
        this.conditionRuleType = se_olValue;
      }

      const operator = this.controller.getValue("se_ols");
      const leftp = this.controller.getValue("se_leftp");
      const fields = this.controller.getValue("se_attrids");
      const funcs = this.controller.getValue("se_funcs");
      const keys = this.controller.getValue("se_keys");
      const rightp = this.controller.getValue("se_rightp");
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
      this.controller.setValue(event.smartFieldId, event.smartFieldValue);
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
          controller: this.controller,
          famid: this.famid,
          initValue: ""
        },
        functions: {
          field: null,
          initValue: ""
        },
        keywords: {
          controller: this.controller,
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
