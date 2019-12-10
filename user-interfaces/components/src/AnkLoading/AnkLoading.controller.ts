import Vue from "vue";
import { Component, Prop } from "vue-property-decorator";

@Component({
  name: "ank-loading"
})
export default class AnakeenLoadingController extends Vue {
  @Prop({
    default: "white",
    type: String,
    validator: value => {
      return value === "black" || value === "white";
    }
  })
  public color;
  @Prop({ type: String, default: "auto" }) public width;
  @Prop({ type: String, default: "18px" }) public height;
  @Prop({ type: Boolean, default: true }) public fullLabel;

  public static get viewBox() {
    return "0 0 400 120";
  }
}
