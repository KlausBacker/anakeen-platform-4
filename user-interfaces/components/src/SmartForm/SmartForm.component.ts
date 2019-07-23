/**
 * Anakeen Smart Element component object
 */
import { Component, Mixins, Prop, Vue } from "vue-property-decorator";
import VueSetup from "../setup.js";
import AnkSmartElement from "../SmartElement/SmartElement.component";
// eslint-disable-next-line no-unused-vars
import { ISmartForm } from "./ISmartForm";

Vue.use(VueSetup);
@Component({
  name: "ank-smart-form"
})
export default class AnkSmartForm extends Mixins(AnkSmartElement) {
  @Prop({
    default: () => ({
      customClientData: null,
      initid: 0,
      revision: -1,
      viewId: "!defaultConsultation"
    }),
    type: Object
  })
  public config!: ISmartForm;

  public mounted() {
    const y = [456467, 456467, 456467, 456467, 456467, 456467, 456467, 456467];
    const x = [456467,456467,456467,456467,456467,456467,456467,456467,456467,456467,456467,456467,];
    window.console.log("BEFORE HELLO MOUNTED");
    window.console.log("HELLO MOUNTED");
  }
}
