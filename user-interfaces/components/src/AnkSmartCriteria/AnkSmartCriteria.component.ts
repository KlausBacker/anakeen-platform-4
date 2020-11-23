import "@progress/kendo-ui/js/kendo.button";
import { Component, Mixins, Prop, Watch } from "vue-property-decorator";
import EventUtilsMixin from "../../mixins/AnkVueComponentMixin/EventUtilsMixin";
import ReadyMixin from "../../mixins/AnkVueComponentMixin/ReadyMixin";
import AnkSmartForm from "../AnkSmartForm";
import AnkLoading from "../AnkLoading";
import ISmartFilter from "./Types/ISmartFilter";
import IConfigurationCriteria, { ICriteriaConfigurationOperator } from "./Types/IConfigurationCriteria";
import { SmartCriteriaKind } from "./Types/SmartCriteriaKind";
import ISmartCriteriaConfiguration from "./Types/ISmartCriteriaConfiguration";
import { ISmartFormConfiguration, ISmartFormFieldEnumConfig } from "../AnkSmartForm/ISmartForm";
import { SmartFilterLogic } from "./Types/SmartFilterLogic";
import IFilter from "./Types/IFilter";
import SmartFormConfigurationBuilder from "./SmartFormConfigurationBuilder";
import { CriteriaOperator, ICriteriaOperator } from "./Types/ICriteriaOperator";
import SmartCriteriaUtils from "./SmartCriteriaUtils";
import AnkI18NMixin from "../../mixins/AnkVueComponentMixin/I18nMixin";
import $ from "jquery";
import SmartCriteriaEvent from "./Types/SmartCriteriaEvent";
import { default as AnkSmartFormDefinition } from "../AnkSmartForm/AnkSmartForm.component";

@Component({
  name: "ank-smart-criteria",
  components: {
    "ank-smart-form": (): Promise<unknown> => {
      return AnkSmartForm;
    },
    "ank-loading": AnkLoading
  }
})
export default class AnkSmartCriteria extends Mixins(EventUtilsMixin, ReadyMixin, AnkI18NMixin) {
  @Prop(Object)
  public config: ISmartCriteriaConfiguration;
  @Prop({
    default: false
  })
  public submit;
  @Prop({
    default: true,
    type: Boolean
  })
  public force;
  @Prop({
    default: () => [],
    type: Array
  })
  public responsiveColumns;

  public $refs!: {
    smartForm: AnkSmartFormDefinition;
  };
  private mountedDone = false;
  private standalone = false;
  private innerConfig: ISmartCriteriaConfiguration = { title: "", defaultStructure: -1, criterias: [] };
  private idMap: Array<string> = [];
  private operatorFieldRegex = /^sc_operator_(\d+)$/;
  private errorStack = [];
  public smartFormConfig: ISmartFormConfiguration = {};
  private smartFormConfigurationBuilder: SmartFormConfigurationBuilder;
  public filterValue: ISmartFilter = {
    kind: SmartCriteriaKind.FIELD,
    field: "",
    id: "",
    operator: {
      key: CriteriaOperator.NONE,
      options: [],
      filterMultiple: false,
      additionalOptions: []
    },
    value: null,
    displayValue: null,
    logic: SmartFilterLogic.AND,
    filters: []
  };
  private loading = true;
  private fieldTranslationMap = {};

  @Watch("config", { immediate: false, deep: true })
  public onConfigChanged(newConfig: ISmartCriteriaConfiguration): void {
    this.errorStack = [];
    this.innerConfig = JSON.parse(JSON.stringify(newConfig));
    this.loadConfiguration();
  }

  initSmartCriteria(): void {
    this.errorStack = [];
    this.innerConfig = JSON.parse(JSON.stringify(this.config));
    if (this.mountedDone === false) {
      this.$once("hook:mounted", this.loadConfiguration);
    } else {
      this.loadConfiguration();
    }
  }

  onSmartFieldChange(event, smartElement, smartField, values): void {
    //Emit smartCriteriaChange event
    this.$emit("smartCriteriaChange", event, smartElement, smartField, values);
    const operatorMatch = smartField.id.match(this.operatorFieldRegex);
    if (operatorMatch) {
      const index = operatorMatch[1];
      const operator = values.current.value;
      this.evaluateSmartFieldVisibilities(operator, index);
    }
  }

  onBeforeSave(...args): void {
    this.$emit("beforeSave", args);
  }

  onSave(...args): void {
    this.$emit("save", args);
  }

  onSmartCriteriaReady(event, smartElement, smartField): void {
    if (smartField.id === "sc_tab") {
      $(".smart-criteria-input-button", this.$el).kendoButton();
      this.initVisibilities();
      this.loading = false;
    }
    if (!this.innerConfig.title) {
      $("header[class*='dcpDocument__header']", this.$el).remove();
    }
  }

  initVisibilities(): void {
    for (let i = 0; i < this.smartFormConfig.structure[0].content.length; i++) {
      let value = this.$refs.smartForm.getValue(`sc_operator_${i}`);
      value = value ? value.value : "";
      this.evaluateSmartFieldVisibilities(value, i);
    }

    while (this.errorStack.length) {
      const error = this.errorStack.pop();
      this.sendError(error.message, error.type);
    }
  }

  /**
   * Add a flag for the mounted event
   * used by the i18n, we cannot use i18n before this event
   */
  mounted(): void {
    this.mountedDone = true;
  }

  onSubmitButtonClick(): void {
    const submitEvent = new SmartCriteriaEvent();
    this.$emit("smartCriteriaSubmitClick", submitEvent);
    if (!submitEvent.isDefaultPrevented()) {
      this._triggerValidated();
    }
  }

  private onEnterKeyupEvent(): void {
    const keyEvent = new SmartCriteriaEvent();
    this.$emit("smartCriteriaEnterKey", keyEvent);
    if (!keyEvent.isDefaultPrevented()) {
      this._triggerValidated();
    }
  }

  private _triggerValidated(): void {
    this.$emit("smartCriteriaValidated");
  }

  private loadConfiguration(): void {
    if (this.mountedDone === false) {
      return;
    }
    this.loading = true;
    if (!this.innerConfig.standalone) {
      $.ajax({
        url: this.getLoaderUrl(),
        data: this.innerConfig
      })
        .done(response => {
          this.innerConfig = response.data.configuration;
          this.idMap = response.data.idMap;
          this.errorStack = this.errorStack.concat(response.data.errors);
          this.checkConfig();
          this.buildSmartFormConfig();
          this.$emit("smartCriteriaReady");
        })
        .fail(() => {
          this.showError("Something went wrong while getting the full smart criteria configuration from the server.");
        });
    } else {
      this.checkConfig();
      this.buildSmartFormConfig();
      this.$emit("smartCriteriaReady");
    }
  }

  private buildSmartFormConfig(): void {
    const translations = {
      and: `${this.$t("SmartFormConfigurationBuilder.And")}`
    };
    this.smartFormConfigurationBuilder = this.getSmartFormConfigurationBuilder(
      JSON.parse(JSON.stringify(this.innerConfig)),
      translations,
      this.responsiveColumns
    );
    this.smartFormConfig = { ...this.smartFormConfigurationBuilder.build() };
    this.fieldTranslationMap = { ...this.smartFormConfigurationBuilder.fieldTranslationMap };
    this.errorStack = this.errorStack.concat(this.smartFormConfigurationBuilder.getErrorStack());
  }

  private getSmartFieldDom(smartFieldId: string): JQuery {
    return $(`.dcpAttribute[data-attrid=${smartFieldId}]`, this.$el);
  }

  private setSmartFieldVisibility(smartFieldId: string, visible: boolean): void {
    const dom = this.getSmartFieldDom(smartFieldId);
    if (visible) {
      dom.removeClass("smart-criteria-value-hidden");
    } else {
      dom.addClass("smart-criteria-value-hidden");
    }
  }

  private evaluateSmartFieldVisibilities(operator: string, index: number): void {
    let valueVisible;
    let multipleVisible;
    let betweenVisible;

    const criteria = this.innerConfig.criterias[index];
    const operatorVisible = criteria.modifiableOperator;
    const operatorData: ICriteriaConfigurationOperator = SmartCriteriaUtils.getOperatorData(operator, criteria);
    if (!operatorData) {
      valueVisible = false;
      multipleVisible = false;
      betweenVisible = false;
    } else {
      betweenVisible = operatorData.isBetween;
      multipleVisible = operatorData.filterMultiple;
      valueVisible = operatorData.acceptValues;

      const isStandardKind = SmartCriteriaUtils.isStandardKind(criteria.kind);
      if (!isStandardKind && !operatorData.acceptValues) {
        valueVisible = false;
        multipleVisible = false;
      }
      if (multipleVisible) {
        valueVisible = false;
      }
    }

    this.setSmartFieldVisibility(SmartCriteriaUtils.getOperatorName(index), operatorVisible);
    this.setSmartFieldVisibility(SmartCriteriaUtils.getValueName(index), valueVisible);
    this.setSmartFieldVisibility(SmartCriteriaUtils.getValueBetweenLabelName(index), betweenVisible);
    this.setSmartFieldVisibility(SmartCriteriaUtils.getValueBetweenName(index), betweenVisible);
    this.setSmartFieldVisibility(SmartCriteriaUtils.getValueMultipleName(index), multipleVisible);
  }

  static getOperator(operatorKey: string, operators: Array<ISmartFormFieldEnumConfig>): ISmartFormFieldEnumConfig {
    for (const operator of operators) {
      if (operator.key === operatorKey) {
        return operator;
      }
    }
  }

  private showError(message: string, type = "error"): void {
    this.$refs.smartForm.showMessage({
      type,
      message
    });
  }

  private sendError(message: string, type = "error"): void {
    // emit smartCriteriaError event
    this.$emit("smartCriteriaError", message);
    this.showError(message, type);
  }

  public loadValues(filterValues: ISmartFilter) {
    const filters = SmartCriteriaUtils.flattenFilterValues(filterValues);

    filters.forEach(filter => {
      const position = SmartCriteriaUtils.getPositionIdMap(this.idMap, filter.id);

      this.$refs.smartForm.setValue(SmartCriteriaUtils.getOperatorName(position), {
        value: filter.operator.key
      });
      if (filter.operator.filterMultiple && Array.isArray(filter.value)) {
        this.$refs.smartForm.setValue(
          SmartCriteriaUtils.getValueMultipleName(position),
          filter.value.map((filterValue, index) => {
            return { value: filterValue, displayValue: filter.displayValue[index] };
          })
        );
      } else if (filter.isBetween && Array.isArray(filter.value)) {
        this.$refs.smartForm.setValue(SmartCriteriaUtils.getValueName(position), {
          value: filter.value[0]
        });
        this.$refs.smartForm.setValue(SmartCriteriaUtils.getValueBetweenName(position), {
          value: filter.value[1]
        });
      } else if (typeof filter.value != "undefined") {
        this.$refs.smartForm.setValue(SmartCriteriaUtils.getValueName(position), {
          value: filter.value,
          displayValue: filter.displayValue
        });
      }
    });
  }

  public getFilters(): ISmartFilter {
    let filter: ISmartFilter;
    let initFilter = false;
    filter = {
      field: undefined,
      filters: [],
      disabled: true,
      kind: undefined,
      logic: undefined,
      id: undefined,
      operator: {
        key: CriteriaOperator.NONE,
        options: [],
        filterMultiple: false,
        additionalOptions: []
      },
      value: undefined,
      displayValue: undefined
    };
    for (let i = 0; i < this.innerConfig.criterias.length; i++) {
      const criteria = this.innerConfig.criterias[i];
      const computeFilter = this.computeFilterValue(criteria, i);
      if (computeFilter.disabled !== true) {
        if (initFilter === true) {
          filter.filters.push(computeFilter);
        } else {
          initFilter = true;
          filter = computeFilter;
        }
      }
    }
    return filter;
  }

  private computeFilterValue(criteria: IConfigurationCriteria, index: number): ISmartFilter {
    const smartFilter: ISmartFilter = {
      field: undefined,
      filters: [],
      kind: criteria.kind,
      disabled: false,
      logic: SmartFilterLogic.AND,
      id: undefined,
      isBetween: false,
      operator: {
        key: CriteriaOperator.NONE,
        options: [],
        filterMultiple: false,
        additionalOptions: []
      },
      value: "",
      displayValue: ""
    };

    const smartFormOperatorValue = this.$refs.smartForm.getValue(SmartCriteriaUtils.getOperatorName(index));
    const operatorString = smartFormOperatorValue ? smartFormOperatorValue.value : "";
    const operatorData: ICriteriaConfigurationOperator = SmartCriteriaUtils.getOperatorData(operatorString, criteria);
    const operator: ICriteriaOperator = {
      key: operatorData ? operatorData.key : CriteriaOperator.NONE,
      options: operatorData ? operatorData.options : [],
      filterMultiple: operatorData.filterMultiple,
      additionalOptions: []
    };

    const isBetween = operatorData.isBetween;
    const isFilterMultiple = operatorData.filterMultiple;
    const smartFormValue = isBetween
      ? [
          this.$refs.smartForm.getValue(SmartCriteriaUtils.getValueName(index)),

          this.$refs.smartForm.getValue(SmartCriteriaUtils.getValueBetweenName(index))
        ]
      : isFilterMultiple
      ? this.$refs.smartForm.getValue(SmartCriteriaUtils.getValueMultipleName(index))
      : this.$refs.smartForm.getValue(SmartCriteriaUtils.getValueName(index));
    let value;
    let displayValue;

    if (isBetween || isFilterMultiple) {
      value = smartFormValue.map(valObject => (valObject ? valObject.value : null));
      displayValue = smartFormValue.map(valObject => (valObject ? valObject.displayValue : null));
    } else {
      value = smartFormValue ? smartFormValue.value : null;
      displayValue = smartFormValue ? smartFormValue.displayValue : null;
    }
    smartFilter.isBetween = isBetween;
    smartFilter.operator = operator;
    smartFilter.value = value;
    smartFilter.displayValue = displayValue;

    if (operatorData.acceptValues === true && value === null) {
      smartFilter.disabled = true;
    }

    switch (criteria.kind) {
      case SmartCriteriaKind.FIELD:
      case SmartCriteriaKind.PROPERTY:
      case SmartCriteriaKind.VIRTUAL:
        smartFilter.field = criteria.field;
        break;
      default:
        this.customFilterValueAdditionalProcessing(smartFilter, criteria);
        break;
    }
    smartFilter.id = criteria.id;
    return smartFilter;
  }

  public getSmartCriteriaForm(): Vue | Element | Vue[] | Element[] {
    return this.$refs.smartForm;
  }

  public getFilterValue(id: string | number): IFilter {
    let index = id;
    if (typeof id !== "number") {
      index = this.getIndex(id);
    }
    // eslint-disable-next-line @typescript-eslint/consistent-type-assertions
    return this.computeFilterValue(this.innerConfig.criterias[index], <number>index);
  }

  public beforeDestroy(): void {
    // Remove smart form controller on vue component destroy
    this.$refs.smartForm.tryToDestroy({ testDirty: false });
  }

  private getIndex(id: string): number {
    const errorIndex = -1;
    this.innerConfig.criterias.forEach((criteria, index) => {
      for (const criteria of this.innerConfig.criterias) {
        switch (criteria.kind) {
          case SmartCriteriaKind.FIELD:
          case SmartCriteriaKind.PROPERTY:
          case SmartCriteriaKind.VIRTUAL:
            if (criteria.field === id) {
              return index;
            }
            break;
          default:
            if (criteria.label === id) {
              return index;
            }
            break;
        }
      }
      return errorIndex;
    });
    return 0;
  }

  /**
   * Returns the smart form configuration builder used by the smart criteria based component.
   * If another loader must be used to extend functionnalities, this method must be overriden.
   * @param innerConfig the smart criteria configuration
   * @param translations the translations
   * @param responsiveColumns the responsive columns prop value
   */
  protected getSmartFormConfigurationBuilder(
    innerConfig: ISmartCriteriaConfiguration,
    translations: { and: string },
    responsiveColumns: any
  ): SmartFormConfigurationBuilder {
    return new SmartFormConfigurationBuilder(innerConfig, translations, responsiveColumns);
  }

  /**
   * Modifies in place the smart filter value before returning it.
   * For additional functionalities, this method must be overriden.
   * @param smartFilter the initial smart filter value
   * @param criteria the current criteria value
   */
  // eslint-disable-next-line @typescript-eslint/no-empty-function
  protected customFilterValueAdditionalProcessing(smartFilter: ISmartFilter, criteria: IConfigurationCriteria): void {}

  protected getLoaderUrl(): string {
    return "/api/v2/smartcriteria/loadconfiguration";
  }

  private onSmartFieldHelperSearch(event, smartElement, smartField, options) {
    const attributes = options.data.attributes;
    const autocomplete = smartField._attributeModel.attributes.autocomplete;
    if (autocomplete && autocomplete.inputs) {
      const inputsResult = {};
      Object.keys(autocomplete.inputs).forEach(key => {
        const referencedIndex = parseInt(key.substr(-1, 1));
        const sfOperatorValue = this.$refs.smartForm.getValue(SmartCriteriaUtils.getOperatorName(referencedIndex));
        const referencedCriteria = this.innerConfig.criterias[referencedIndex];
        const operatorString = sfOperatorValue ? sfOperatorValue.value : "";
        const referencedOperatorData: ICriteriaConfigurationOperator = SmartCriteriaUtils.getOperatorData(
          operatorString,
          referencedCriteria
        );
        const operatorMultiple = referencedOperatorData.filterMultiple;
        const multipleKey = operatorMultiple ? "multiple" : "single";
        const referencedField = referencedCriteria.field;
        if (this.fieldTranslationMap[referencedField] && this.fieldTranslationMap[referencedField][multipleKey]) {
          inputsResult[this.fieldTranslationMap[referencedField][multipleKey]] = autocomplete.inputs[key];
        } else {
          inputsResult[key] = autocomplete.inputs[key];
        }
      });
      autocomplete.inputs = inputsResult;
    }
  }

  private checkConfig(): void {
    const criterias = this.innerConfig.criterias;
    criterias.forEach(this.checkCriteria);
  }

  private checkCriteria(criteria: IConfigurationCriteria, index: number): void {
    if (!criteria.kind) {
      this.errorStack.push({ message: `Missing 'kind' parameter in criteria ${index}` });
    }

    if (!criteria.field) {
      this.errorStack.push({ message: `Missing 'field' parameter in criteria ${index}` });
    }

    if (criteria.kind === SmartCriteriaKind.FIELD) {
      if (!criteria.structure) {
        this.errorStack.push({ message: `Missing 'structure' parameter in criteria ${index}` });
      }
    }

    if (!criteria.type) {
      this.errorStack.push({ message: `Missing 'type' parameter in criteria ${index}` });
    }

    if (!criteria.label) {
      this.errorStack.push({
        message: `Missing 'type' parameter in criteria ${index}`,
        type: "warning"
      });
    }

    if (!criteria.operators || !criteria.operators.length) {
      this.errorStack.push({
        message: `Missing 'operators' parameter in criteria ${index}`,
        type: "warning"
      });
    }

    for (const operator of criteria.operators) {
      if (!operator.key) {
        this.errorStack.push({ message: `Missing 'key' parameter for operator configuration in criteria ${index}` });
      }

      if (!operator.label) {
        this.errorStack.push({
          message: `Missing 'label' parameter for operator '${operator.key}' in criteria ${index}`,
          type: "warning"
        });
      }
    }
  }
}
