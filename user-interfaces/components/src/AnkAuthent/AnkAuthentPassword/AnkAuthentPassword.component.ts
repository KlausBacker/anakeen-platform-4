import Vue from "vue";
import { Component, Prop } from "vue-property-decorator";
const INPUT_TYPES = {
  password: "password",
  text: "text"
};

// noinspection JSUnusedGlobalSymbols
@Component({
  name: "ank-authent-password"
})
export default class AuthentPasswordComponent extends Vue {
  @Prop({ type: String, default: "Password" }) public label;
  @Prop({ type: String, default: "" }) public placeholder;
  @Prop({ type: String, default: "" }) public validationMessage;
  public value: string = "";
  public passInputType: string = "password";
  public pwdId: string = "pwd" + this._uid;

  public get visibilityIcon() {
    if (this.passInputType === INPUT_TYPES.password) {
      return "fa-eye";
    } else if (this.passInputType === INPUT_TYPES.text) {
      return "fa-eye-slash";
    }
    return "fa-eye";
  }

  public $changePassword() {
    this.$emit("input", this.value);
  }

  public onPasswordInput(event) {
    this.displayCustomValidity(event);
    this.$changePassword();
  }

  public onPasswordInvalid(event) {
    this.displayCustomValidity(event);
  }

  public displayCustomValidity(event) {
    if (event.target.value === "" && this.validationMessage) {
      event.target.setCustomValidity(this.validationMessage);
    } else {
      event.target.setCustomValidity("");
    }
  }

  public revealPassword() {
    if (this.passInputType === INPUT_TYPES.password) {
      this.passInputType = INPUT_TYPES.text;
    } else if (this.passInputType === INPUT_TYPES.text) {
      this.passInputType = INPUT_TYPES.password;
    }
  }
  public setValue(value) {
    this.value = value;
  }
}
