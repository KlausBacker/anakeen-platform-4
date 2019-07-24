/**
 * Anakeen Smart Element component object
 */
import { Component, Mixins, Prop, Vue, Watch } from "vue-property-decorator";
import VueSetup from "../setup.js";
// eslint-disable-next-line no-unused-vars
import { ISmartElementValue } from "../SmartElement/ISmartElementValue";
import AnkSmartElement from "../SmartElement/SmartElement.component";
// eslint-disable-next-line no-unused-vars
import { ISmartForm } from "./ISmartForm";

Vue.use(VueSetup);
// noinspection JSUnusedGlobalSymbols
@Component({
  name: "ank-smart-form"
})
export default class AnkSmartForm extends Mixins(AnkSmartElement) {
  @Prop({
    default: () => ({
      customClientData: null,
      initid: 0,
      revision: -1,
      viewId: "!defaultEdition"
    }),
    type: Object
  })
  public config!: ISmartForm;

  @Watch("config", { immediate: false, deep: true })
  public onConfigChanged(newConfig: ISmartForm) {
    // noinspection JSIgnoredPromiseFromCall
    this.fetchSmartElement(this.initialConfig, {
      formConfiguration: newConfig
    });
  }

  get initialConfig() {
    const data: ISmartElementValue = {
      noRouter: !this.browserHistory
    };

    data.initid = -this._uid;
    data.customClientData = this.customClientData;
    data.revision = this.revision;
    data.viewId = this.viewId;
    return data;
  }

  public mounted() {
    window.console.log("BEFORE HELLO MOUNTED2");
    this._initController(this.initialConfig, {
      formConfiguration: this.config
    });
    window.console.log("HELLO MOUNTED");
  }
}
