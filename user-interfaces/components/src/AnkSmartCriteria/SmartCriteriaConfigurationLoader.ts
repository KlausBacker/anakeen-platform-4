import ISmartCriteriaConfiguration from "./Types/ISmartCriteriaConfiguration";
import { SmartCriteriaKind } from "./Types/SmartCriteriaKind";
import IConfigurationCriteria from "./Types/IConfigurationCriteria";
import SmartCriteriaUtils from "./SmartCriteriaUtils";
import $ from "jquery";
import SmartCriteriaRequest from "./Types/SmartCriteriaRequest";

export default class {
  configuration: ISmartCriteriaConfiguration;
  ajaxRequests: Array<SmartCriteriaRequest>;
  ajaxPromises: Array<Promise<any>>;
  errorStack: Array<any>;

  constructor(smartCriteriaConfiguration: ISmartCriteriaConfiguration) {
    this.configuration = smartCriteriaConfiguration;
    this.ajaxRequests = [];
    this.errorStack = [];
  }

  load(): Array<Promise<any>> {
    this.initializeConfiguration(this.configuration);
    for (const criteria of this.configuration.criterias) {
      switch (criteria.kind) {
        case SmartCriteriaKind.FIELD:
          this.prepareFieldRequest(criteria);
          break;
        case SmartCriteriaKind.PROPERTY:
          this.preparePropertyRequest(criteria);
          break;
        case SmartCriteriaKind.VIRTUAL:
          this.prepareVirtualRequest(criteria);
          break;
        default:
          this.prepareCustomRequest(criteria);
          break;
      }
    }
    this.ajaxPromises = this.ajaxRequests.map(
      ajaxInfo =>
        new Promise((resolve, reject) =>
          $.ajax({ url: ajaxInfo.url, data: ajaxInfo.data })
            .done(response => {
              ajaxInfo.done(response);
              resolve();
            })
            .fail(err => {
              ajaxInfo.fail(err);
              reject();
            })
        )
    );
    return this.ajaxPromises;
  }

  private stackError(message: string, type = "error"): void {
    this.errorStack.push({ message, type });
  }

  public getErrorStack(): Array<any> {
    return [...this.errorStack];
  }

  private prepareFieldRequest(criteria: IConfigurationCriteria): void {
    if (!criteria.field) {
      this.stackError(`Error: option 'field' not found in configuration`);
    }
    if (!criteria.structure) {
      if (this.configuration.defaultStructure) {
        criteria.structure = this.configuration.defaultStructure;
      } else {
        this.stackError(`Error: option 'structure' not found in configuration for criteria '${criteria.field}'`);
      }
    }

    if (!criteria.operators) {
      criteria.operators = [];
    }

    this.ajaxRequests.push({
      url: `/api/v2/searchcriteria/${criteria.structure}/${criteria.field}`,
      done: response => this.processFieldResponse(criteria, response),
      fail: err => this.stackError(`Error in request : ${err}`)
    });
  }

  private preparePropertyRequest(criteria: IConfigurationCriteria): void {
    if (!criteria.operators) {
      criteria.operators = [];
    }
    const data: any = {};
    if (criteria.field === "title") {
      criteria.type = "text";
    } else if (criteria.field === "state") {
      criteria.type = "enum";
      if (!criteria.stateList) {
        if (criteria.stateWorkflow) {
          data.workflow = criteria.stateWorkflow;
        } else {
          let structure: string | number = criteria.stateStructure;
          if (!structure) {
            structure = this.configuration.defaultStructure;
          }
          if (structure) {
            data.structure = structure;
          }
        }
      } else {
        criteria.enumItems = [...criteria.stateList];
        data.statelist = true;
      }
    }
    this.ajaxRequests.push({
      url: `/api/v2/searchcriteria/property/${criteria.field}`,
      data,
      done: response => this.processPropertyResponse(criteria, response),
      fail: err => this.stackError(`Error in request : ${err}`)
    });
  }

  private prepareVirtualRequest(criteria: IConfigurationCriteria): void {
    if (!criteria.operators) {
      criteria.operators = [];
    }
    criteria.label = criteria.label ? criteria.label : criteria.field;
    this.ajaxRequests.push({
      url: `/api/v2/searchcriteria/virtual/${criteria.type}`,
      done: response => this.processVirtualResponse(criteria, response),
      fail: err => this.stackError(`Error in request : ${err}`)
    });
  }

  private processFieldResponse(criteria: IConfigurationCriteria, response: any): void {
    // Label
    if (!criteria.label) {
      criteria.label = response.data.title;
    }

    // Type
    if (!criteria.type) {
      criteria.type = response.data.smartType;
    }
    if (criteria.type === "htmltext" || criteria.type === "longtext") {
      criteria.type = "text";
    }

    //TypeFormat
    if (response.data.typeFormat) {
      criteria.typeFormat = response.data.typeFormat;
    }

    if (response.data.enumItems) {
      criteria.enumItems = response.data.enumItems;
    }

    // Multiple
    if (typeof criteria.multipleField !== "undefined") {
      criteria.multipleField = response.data.multiple;
    }

    //defaultOperator
    if (!criteria.default) {
      criteria.default = {
        operator: response.data.defaultOperator
      };
    } else if (!criteria.default.operator) {
      criteria.default.operator = response.data.defaultOperator;
    }

    this.setCriteriaOperators(criteria, response);
  }

  private processPropertyResponse(criteria: IConfigurationCriteria, response: any): void {
    // Label
    if (!criteria.label) {
      criteria.label = response.data.title;
    }

    // Type
    if (!criteria.type) {
      criteria.type = response.data.smartType;
    }

    // Multiple
    if (typeof criteria.multipleField !== "undefined") {
      criteria.multipleField = response.data.multiple;
    }

    //defaultOperator
    if (!criteria.default) {
      criteria.default = {
        operator: response.data.defaultOperator
      };
    } else if (!criteria.default.operator) {
      criteria.default.operator = response.data.defaultOperator;
    }

    // State
    if (criteria.field === "state") {
      if (!criteria.enumItems && response.data.enumItems) {
        criteria.enumItems = response.data.enumItems;
      }
    }

    this.setCriteriaOperators(criteria, response);
  }

  private processVirtualResponse(criteria: IConfigurationCriteria, response: any): void {
    //defaultOperator
    if (!criteria.default) {
      criteria.default = {
        operator: response.data.defaultOperator
      };
    } else if (!criteria.default.operator) {
      criteria.default.operator = response.data.defaultOperator;
    }

    this.setCriteriaOperators(criteria, response);
  }

  private setCriteriaOperators(criteria: IConfigurationCriteria, response: any): void {
    const requiredOperators = JSON.parse(JSON.stringify(criteria.operators));
    const correctRequiredOperators = [];
    const spuriousRequiredOperators = [];
    const availableOperators = JSON.parse(JSON.stringify(response.data.operators));
    if (requiredOperators.length) {
      requiredOperators.map(reqOp => {
        let isSpurious = true;
        for (const availableOp of availableOperators) {
          if (SmartCriteriaUtils.areOpatorEquals(reqOp, availableOp)) {
            const goodOp = availableOp;
            if (reqOp.label) {
              goodOp.label = reqOp.label;
            }
            correctRequiredOperators.push(goodOp);
            isSpurious = false;
            break;
          }
        }
        if (isSpurious) {
          spuriousRequiredOperators.push(reqOp);
        }
        return reqOp;
      });
      for (const spuriousOperator of spuriousRequiredOperators) {
        this.stackError(
          `Error: operator ${spuriousOperator.key} with options ${spuriousOperator.options} is not available and will be ignored`
        );
      }
      criteria.operators = correctRequiredOperators;
    } else {
      criteria.operators = availableOperators;
    }
  }

  /**
   * Modifies configuration in place to set default values when needed
   * @param configuration the smart criteria configuration
   */
  private initializeConfiguration(configuration: ISmartCriteriaConfiguration): void {
    if (!configuration) {
      //Default empty configuration
      configuration = this.getEmptyConfiguration();
    } else {
      if (!configuration.criterias) {
        configuration.criterias = [];
      }
      for (const criteria of this.configuration.criterias) {
        if (criteria.modifiableOperator === undefined) {
          criteria.modifiableOperator = true;
        }
      }
    }
  }

  private getEmptyConfiguration(): ISmartCriteriaConfiguration {
    return { criterias: [] };
  }

  /**
   * Method used to handle smart criteria custom kind (i.e. fulltext)
   * If needed, an AJAX request can be added to ajaxRequests field.
   * Errors can be added with the stack error method.
   * @param criteriaConfiguration
   */
  // eslint-disable-next-line @typescript-eslint/no-empty-function
  protected prepareCustomRequest(criteriaConfiguration: IConfigurationCriteria): void {}
}
