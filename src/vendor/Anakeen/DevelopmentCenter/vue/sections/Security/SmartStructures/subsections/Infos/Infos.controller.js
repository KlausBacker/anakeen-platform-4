import InfosNav from "devComponents/SSInfoNav/SSInfoNav.vue";

export default {
  name: "Infos",
  props: ["ssName"],
  components: {
    InfosNav
  },
  watch: {
    ssName(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.fetchStructureInfos();
      }
    },
    isReady(newValue) {
      if (newValue) {
        if (this.structureDetails && this.structureDetails.workflow) {
          this.infoSections.push({
            label: "Workflow",
            path: `/devel/wfl/${this.structureDetails.workflow.name ||
              this.structureDetails.workflow.id}/infos`
          });
        }
      }
    }
  },
  data() {
    return {
      isReady: false,
      structureDetails: {},
      infoSections: []
    };
  },
  computed: {
    urlInfo() {
      return `/api/v2/devel/smart/structures/${this.ssName}/info/`;
    },
    structureSecurity() {
      return this.structureDetails ? this.structureDetails.security || {} : {};
    },
    structureProperties() {
      return this.structureDetails
        ? this.structureDetails.properties || {}
        : {};
    },
    structureInfo() {
      return this.structureDetails ? this.structureDetails.info || {} : {};
    }
  },
  mounted() {
    this.fetchStructureInfos();
    this.infoSections = [
      {
        label: "Structure",
        path: `/devel/smartStructures/${this.ssName}/infos`
      },
      { label: "User Interface", path: `/devel/ui/${this.ssName}/infos` }
    ];
  },
  methods: {
    fetchStructureInfos() {
      this.$http
        .get(this.urlInfo)
        .then(response => {
          this.structureDetails = response.data.data;
          this.isReady = true;
        })
        .catch(err => {
          console.error(err);
        });
    },
    formatTags(tags) {
      if (tags) {
        return Object.keys(tags)
          .map(key => {
            return `<b>${key} :</b> ${tags[key]}`;
          })
          .join("<br/>");
      }
    }
  }
};
