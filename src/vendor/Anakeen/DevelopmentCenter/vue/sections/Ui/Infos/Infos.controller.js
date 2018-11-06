export default {
  name: "informations",
  props: ["ssName"],
  watch: {
    ssName(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.$http.get(this.url).then(response => {
          const ui = response.data.data.ui;
          this.mskfamid = response.data.data.msk["msk_famid"];
          this.cvdoc = ui.ccvid || {};
          this.renderaccess = ui.render || {};
        });
      }
    }
  },
  data() {
    return {
      cvdoc: "",
      renderaccess: "",
      mskfamid: ""
    };
  },
  computed: {
    url() {
      return `/api/v2/devel/smart/structures/${this.ssName}/info/`;
    }
  },
  mounted() {
    this.$http.get(this.url).then(response => {
      const ui = response.data.data.ui;
      this.mskfamid = response.data.data.msk["msk_famid"];
      this.cvdoc = ui.ccvid || {};
      this.renderaccess = ui.render || {};
    });
  }
};
