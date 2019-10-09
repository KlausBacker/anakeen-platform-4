import Vue from "vue";
import { Component, Prop, Watch } from "vue-property-decorator";
import PropertiesView from "./PropertiesView/PropertiesView.vue";

@Component({
  components: {
    "properties-view": PropertiesView
  }
})
export default class SmartStructureManagerInformationsController extends Vue {
  @Prop({
    default: "",
    type: String
  })
  public ssName;
}
