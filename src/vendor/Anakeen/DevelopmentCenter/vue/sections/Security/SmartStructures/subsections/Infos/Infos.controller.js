export default {
  name: "Infos",
  props: ["ssName"],
  watch: {
    ssName(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.fetchStructureInfos();
      }
    }
  },
  data() {
    return {
      isReady: false,
      structureDetails: {}
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
