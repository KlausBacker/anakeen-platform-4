import { Component, Prop, Vue } from "vue-property-decorator";
import AnkSmartForm from "../../../AnkSmartForm";
import GridController from "../../AnkSEGrid.component";
import { ISmartFormConfiguration } from "../../../AnkSmartForm/ISmartForm";
import { default as AnkSmartFormDefinition } from "../../../AnkSmartForm/AnkSmartForm.component";

const FIELD_TYPE_OPERATOR = {
  docid: {
    eq: "docid",
    neq: "docid"
  },
  account: {
    eq: "account",
    neq: "account"
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
          this.config.values.first_grid_filter_value = columnFilter.filters[0].value;
        }
        if (columnFilter.filters.length >= 2) {
          this.config.values.second_grid_filter_operator = columnFilter.filters[1].operator;
          this.config.values.second_grid_filter_value = columnFilter.filters[1].value;
        }
      }
    }
    // apply filters type field
    const firstValueField = this.config.structure[0].content.find(el => el.name === "first_grid_filter_value");
    const secondValueField = this.config.structure[0].content.find(el => el.name === "second_grid_filter_value");
    if (firstValueField) {
      const type = getFilterValueType(this.columnConfig.smartType, this.config.values.first_grid_filter_operator);
      if (!type) {
        firstValueField.display = "none";
      } else {
        firstValueField.type = type;
        firstValueField.display = "write";
      }
    }
    if (secondValueField) {
      const type = getFilterValueType(this.columnConfig.smartType, this.config.values.second_grid_filter_operator);
      if (!type) {
        secondValueField.display = "none";
      } else {
        secondValueField.type = type;
        secondValueField.display = "write";
      }
    }
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
        value: firstValue ? firstValue.value : null
      });
    }
    if (secondValue && (secondValue.value || isUnaryOperator(secondOperator.value))) {
      filterResult.filters.push({
        field: this.field,
        operator: secondOperator.value,
        value: secondValue ? secondValue.value : null
      });
    }
    if (filterResult.filters.length) {
      this.$emit("filter", filterResult);
    } else {
      this.$emit("filter", { field: this.field });
    }
  }

  protected onSmartFieldChange(event, se, sf) {
    const reg = /(first|second)_grid_filter_operator/;
    const matches = sf.id.match(reg);
    if (matches && matches.length > 1) {
      const fieldNumber = matches[1];
      const fieldValueId = `${fieldNumber}_grid_filter_value`;
      const struct = this.config.structure[0].content.find(s => s.name === fieldValueId);
      if (struct) {
        const operatorValue = sf.getValue().value;
        const type = getFilterValueType(this.columnConfig.smartType, operatorValue);
        if (type) {
          struct.type = type;
          struct.display = "write";
        } else {
          struct.display = "none";
        }
        this.config.values[sf.id] = operatorValue;
      }
    }
  }

  protected onReady() {
    // @ts-ignore
    this.$kendo.ui.progress(this.$(this.$refs.wrapper), false);
  }

}
