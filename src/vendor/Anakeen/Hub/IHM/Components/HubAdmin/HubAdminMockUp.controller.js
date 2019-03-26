export default {
  name: "ank-hub-admin-mockup",
  props: ["info", "selectedId"],

  methods: {
    selectConfig(e) {
      console.log("select", e);
      this.$emit("mock-select", e);
    }
  }
};
