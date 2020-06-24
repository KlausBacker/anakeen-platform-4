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
import SmartCriteriaConfigurationLoader from "./SmartCriteriaConfigurationLoader";
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
  private innerConfig: ISmartCriteriaConfiguration = { title: "", defaultStructure: -1, criterias: [] };
  private operatorFieldRegex = /^sc_operator_(\d+)$/;
  private errorStack = [];
  public smartFormConfig: ISmartFormConfiguration = {};
  private smartCriteriaConfigurationLoader: SmartCriteriaConfigurationLoader;
  private smartFormConfigurationBuilder: SmartFormConfigurationBuilder;
  public filterValue: ISmartFilter = {
    kind: SmartCriteriaKind.FIELD,
    field: "",
    operator: {
      key: CriteriaOperator.NONE,
      options: [],
      filterMultiple: false,
      additionalOptions: []
    },
    value: null,
    logic: SmartFilterLogic.AND,
    filters: []
  };
  private loading = true;

  @Watch("config", { immediate: false, deep: true })
  public onConfigChanged(newConfig: ISmartCriteriaConfiguration): void {
    this.innerConfig = JSON.parse(JSON.stringify(newConfig));
    this.smartCriteriaConfigurationLoader = this.getConfigurationLoader(this.innerConfig);
    this.loadConfiguration();
  }

  initSmartCriteria(): void {
    this.innerConfig = JSON.parse(JSON.stringify(this.config));
    this.smartCriteriaConfigurationLoader = this.getConfigurationLoader(this.innerConfig);
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
  }

  initVisibilities(): void {
    for (let i = 0; i < this.smartFormConfig.structure[0].content.length; i++) {
      let value = this.$refs.smartForm.getValue(`sc_operator_${i}`);
      value = value ? value.value : "";
      this.evaluateSmartFieldVisibilities(value, i);
    }

    for (const msg of this.errorStack) {
      this.sendError(msg.message, msg.type);
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
    const ajaxPromises = this.smartCriteriaConfigurationLoader.load();
    this.errorStack = this.smartCriteriaConfigurationLoader.getErrorStack();
    Promise.all(ajaxPromises).then(() => {
      this.buildSmartFormConfig();
      this.$emit("smartCriteriaReady");
    });
  }

  private buildSmartFormConfig(): void {
    const translations = {
      and: `${this.$t("SmartFormConfigurationBuilder.And")}`
    };
    this.smartFormConfigurationBuilder = new SmartFormConfigurationBuilder(
      this.innerConfig,
      translations,
      this.responsiveColumns
    );
    this.smartFormConfigurationBuilder = this.getSmartFormConfigurationBuilder(
      this.innerConfig,
      translations,
      this.responsiveColumns
    );
    this.smartFormConfig = { ...this.smartFormConfigurationBuilder.build() };
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

    this.setSmartFieldVisibility(SmartFormConfigurationBuilder.getOperatorName(index), operatorVisible);
    this.setSmartFieldVisibility(SmartFormConfigurationBuilder.getValueName(index), valueVisible);
    this.setSmartFieldVisibility(SmartFormConfigurationBuilder.getValueBetweenLabelName(index), betweenVisible);
    this.setSmartFieldVisibility(SmartFormConfigurationBuilder.getValueBetweenName(index), betweenVisible);
    this.setSmartFieldVisibility(SmartFormConfigurationBuilder.getValueMultipleName(index), multipleVisible);
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

  public getFilters(): ISmartFilter {
    let filter: ISmartFilter;
    let initFilter = false;
    filter = {
      field: undefined,
      filters: [],
      disabled: true,
      kind: undefined,
      logic: undefined,
      operator: {
        key: CriteriaOperator.NONE,
        options: [],
        filterMultiple: false,
        additionalOptions: []
      },
      value: undefined
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
      operator: {
        key: CriteriaOperator.NONE,
        options: [],
        filterMultiple: false,
        additionalOptions: []
      },
      value: ""
    };

    const smartFormOperatorValue = this.$refs.smartForm.getValue(SmartFormConfigurationBuilder.getOperatorName(index));
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
          this.$refs.smartForm.getValue(SmartFormConfigurationBuilder.getValueName(index)),

          this.$refs.smartForm.getValue(SmartFormConfigurationBuilder.getValueBetweenName(index))
        ]
      : isFilterMultiple
      ? this.$refs.smartForm.getValue(SmartFormConfigurationBuilder.getValueMultipleName(index))
      : this.$refs.smartForm.getValue(SmartFormConfigurationBuilder.getValueName(index));
    let value;

    if (isBetween || isFilterMultiple) {
      value = smartFormValue.map(valObject => (valObject ? valObject.value : null));
    } else {
      value = smartFormValue ? smartFormValue.value : null;
    }

    smartFilter.operator = operator;
    smartFilter.value = value;

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
   * Returns the configuration loader used by the smart criteria based component.
   * If another loader must be used to extend functionnalities, this method must be overriden.
   * @param config the user defined smart criteria configuration.
   */
  protected getConfigurationLoader(config: ISmartCriteriaConfiguration): SmartCriteriaConfigurationLoader {
    return new SmartCriteriaConfigurationLoader(config);
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
}
