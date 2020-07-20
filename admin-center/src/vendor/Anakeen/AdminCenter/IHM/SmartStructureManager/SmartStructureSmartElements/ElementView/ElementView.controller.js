import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
export default {
  name: "ElementView",
  components: { "ank-smart-element": () => AnkSmartElement },
  props: ["initid", "viewId"],
  data() {
    return {
      element: null,
      errorMessage: ""
    };
  },
  computed: {
    isReady() {
      return !!this.element;
    }
  },
  mounted() {
    kendo.ui.progress(this.$(this.$el), true);
  },
  methods: {
    onDetachElement() {
      if (window.open) {
        if (this.viewId) {
          window.open(`/api/v2/smart-elements/${this.initid}/views/${this.viewId}.html`);
        } else {
          window.open(`/api/v2/smart-elements/${this.initid}.html`);
        }
      }
    },
    onReady(event, element) {
      this.errorMessage = "";
      this.element = element;
      this.element.name = this.$refs.smartElement.getProperty("name");
      kendo.ui.progress(this.$(this.$el), false);
    },
    refreshGrid() {
      this.$emit("se-after-save");
    }
  }
};
