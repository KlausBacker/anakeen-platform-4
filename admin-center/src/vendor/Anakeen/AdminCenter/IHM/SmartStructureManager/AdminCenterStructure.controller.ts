import AnkTabs from "@anakeen/user-interfaces/components/lib/AnkTabs.esm";
import AnkTab from "@anakeen/user-interfaces/components/lib/AnkTab.esm";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import { Component, Mixins, Watch, Prop } from "vue-property-decorator";
import DefaultValues from "./SmartStructureDefaultValues/SmartStructureManagerDefaultValues.vue";
import Info from "./SmartStructureInformations/SmartStructureManagerInformations.vue";
import SSList from "./SmartStructureList/SSList.vue";
import Parameters from "./SmartStructureParameters/SmartStructureManagerParameters.vue";
import SmartElements from "./SmartStructureSmartElements/SmartStructureManagerSmartElements.vue";

@Component({
  components: {
    "ank-tab": AnkTab,
    "ank-tabs": AnkTabs,
    "ss-list": SSList,
    "ssm-default-values": DefaultValues,
    "ssm-info": Info,
    "ssm-parameters": Parameters,
    "ssm-smart-elements": SmartElements
  }
})
export default class AdminCenterStructureController extends Mixins(AnkI18NMixin) {
  public $refs!: {
    [key: string]: any;
  };
  @Prop({ default: "", type: String })
  public value!: string;
  @Prop({ default: "informations", type: String })
  public tabFromUrl: string;
  public isEmpty: boolean = true;
  public selectedSS: string = "";
  public structureInfos: object = {};
  @Watch("value")
  public onValuePropChanged(newVal, oldVal) {
    if (newVal !== oldVal) {
      this.selectedSS = newVal;
    }
  }
  @Watch("selectedSS")
  public watchSelectedSS(newValue, oldValue) {
    this.isEmpty = false;
  }
  public mounted() {
    if (this.value) {
      this.selectedSS = this.value;
      this.$nextTick(() => {
        this.$refs.ssmTabs.setSelectedTab(this.tabFromUrl);
      });
    }
  }
  protected gotoParentStructure(structureId) {
    this.selectedSS = structureId.toString();
  }
  protected recordStructureInfos(informations) {
    this.structureInfos = informations;
  }
  protected emitTabId(selectedTab) {
    this.$emit("tabChange", selectedTab.tabId);
  }
}
