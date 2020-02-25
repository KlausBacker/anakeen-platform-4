import { Component, Prop, Vue } from "vue-property-decorator";
import AnkSmartForm from "../../../AnkSmartForm";
import GridController from "../../AnkSEGrid.component";
import { ISmartFormConfiguration } from "../../../AnkSmartForm/ISmartForm";
import { default as AnkSmartFormDefinition } from "../../../AnkSmartForm/AnkSmartForm.component";

const FIELD_TYPE_OPERATOR = {
  docid: {
    eq: "docid",
    neq: "docid",
    "eq*": "docid",
    "neq*": "docid"
  },
  account: {
    eq: "account",
    neq: "account",
    "eq*": "account",
    "neq*": "account"
  },
  enum: {
    eq: "enum",
    neq: "enum",
    "eq*": "enum",
    "neq*": "enum"
  },
  date: "date",
  int: "int",
  double: "double",
  money: "money"
};

const isUnaryOperator = operator => operator === "isempty" || operator === "isnotempty";

const getFilterValueType = (columnType, operator, defaultType = "text") => {
  if (isUnaryOperator(operator)) {
    return null;
  }
  if (typeof FIELD_TYPE_OPERATOR[columnType] === "string") {
    return FIELD_TYPE_OPERATOR[columnType];
  } else if (typeof FIELD_TYPE_OPERATOR[columnType] === "object") {
    return FIELD_TYPE_OPERATOR[columnType][operator] || defaultType;
  }
  return defaultType;
};

@Component({
  name: "ank-se-grid-filter",
  components: {
    "ank-smart-form": () => AnkSmartForm
  }
})
export default class GridFilterCell extends Vue {
  @Prop({
    type: String,
    required: true
  })
  public field!: string;

  @Prop({
    required: true
  })
  public title!: string;

  @Prop({
    type: [Boolean, Object],
    required: true
  })
  public sortable!: boolean | object;

  @Prop({
    type: Object,
    required: true
  })
  public columnConfig;

  @Prop({
    type: Object,
    required: true
  })
  public grid!: GridController;

  @Prop({
    type: String,
    default: "and"
  })
  public logic!: string;

  public $refs!: {
    smartForm: AnkSmartFormDefinition;
  };

  public config: ISmartFormConfiguration = {
    structure: [
      {
        name: "grid_filter",
        type: "frame",
        content: [
          {
            label: this.title || this.field,
            name: "first_grid_filter_operator",
            type: "enum",
            enumItems: this.columnConfig
              ? Object.keys(this.columnConfig.filterable.operators.string).map(k => {
                  return {
                    key: k,
                    label: this.columnConfig.filterable.operators.string[k]
                  };
                })
              : []
          },
          {
            label: this.title || this.field,
            name: "first_grid_filter_value",
            type: "text",
            typeFormat: this.columnConfig ? this.columnConfig.relation : ""
            // multiple: this.columnConfig ? this.columnConfig.multipe : false
          },
          {
            label: this.title || this.field,
            display: "read",
            name: "grid_filter_operator_concat",
            type: "text"
          },
          {
            label: this.title || this.field,
            name: "second_grid_filter_operator",
            type: "enum",
            enumItems: this.columnConfig
              ? Object.keys(this.columnConfig.filterable.operators.string).map(k => {
                  return {
                    key: k,
                    label: this.columnConfig.filterable.operators.string[k]
                  };
                })
              : []
          },
          {
            label: this.title || this.field,
            name: "second_grid_filter_value",
            type: "text",
            typeFormat: this.columnConfig ? this.columnConfig.relation : "",
            multiple: this.columnConfig ? this.columnConfig.multipe : false
          }
        ]
      }
    ],
    renderOptions: {
      fields: {
        grid_filter: {
          labelPosition: "none"
        },
        first_grid_filter_operator: {
          displayDeleteButton: false,
          editDisplay: "list"
        },
        first_grid_filter_value: {
          labelPosition: "none"
        },
        second_grid_filter_operator: {
          displayDeleteButton: false,
          labelPosition: "none",
          editDisplay: "list"
        },
        second_grid_filter_value: {
          labelPosition: "none"
        },
        grid_filter_operator_concat: {
          labelPosition: "none"
        }
      }
    },
    values: {
      grid_filter_operator_concat: this.logic,
      first_grid_filter_operator: this.columnConfig
        ? Object.keys(this.columnConfig.filterable.operators.string)[0]
        : "",
      first_grid_filter_value: "",
      second_grid_filter_operator: this.columnConfig
        ? Object.keys(this.columnConfig.filterable.operators.string)[0]
        : "",
      second_grid_filter_value: ""
    }
  };

  public created() {
    if (this.grid && this.grid.currentFilter && this.grid.currentFilter.filters) {
      const columnFilters = this.grid.currentFilter.filters.filter(f => f.field === this.field);
      if (columnFilters && columnFilters.length) {
        const columnFilter = columnFilters[0];
        if (columnFilter.filters.length >= 1) {
          this.config.values.first_grid_filter_operator = columnFilter.filters[0].operator;
          this.config.values.first_grid_filter_value = {
            displayValue: columnFilter.filters[0].displayValue,
            value: columnFilter.filters[0].value
          };
        }
        if (columnFilter.filters.length >= 2) {
          this.config.values.second_grid_filter_operator = columnFilter.filters[1].operator;
          this.config.values.second_grid_filter_value = {
            value: columnFilter.filters[1].value,
            displayValue: columnFilter.filters[1].displayValue
          };
        }
      }
    }
    // apply filters type field
    this.updateValueFieldType("first_grid_filter_value", this.config.values.first_grid_filter_operator);
    this.updateValueFieldType("second_grid_filter_value", this.config.values.second_grid_filter_operator);
  }

  public mounted() {
    // @ts-ignore
    this.$kendo.ui.progress(this.$(this.$refs.wrapper), true);
  }

  public clear() {
    this.$emit("filter", { field: this.field });
  }

  public filter() {
    const firstOperator = this.$refs.smartForm.getValue("first_grid_filter_operator", "current");
    const firstValue = this.$refs.smartForm.getValue("first_grid_filter_value", "current");
    const secondOperator = this.$refs.smartForm.getValue("second_grid_filter_operator", "current");
    const secondValue = this.$refs.smartForm.getValue("second_grid_filter_value", "current");
    const filterResult = {
      field: this.field,
      logic: this.logic,
      filters: []
    };
    if (firstValue && (firstValue.value || isUnaryOperator(firstOperator.value))) {
      filterResult.filters.push({
        field: this.field,
        operator: firstOperator.value,
        value: firstValue ? firstValue.value : null,
        displayValue: firstValue ? firstValue.displayValue : null
      });
    }
    if (secondValue && (secondValue.value || isUnaryOperator(secondOperator.value))) {
      filterResult.filters.push({
        field: this.field,
        operator: secondOperator.value,
        value: secondValue ? secondValue.value : null,
        displayValue: secondValue ? secondValue.displayValue : null
      });
    }
    if (filterResult.filters.length) {
      this.$emit("filter", filterResult);
    } else {
      this.$emit("filter", { field: this.field });
    }
  }

  protected onSmartFieldChange(event, se, sf) {
    if (sf.id === "first_grid_filter_operator" || sf.id === "second_grid_filter_operator") {
      // Smart Form will reload so keep operator and filter values
      ["first", "second"].forEach(item => {
        const fieldValueId = `${item}_grid_filter_value`;
        const fieldOperatorId = `${item}_grid_filter_operator`;
        const operatorValue = this.$refs.smartForm.getValue(fieldOperatorId, "current");
        const fieldValue = this.$refs.smartForm.getValue(fieldValueId, "current");
        this.updateValueFieldType(fieldValueId, operatorValue.value);
        this.config.values[fieldOperatorId] = operatorValue.value;
        this.config.values[fieldValueId] = fieldValue;
      });
    }
  }

  protected onReady() {
    // @ts-ignore
    this.$kendo.ui.progress(this.$(this.$refs.wrapper), false);
  }

  protected updateValueFieldType(fieldValueId, fieldOperator) {
    const valueField = this.config.structure[0].content.find(el => el.name === fieldValueId);
    if (valueField) {
      const type = getFilterValueType(this.columnConfig.smartType, fieldOperator);
      if (!type) {
        valueField.display = "none";
      } else {
        valueField.type = type;
        valueField.display = "write";
      }
    }
  }
}
