import AnkMixins from "../../../mixins/AnkVueComponentMixin";
const INPUT_TYPES = {
  password: "password",
  text: "text"
};

// noinspection JSUnusedGlobalSymbols
export default {
  name: "ank-authent-password",
  mixins: AnkMixins,
  props: {
    label: {
      type: String,
      default: "Password"
    },
    placeholder: {
      type: String,
      default: ""
    },

    validationMessage: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      value: "",
      passInputType: "password",
      pwdId: "pwd" + this._uid
    };
  },

  computed: {
    visibilityIcon() {
      if (this.passInputType === INPUT_TYPES.password) {
        return "fa-eye";
      } else if (this.passInputType === INPUT_TYPES.text) {
        return "fa-eye-slash";
      }
      return "fa-eye";
    }
  },

  methods: {
    $changePassword() {
      this.$emit("input", this.value);
    },

    onPasswordInput(event) {
      this.displayCustomValidity(event);
      this.$changePassword();
    },

    onPasswordInvalid(event) {
      this.displayCustomValidity(event);
    },

    displayCustomValidity(event) {
      if (event.target.value === "" && this.validationMessage) {
        event.target.setCustomValidity(this.validationMessage);
      } else {
        event.target.setCustomValidity("");
      }
    },

    revealPassword() {
      if (this.passInputType === INPUT_TYPES.password) {
        this.passInputType = INPUT_TYPES.text;
      } else if (this.passInputType === INPUT_TYPES.text) {
        this.passInputType = INPUT_TYPES.password;
      }
    },

    setValue(value) {
      this.value = value;
    }
  }
};
