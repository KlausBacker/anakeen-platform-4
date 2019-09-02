/* tslint:disable:variable-name */
import { Component, Vue } from "vue-property-decorator";
// A mixin to indicate if the component is ready
@Component
export default class AnkVueReadyMixin extends Vue {
  private _ank_ready: boolean = false;

  public isReady() {
    return this._ank_ready;
  }

  public _enableReady() {
    const ready = () => {
      this._ank_ready = true;
      this.$emit("ank-ready");
    };
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", ready);
    } else {
      ready();
    }
  }
}
