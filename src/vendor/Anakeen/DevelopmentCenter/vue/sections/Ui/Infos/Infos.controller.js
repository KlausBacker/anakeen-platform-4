export default {
  name: "informations",
  props: ["ssName"],
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
      if (ui.ccvid) {
        this.cvdoc = ui.ccvid;
      }
      if (ui.render) {
        this.renderaccess = ui.render;
      }
    });
  }
};
