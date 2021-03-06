import { Component, Prop, Mixins, Watch } from "vue-property-decorator";
import AnkSmartForm from "../../../AnkSmartForm";
import { ISmartFormConfiguration } from "../../../AnkSmartForm/ISmartForm";
import { default as AnkSmartFormDefinition } from "../../../AnkSmartForm/AnkSmartForm.component";
import I18nMixin from "../../../../mixins/AnkVueComponentMixin/I18nMixin";
import AnkSmartElementGrid from "../../AnkSEGrid.component";
import $ from "jquery";

type OperatorType = "docid" | "account" | "enum" | "timestamp" | "time" | "date" | "int" | "double" | "money" | "text";

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
  timestamp: "timestamp",
  time: "time",
  date: "date",
  int: "int",
  double: "double",
  money: "money"
};

const isUnaryOperator = (operator): boolean => operator === "isempty" || operator === "isnotempty";
const isAutocompleteType = (type): boolean => type === "text" || type === "docid" || type === "account";

const getFilterValueType = (columnType, operator, defaultType: OperatorType = "text"): OperatorType | null => {
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
    "ank-smart-form": (): Promise<unknown> => AnkSmartForm
  }
})
export default class GridFilterCell extends Mixins(I18nMixin) {
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
  public grid!: AnkSmartElementGrid;

  @Prop({
    type: String,
    default: "and"
  })
  public logic!: string;

  @Watch("loading")
  protected onLoadingChange(newValue) {
    kendo.ui.progress($(this.$refs.wrapper), !!newValue);
  }

  public $refs!: {
    smartForm: AnkSmartFormDefinition;
    wrapper: HTMLElement;
  };

  public loading = false;
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
              ? Object.keys(this.columnConfig.filterable.operators.string)
                  // Constraint to requested operators only
                  .filter(k => {
                    if (
                      this.grid.filterable &&
                      this.grid.filterable[this.field] &&
                      Array.isArray(this.grid.filterable[this.field].activeOperators) &&
                      this.grid.filterable[this.field].activeOperators.length
                    ) {
                      return this.grid.filterable[this.field].activeOperators.indexOf(k) > -1;
                    }
                    // By default all operators are available
                    return true;
                  })
                  .map(k => {
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
            display:
              this.grid.filterable && this.grid.filterable[this.field] && this.grid.filterable[this.field].singleFilter
                ? "none"
                : "read",
            name: "grid_filter_operator_concat",
            type: "text"
          },
          {
            label: this.title || this.field,
            name: "second_grid_filter_operator",
            display:
              this.grid.filterable && this.grid.filterable[this.field] && this.grid.filterable[this.field].singleFilter
                ? "none"
                : "write",
            type: "enum",
            enumItems: this.columnConfig
              ? Object.keys(this.columnConfig.filterable.operators.string)
                  // Constraint to requested operators only
                  .filter(k => {
                    if (
                      this.grid.filterable &&
                      this.grid.filterable[this.field] &&
                      Array.isArray(this.grid.filterable[this.field].activeOperators) &&
                      this.grid.filterable[this.field].activeOperators.length
                    ) {
                      return this.grid.filterable[this.field].activeOperators.indexOf(k) > -1;
                    }
                    // By default all operators are available
                    return true;
                  })
                  .map(k => {
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
            display:
              this.grid.filterable && this.grid.filterable[this.field] && this.grid.filterable[this.field].singleFilter
                ? "none"
                : "write",
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
      first_grid_filter_operator: "",
      first_grid_filter_value: null,
      second_grid_filter_operator: "",
      second_grid_filter_value: null
    }
  };

  public created(): void {
    if (this.grid && this.grid.currentFilter && this.grid.currentFilter.filters) {
      const columnFilters: any[] = this.grid.currentFilter.filters.filter((f: any) => f.field === this.field);
      if (columnFilters && columnFilters.length) {
        const columnFilter = columnFilters[0];
        if ((!columnFilter.filters && columnFilter.operator) || columnFilter.filters.length >= 1) {
          const operator = columnFilter.operator || columnFilter.filters[0].operator;
          const value = columnFilter.value || columnFilter.filters[0].value;
          const displayValue = columnFilter.displayValue || columnFilter.filters[0].displayValue;
          this.config.values.first_grid_filter_operator = operator;
          this.config.values.first_grid_filter_value = {
            displayValue,
            value
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

      const columnActualFilters = this.grid.filter.filters.find(val => val.field === this.field);
      if(columnActualFilters) {
        this.config.values.first_grid_filter_operator = columnActualFilters.filters[0]
          ? columnActualFilters.filters[0].operator
          : Object.keys(this.columnConfig.filterable.operators.string)[0];
        this.config.values.second_grid_filter_operator = columnActualFilters.filters[1]
        ? columnActualFilters.filters[1].operator
        : Object.keys(this.columnConfig.filterable.operators.string)[0];
      } else {
        let defaultOperatorValue = "";
        if (
          this.grid.filterable &&
          this.grid.filterable[this.field] &&
          this.grid.filterable[this.field].defaultSelectedOperator
        ) {
          defaultOperatorValue = this.grid.filterable[this.field].defaultSelectedOperator;
        }

        if (
          this.grid.filterable &&
          this.grid.filterable[this.field] &&
          this.grid.filterable[this.field].activeOperators &&
          this.grid.filterable[this.field].activeOperators.length
        ) {
          const activeOperators = this.grid.filterable[this.field].activeOperators;
          if (!defaultOperatorValue) {
          defaultOperatorValue = activeOperators[0];
          } else if (activeOperators.indexOf(defaultOperatorValue) === -1) {
          console.error('[Smart Element Grid] Operator : "%s" not exist', defaultOperatorValue);
            defaultOperatorValue = activeOperators[0];
          }
        } else if (Object.keys(this.columnConfig.filterable.operators.string)[0]) {
          defaultOperatorValue = Object.keys(this.columnConfig.filterable.operators.string)[0];
        }

        if (this.columnConfig.filterable.operators.string[defaultOperatorValue]) {
          this.config.values.first_grid_filter_operator = defaultOperatorValue;
          this.config.values.second_grid_filter_operator = defaultOperatorValue;
        } else {
          console.error('[Smart Element Grid] Operator : "%s" not exist', defaultOperatorValue);
        }
      }
    }
    this.config.values.grid_filter_operator_concat = {
      value: this.logic,
      displayValue: this.$t("gridFilter.And") as string
    };
    // apply filters type field
    this.updateValueFieldType("first_grid_filter_value", this.config.values.first_grid_filter_operator);
    if (!this.singleFilter) {
      this.updateValueFieldType("second_grid_filter_value", this.config.values.second_grid_filter_operator);
    }
  }

  public mounted(): void {
    this.loading = true;
  }

  public get singleFilter(): boolean {
    return this.grid.filterable && this.grid.filterable[this.field] && this.grid.filterable[this.field].singleFilter;
  }

  public clear(): void {
    this.$emit("filter", { field: this.field });
  }

  public filter(): void {
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

  protected onSmartFieldChange(event, se, sf): void {
    if (sf.id === "first_grid_filter_operator" || sf.id === "second_grid_filter_operator") {
      const fields = ["first"];
      if (!this.singleFilter) {
        fields.push("second");
      }
      // Smart Form will reload so keep operator and filter values
      fields.forEach(item => {
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

  protected onReady(): void {
    this.loading = false;
  }

  protected updateValueFieldType(fieldValueId, fieldOperator): void {
    const valueField = this.config.structure[0].content.find(el => el.name === fieldValueId);
    if (valueField) {
      const type = getFilterValueType(this.columnConfig.smartType, fieldOperator);
      if (!type) {
        valueField.display = "none";
      } else {
        valueField.type = type;
        valueField.display = "write";
        this.setAutocompleteField(valueField, type, fieldValueId, fieldOperator);
      }
    }
  }

  protected setAutocompleteField(valueField, type, fieldValueId, fieldOperatorValue) {
    if (this.field === "state" && (fieldOperatorValue === "eq" || fieldOperatorValue === "neq")) {
      // @ts-ignore
      valueField.autocomplete = {
        url: `/api/v2/grid/filter/${this.grid.collection}/state/autocomplete`,
        outputs: {
          [fieldValueId]: "stateValue"
        }
      };
      // set docid to enable value/displayValue behavior, enum type would be a breaking change
      this.$set(valueField, "type", "docid");
    } else if (
      type === "enum" &&
      (fieldOperatorValue === "eq" ||
        fieldOperatorValue === "neq" ||
        fieldOperatorValue === "eq*" ||
        fieldOperatorValue === "neq*")
    ) {
      if (this.columnConfig && this.columnConfig.relation) {
        // @ts-ignore
        this.config.renderOptions.fields[fieldValueId].useSourceUri = true;
        // @ts-ignore
        valueField.enumUrl = `/api/v2/enumerates/${this.columnConfig.relation}/`;
      }
    } else {
      // @ts-ignore
      delete valueField.autocomplete;
    }
    if (
      isAutocompleteType(type) &&
      this.grid.filterable &&
      this.grid.filterable[this.field] &&
      this.grid.filterable[this.field].autocomplete
    ) {
      // @ts-ignore
      valueField.autocomplete = this.grid.filterable[this.field].autocomplete;
    }
  }
}
