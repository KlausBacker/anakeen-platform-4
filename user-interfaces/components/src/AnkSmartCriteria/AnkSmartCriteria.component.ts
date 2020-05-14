import "@progress/kendo-ui/js/kendo.button";
import { Component, Mixins, Prop, Watch } from "vue-property-decorator";
import EventUtilsMixin from "../../mixins/AnkVueComponentMixin/EventUtilsMixin";
import ReadyMixin from "../../mixins/AnkVueComponentMixin/ReadyMixin";
import AnkSmartForm from "../AnkSmartForm";
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

// @ts-ignore
@Component({
  name: "ank-smart-criteria",
  components: {
    "ank-smart-form": () => {
      return AnkSmartForm;
    }
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

  @Watch("config", { immediate: false, deep: true })
  public onConfigChanged(newConfig: ISmartCriteriaConfiguration): void {
    this.innerConfig = JSON.parse(JSON.stringify(newConfig));
    this.smartCriteriaConfigurationLoader = new SmartCriteriaConfigurationLoader(this.innerConfig);
    this.loadConfiguration();
  }

  initSmartCriteria(): void {
    this.innerConfig = JSON.parse(JSON.stringify(this.config));
    this.smartCriteriaConfigurationLoader = new SmartCriteriaConfigurationLoader(this.innerConfig);
    this.loadConfiguration();
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
    }
  }

  initVisibilities(): void {
    for (let i = 0; i < this.smartFormConfig.structure[0].content.length; i++) {
      // @ts-ignore
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
    const ajaxPromises = this.smartCriteriaConfigurationLoader.load();
    this.errorStack = this.smartCriteriaConfigurationLoader.getErrorStack();
    // @ts-ignore
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
    this.smartFormConfig = { ...this.smartFormConfigurationBuilder.build() };
    this.errorStack = this.errorStack.concat(this.smartFormConfigurationBuilder.getErrorStack());
  }

  private static getSmartFieldDom(smartFieldId: string): any {
    return $(`.dcpAttribute[data-attrid=${smartFieldId}]`);
  }

  private static setSmartFieldVisibility(smartFieldId: string, visible: boolean): void {
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
    const operatorData: ICriteriaConfigurationOperator = SmartCriteriaUtils.getOperatorData(operator, criteria);
    if (!operatorData) {
      valueVisible = false;
      multipleVisible = false;
      betweenVisible = false;
    } else {
      betweenVisible = operatorData.isBetween;
      multipleVisible = operatorData.filterMultiple;
      valueVisible = operatorData.acceptValues;

      const isFulltext = criteria.kind === SmartCriteriaKind.FULLTEXT;
      if (!isFulltext && !operatorData.acceptValues) {
        valueVisible = false;
        multipleVisible = false;
      }
      if (multipleVisible) {
        valueVisible = false;
      }
    }

    AnkSmartCriteria.setSmartFieldVisibility(SmartFormConfigurationBuilder.getValueName(index), valueVisible);
    AnkSmartCriteria.setSmartFieldVisibility(
      SmartFormConfigurationBuilder.getValueBetweenLabelName(index),
      betweenVisible
    );
    AnkSmartCriteria.setSmartFieldVisibility(SmartFormConfigurationBuilder.getValueBetweenName(index), betweenVisible);
    AnkSmartCriteria.setSmartFieldVisibility(
      SmartFormConfigurationBuilder.getValueMultipleName(index),
      multipleVisible
    );
  }

  static getOperator(operatorKey: string, operators: Array<ISmartFormFieldEnumConfig>): ISmartFormFieldEnumConfig {
    for (const operator of operators) {
      if (operator.key === operatorKey) {
        return operator;
      }
    }
  }

  private showError(message: string, type = "error"): void {
    // @ts-ignore
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
    filter = {
      field: undefined,
      filters: [],
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
      if (i != 0) {
        filter.filters.push(this.computeFilterValue(criteria, i));
      } else {
        filter = this.computeFilterValue(criteria, i);
      }
    }
    return filter;
  }

  private computeFilterValue(criteria: IConfigurationCriteria, index: number): ISmartFilter {
    const smartFilter: ISmartFilter = {
      field: undefined,
      filters: [],
      kind: criteria.kind,
      logic: SmartFilterLogic.AND,
      operator: {
        key: CriteriaOperator.NONE,
        options: [],
        filterMultiple: false,
        additionalOptions: []
      },
      value: ""
    };

    // @ts-ignore
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
    // @ts-ignore
    const smartFormValue = isBetween ? [this.$refs.smartForm.getValue(SmartFormConfigurationBuilder.getValueName(index)), this.$refs.smartForm.getValue(SmartFormConfigurationBuilder.getValueBetweenName(index))] : (isFilterMultiple ? this.$refs.smartForm.getValue(SmartFormConfigurationBuilder.getValueMultipleName(index)) : this.$refs.smartForm.getValue(SmartFormConfigurationBuilder.getValueName(index)));
    let value;

    if (isBetween || isFilterMultiple) {
      value = smartFormValue.map(valObject => (valObject ? valObject.value : null));
    } else {
      value = smartFormValue ? smartFormValue.value : null;
    }

    smartFilter.operator = operator;
    smartFilter.value = value ? value : "";
    if (criteria.kind !== SmartCriteriaKind.FULLTEXT) {
      smartFilter.field = criteria.field;
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

  // @ts-ignore
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
          case SmartCriteriaKind.FULLTEXT:
            if (criteria.label === id) {
              return index;
            }
            break;
          default:
            break;
        }
      }
      return errorIndex;
    });
  }
}
