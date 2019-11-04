/**
 * Anakeen Smart Element component object
 */
import { Component, Mixins, Prop, Watch } from "vue-property-decorator";
import AnkSmartElement from "../AnkSmartElement/AnkSmartElement.component";
// eslint-disable-next-line no-unused-vars
import { ISmartFormConfiguration, ISmartFormValue } from "./ISmartForm";

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
  public config!: ISmartFormConfiguration;

  @Prop({
    default: () => ({}),
    type: Object
  })
  public options!: any;

  @Watch("config", { immediate: false, deep: true })
  public onConfigChanged(newConfig: ISmartFormConfiguration) {
    // noinspection JSIgnoredPromiseFromCall
    const smartElementOptions = Object.assign(this.options, {
      formConfiguration: JSON.parse(JSON.stringify(newConfig))
    });

    this.fetchSmartElement(this.initialFormData, smartElementOptions).catch(e => {
      console.error(e);
    });
  }

  get initialFormData(): ISmartFormValue {
    const data: ISmartFormValue = {
      noRouter: !this.browserHistory
    };

    data.initid = -this._uid;
    data.customClientData = this.customClientData;
    data.revision = this.revision;
    data.viewId = this.viewId;
    return data;
  }

  public mounted() {
    const smartElementOptions = Object.assign(this.options, {
      formConfiguration: JSON.parse(JSON.stringify(this.config))
    });
    this._initController(this.initialFormData, smartElementOptions);
  }
}
