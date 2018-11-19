export default {
  name: "informations",
  props: ["wflName"],
  data() {
    return {
      wflData: {}
    };
  },
  computed: {
    graphUrl() {
      return `/api/v2/devel/ui/workflows/image/${
        this.wflName
      }/sizes/24x24.svg?inline=true`;
    }
  },
  mounted() {
    this.$http
      .get(`/api/v2/devel/smart/workflows/${this.wflName}`)
      .then(response => {
        this.wflData = response.data.data.properties;
      });
  }
};
