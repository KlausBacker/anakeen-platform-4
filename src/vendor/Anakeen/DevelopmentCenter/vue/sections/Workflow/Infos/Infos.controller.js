export default {
  name: "informations",
  props: ["wflIdentifier"],
  data() {
    return {
      wflData: {},
      wflGraph: ""
    };
  },
  mounted() {
    this.$http
      .get(`/api/v2/devel/smart/workflows/${this.wflIdentifier}.json`)
      .then(response => {
        this.wflData = response.data;
      });
    this.$http
      .get(`/api/v2/devel/ui/workflows/image/{workflow}/sizes/24x24`)
      .then(response => {
        this.wflGraph = response.data;
      });
  }
};
