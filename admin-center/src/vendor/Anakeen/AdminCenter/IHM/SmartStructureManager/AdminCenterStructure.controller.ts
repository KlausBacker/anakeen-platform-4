import AnkTabs from "@anakeen/user-interfaces/components/lib/AnkSETabs";
import AnkTab from "@anakeen/user-interfaces/components/lib/AnkTab";
import Vue from "vue";
import { Component, Watch } from "vue-property-decorator";
import DefaultValues from "./SmartStructureDefaultValues/SmartStructureManagerDefaultValues.vue";
import Info from "./SmartStructureInformations/SmartStructureManagerInformations.vue";
import SSList from "./SmartStructureList/SSList.vue";

@Component({
  components: {
    "ank-tab": AnkTab,
    "ank-tabs": AnkTabs,
    "ss-list": SSList,
    "ssm-default-values": DefaultValues,
    "ssm-info": Info
  }
})
export default class AdminCenterStructureController extends Vue {
  public $refs!: {
    [key: string]: any;
  };
  public isEmpty: boolean = true;
  public selectedSS: string = "";
  public selectedTab: string = "informations";
  @Watch("selectedTab")
  public onSelectedTabDataChange(newVal, oldVal) {
    if (newVal && newVal !== oldVal) {
      this.$emit("selectedStructure", newVal);
    }
  }
  @Watch("selectedSS")
  public watchSelectedSS(newValue) {
    if (newValue) {
      this.isEmpty = false;
      this.selectedTab = "informations";
    }
  }
  protected onTabClick() {
    switch (this.selectedTab) {
      case "defaultValues":
        this.$refs.defaultComp.$refs.defaultGridContent.kendoWidget().dataSource.read();
        break;
    }
    // this.$refs.ssmTabs.selectSs(this.selectedTab);
  }
}
