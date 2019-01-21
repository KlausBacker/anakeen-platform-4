export default {
  name: "informations",
  props: ["wflName"],
  data() {
    return {
      wflData: {},
      wflGraph: "",
      wflOrient: "LR",
      wflUseLabel: "activity"
    };
  },
  methods: {
    rotateGraph: function() {
      const orientations = ["LR", "TB", "RL", "BT"];
      let iOrient = orientations.indexOf(this.wflOrient);

      iOrient = (iOrient + 1) % 4;
      this.wflOrient = orientations[iOrient];
      this.displayGraph();
    },
    toggleUseLabel: function() {
      let labelsMode = ["state", "activity", "raw"];
      let pos = labelsMode.indexOf(this.wflUseLabel);
      this.wflUseLabel = labelsMode[(pos + 1) % 3];
      this.displayGraph();
    },
    downloadGraph: function() {
      window.open(this.getGraphUrl());
    },
    getGraphUrl: function() {
      return `/api/v2/devel/ui/workflows/image/${
        this.wflName
      }.svg?orientation=${this.wflOrient}&useLabel=${this.wflUseLabel}`;
    },
    displayGraph: function() {
      this.$http.get(this.getGraphUrl()).then(response => {
        this.wflGraph = response.data;
      });
    }
  },
  devCenterRefreshData() {
    this.$http
      .get(`/api/v2/devel/smart/workflows/${this.wflName}`)
      .then(response => {
        this.wflData = response.data.data.properties;
      });
    this.displayGraph();
  },
  mounted() {
    this.$http
      .get(`/api/v2/devel/smart/workflows/${this.wflName}`)
      .then(response => {
        this.wflData = response.data.data.properties;
      });
    this.displayGraph();
  }
};
