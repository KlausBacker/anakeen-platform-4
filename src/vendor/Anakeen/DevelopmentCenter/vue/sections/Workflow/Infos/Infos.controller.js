export default {
  name: "informations",
  props: ["wflName"],
  data() {
    return {
      wflData: {},
      wflGraph: ""
    };
  },
  mounted() {
    this.$http
      .get(`/api/v2/devel/smart/workflows/${this.wflName}`)
      .then(response => {
        this.wflData = response.data.data.properties;
      });
    this.$http
      .get(`/api/v2/devel/ui/workflows/image/${this.wflName}/sizes/24x24.svg`)
      .then(response => {
        this.wflGraph = response.data;
      });
  }
};
