import * as exampleTitle from "./SmartCriteriaExampleTitle.json";
import * as exampleCustomTitle from "./SmartCriteriaExampleCustomTitle.json";
import * as exampleAutocomplete from "./SmartCriteriaExampleAutocomplete.json";
import * as exampleAuthorAutocomplete from "./SmartCriteriaExampleAuthorAutocomplete.json";
import * as exampleComplexAutocomplete from "./SmartCriteriaExampleComplexAutocomplete.json";
import * as exampleState from "./SmartCriteriaExampleState.json";
import * as exampleBetween from "./SmartCriteriaExampleBetweenValue.json";
import * as exampleTextual from "./SmartCriteriaExampleTextual.json";
import * as exampleTitleCustomOperators from "./SmartCriteriaExampleTitleCustomOperators.json";
import * as exampleAllType from "./SmartCriteriaExampleAllType.json";
import * as exampleDemo from "./SmartCriteriaExampleDemo.json";
import * as exampleModifiableOperator from "./SmartCriteriaExampleModifiableOperator.json";
import * as exampleCorrectStandalone from "./SmartCriteriaExampleCorrectStandalone.json";
import { Component, Vue } from "vue-property-decorator";
// noinspection JSUnusedGlobalSymbols
@Component({})
export default class TestExamplesSmartCriteriaController extends Vue {
  public examples: any[] = [
    {
      config: exampleDemo,
      label: exampleDemo.title || "Example Demo"
    },
    {
      config: exampleCorrectStandalone,
      label: exampleCorrectStandalone.title || "Example correct standalone"
    },
    {
      config: exampleAutocomplete,
      label: exampleAutocomplete.title || "Example Autocomplete"
    },
    {
      config: exampleAuthorAutocomplete,
      label: exampleAuthorAutocomplete.title || "Example Author Autocomplete"
    },
    {
      config: exampleComplexAutocomplete,
      label: exampleComplexAutocomplete.title || "Example Complex Autocomplete"

    },
    {
      config: exampleModifiableOperator,
      label: exampleModifiableOperator.title || "Example Modifiable Operator"
    },
    {
      config: exampleAllType,
      label: exampleAllType.title || "Example All Type",
      responsiveColumns: [
        {
          number: 3,
          minWidth: "50rem",
          maxWidth: null,
          grow: true
        }
      ]
    },
    {
      config: exampleTitle,
      label: exampleTitle.title || "Example Title"
    },
    {
      config: exampleCustomTitle,
      label: exampleCustomTitle.title || "Example Custom Title"
    },
    {
      config: exampleTitleCustomOperators,
      label: exampleTitleCustomOperators.title || "Example Title Custom Operators"
    },
    {
      config: exampleTextual,
      label: exampleTextual.title || "Example Textuel"
    },
    {
      config: exampleState,
      label: exampleState.title || "Example State"
    },
    {
      config: exampleBetween,
      label: exampleBetween.title || "Example Between"
    },
  ];
  public selectedIndex: number = -1;


  protected storageKey = "smartCriteriaExamples";
  private staticExampleLength: number;

  // noinspection JSUnusedGlobalSymbols
  public beforeMount() {
    const storageExample = this.getRecordExamples();
    this.staticExampleLength = this.examples.length;
    storageExample.forEach((item, index) => {
      this.examples.push({
        config: item,
        label: item.title || "Untitled form #" + (index + this.staticExampleLength + 1),
        localIndex: index
      });
    });
  }
  public onSelectExample(e) {
    const target = e.currentTarget;
    const key = parseInt(target.dataset.key, 10);
    this.selectExample(key);
  }

  public selectExample(index) {
    this.selectedIndex = index;
    this.$emit("select", this.examples[index]);
  }

  public createExample() {
    const recordedExamples = this.getRecordExamples();
    const newExample = {
      title: "Local criteria #" + (this.examples.length + 1)
    };
    const index = this.examples.length - this.staticExampleLength;
    recordedExamples.push(newExample);
    window.localStorage.setItem(this.storageKey, JSON.stringify(recordedExamples));
    this.examples.push({
      config: newExample,
      label: newExample.title,
      localIndex: index
    });
    this.selectExample(this.examples.length - 1);
  }

  public recordExample(index, config) {
    const recordedExamples = this.getRecordExamples();
    const generalIndex = index + this.staticExampleLength;

    recordedExamples[index] = config;
    this.examples[generalIndex].label = config.title || "Untitled criteria #" + (generalIndex + 1);
    this.examples[generalIndex].config = config;
    window.localStorage.setItem(this.storageKey, JSON.stringify(recordedExamples));
  }

  protected getRecordExamples(): any[] {
    const storageDataExamples: string = window.localStorage.getItem(this.storageKey) || "[]";
    return JSON.parse(storageDataExamples);
  }
}
