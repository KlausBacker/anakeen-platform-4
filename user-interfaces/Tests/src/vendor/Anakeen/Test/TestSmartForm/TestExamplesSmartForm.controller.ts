import * as example1 from "./SmartFormExample1.json";
import * as example2 from "./SmartFormExample2.json";
import * as example3 from "./SmartFormExample3.json";
import * as example4 from "./SmartFormExample4.json";

import { Component, Vue } from "vue-property-decorator";
// noinspection JSUnusedGlobalSymbols
@Component({})
export default class TestExamplesSmartFormController extends Vue {
  public examples: any[] = [
    {
      json: example1,
      label: example1.title || "Example 1"
    },
    {
      json: example2,
      label: example2.title || "Example 2"
    },
    {
      json: example3,
      label: example3.title || "Example 3"
    },
    {
      json: example4,
      label: example4.title || "Example 4"
    }
  ];
  public selectedIndex: number = -1;

  protected storageKey = "smartFormExamples";
  private staticExampleLength: number;

  // noinspection JSUnusedGlobalSymbols
  public beforeMount() {
    const storageExample = this.getRecordExamples();
    this.staticExampleLength = this.examples.length;
    storageExample.forEach((item, index) => {
      this.examples.push({
        json: item,
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
      title: "Local form #" + (this.examples.length + 1)
    };
    const index = this.examples.length - this.staticExampleLength;
    recordedExamples.push(newExample);
    window.localStorage.setItem(this.storageKey, JSON.stringify(recordedExamples));
    this.examples.push({
      json: newExample,
      label: newExample.title,
      localIndex: index
    });
    this.selectExample(this.examples.length - 1);
  }

  public recordExample(index, json) {
    const recordedExamples = this.getRecordExamples();
    const generalIndex = index + this.staticExampleLength;

    recordedExamples[index] = json;
    this.examples[generalIndex].label = json.title || "Untitled form #" + (generalIndex + 1);
    window.localStorage.setItem(this.storageKey, JSON.stringify(recordedExamples));
  }

  protected getRecordExamples(): any[] {
    const storageDataExamples: string = window.localStorage.getItem(this.storageKey) || "[]";
    return JSON.parse(storageDataExamples);
  }
}
