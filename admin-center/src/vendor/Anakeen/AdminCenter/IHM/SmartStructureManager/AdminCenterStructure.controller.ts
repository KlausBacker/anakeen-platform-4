import AnkTabs from "@anakeen/user-interfaces/components/lib/AnkSmartElementTabs.esm";
import AnkTab from "@anakeen/user-interfaces/components/lib/AnkTab.esm";
import { Component, Vue, Watch } from "vue-property-decorator";
import DefaultValues from "./SmartStructureDefaultValues/SmartStructureManagerDefaultValues.vue";
import Info from "./SmartStructureInformations/SmartStructureManagerInformations.vue";
import SSList from "./SmartStructureList/SSList.vue";
import Parameters from "./SmartStructureParameters/SmartStructureManagerParameters.vue";

@Component({
  components: {
    "ank-tab": AnkTab,
    "ank-tabs": AnkTabs,
    "ss-list": SSList,
    "ssm-default-values": DefaultValues,
    "ssm-info": Info,
    "ssm-parameters": Parameters
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
      case "parameters":
        this.$refs.paramsComp.$refs.parametersGridData.kendoDataSource.read();
        break;
    }
  }
  protected gotoParentStructure(structureId){
    this.selectedSS = structureId.toString();
  }
}
