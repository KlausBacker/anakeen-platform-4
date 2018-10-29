import ProfileGrid from "../../../../components/profile/profile.vue";
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
        if (response.data.data.security.cprofid) {
          return response.data.data.security.cprofid.id;
        }
        return 0;
      }
    }
  },
  data() {
    return {
      cprofid: 0,
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
      this.cprofid = 0;
      this.$http
        .get(this.profilUrl)
        .then(response => {
          let cprofid;
          if (typeof this.profilData === "string") {
            cprofid = response.data.data[this.profilData];
          } else if (typeof this.profilData === "function") {
            cprofid = this.profilData(response);
          }
          const cprofidValue = parseInt(cprofid);
          if (cprofidValue) {
            this.cprofid = cprofidValue;
          } else {
            this.empty = true;
            this.profilWaitingLabel =
              "Aucun profil pour la structure " + this.ssName;
          }
          kendo.ui.progress(this.$(this.$el), false);
        })
        .catch(err => {
          console.error(err);
          this.empty = true;
          this.profilWaitingLabel =
            "Aucun profil pour la structure " + this.ssName;
          kendo.ui.progress(this.$(this.$el), false);
        });
    }
  }
};
