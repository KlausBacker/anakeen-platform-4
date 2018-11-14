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
        if (response.data.data.properties.security.profil) {
          return response.data.data.properties.security.profil.id;
        }
        return 0;
      }
    }
  },
  data() {
    return {
      profid: 0,
      profilWaitingLabel: "Chargement en cours...",
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
      this.profid = 0;
      this.$http
        .get(this.profilUrl)
        .then(response => {
          let profid;
          if (typeof this.profilData === "string") {
            profid = response.data.data[this.profilData];
          } else if (typeof this.profilData === "function") {
            profid = this.profilData(response);
          }
          const profidValue = parseInt(profid);
          if (profidValue) {
            this.profid = profidValue;
          } else {
            this.noProfile = true;
            this.profilWaitingLabel =
              "Aucun profil pour la structure " + this.ssName;
          }
          kendo.ui.progress(this.$(this.$el), false);
        })
        .catch(err => {
          console.error(err);
          kendo.ui.progress(this.$(this.$el), false);
        });
    }
  }
};
