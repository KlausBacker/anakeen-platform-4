import * as example1 from "./SmartFormExample1.json";
import * as example2 from "./SmartFormExample2.json";
import * as example3 from "./SmartFormExample3.json";
import * as example4 from "./SmartFormExample4.json";

import { Component, Vue } from "vue-property-decorator";
// noinspection JSUnusedGlobalSymbols
@Component({})
export default class TestExamplesSmartFormController extends Vue {
  public examples = [
    {
      json: example1,
      label: "Example 1"
    },
    {
      json: example2,
      label: "Example 2"
    },
    {
      json: example3,
      label: "Example 3"
    },
    {
      json: example4,
      label: "Example 4"
    }
  ];
  public selectedIndex: number = -1;

  public onSelectExample(e) {
    const target = e.currentTarget;
    const key = parseInt(target.dataset.key, 10);
    this.selectExample(key);
  }

  public selectExample(index) {
    this.selectedIndex = index;
    this.$emit("select", this.examples[index].json);
  }
}
