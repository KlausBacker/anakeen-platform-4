import * as exampleFulltext from "./SmartCriteriaExampleFulltext.json";
import * as exampleAllType from "./SmartCriteriaExampleAllTypeFulltext.json";
import { Component, Vue } from "vue-property-decorator";
// noinspection JSUnusedGlobalSymbols
@Component({})
export default class TestExamplesFulltextSmartCriteriaController extends Vue {
  public examples: any[] = [
    {
      config: exampleFulltext,
      label: exampleFulltext.title || "Example fulltext"
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
    }
  ];
  public selectedIndex = -1;
  protected storageKey = "smartCriteriaFulltextExamples";
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
