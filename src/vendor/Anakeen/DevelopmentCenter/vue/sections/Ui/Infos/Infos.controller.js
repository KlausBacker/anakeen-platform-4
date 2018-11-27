import InfoNav from "devComponents/SSInfoNav/SSInfoNav.vue";
export default {
  name: "informations",
  props: ["ssName"],
  data() {
    return {
      cvdoc: "",
      renderaccess: "",
      mskfamid: "",
      infoSections: []
    };
  },
  components: {
    InfoNav
  },
  computed: {
    url() {
      return `/api/v2/devel/smart/structures/${this.ssName}/info/`;
    }
  },
  devCenterRefreshData() {
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
    this.infoSections = [
      {
        label: "Structure",
        path: `/devel/smartStructures/${this.ssName}/infos`
      },
      {
        label: "Security",
        path: `/devel/security/smartStructures/${this.ssName}/infos`
      }
    ];
  }
};
