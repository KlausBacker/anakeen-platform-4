import ProfileGrid from "../../../../../components/profile/profile.vue";
export default {
  components: {
    "profile-grid": ProfileGrid
  },
  props: {
    ssName: {
      type: String,
      default: ""
    },
    structureUrl: {
      type: String,
      default: "/api/v2/devel/smart/structures/<ssName>/info/"
    },
    profilData: {
      type: [String, Function],
      default: () => response => {
        if (response.data.data.security.cfallid) {
          return response.data.data.security.cfallid.id;
        }
        return 0;
      }
    }
  },
  data() {
    return {
      cfallid: 0,
      profilWaitingLabel: "",
      empty: false,
      noContentIcon: "security",
      noProfile: false
    };
  },
  computed: {
    profilUrl() {
      const baseUrl = this.structureUrl;
      return baseUrl.replace("<ssName>", this.ssName);
    }
  },
  watch: {
    ssName(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.loadProfil();
      }
    }
  },
  mounted() {
    this.loadProfil();
  },
  methods: {
    loadProfil() {
      kendo.ui.progress(this.$(this.$el), true);
      this.cfallid = 0;
      this.$http
        .get(this.profilUrl)
        .then(response => {
          let cfallid;
          if (typeof this.profilData === "string") {
            cfallid = response.data.data[this.profilData];
          } else if (typeof this.profilData === "function") {
            cfallid = this.profilData(response);
          }
          const cfallidValue = parseInt(cfallid);
          if (cfallidValue) {
            this.cfallid = cfallidValue;
          } else {
            this.empty = true;
            this.profilWaitingLabel = "No default field access for " + this.ssName;
          }
          kendo.ui.progress(this.$(this.$el), false);
        })
        .catch(err => {
          console.error(err);
          this.empty = true;
          this.profilWaitingLabel = "No default field access for " + this.ssName;
          kendo.ui.progress(this.$(this.$el), false);
        });
    }
  }
};
