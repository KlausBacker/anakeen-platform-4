import RawView from "./RawView/RawView.vue";

const URLS = {
  json: {
    structure: "/api/v2/smart-structures/<identifier>/views/structure",
    element: "/api/v2/smart-elements/<identifier>.json",
    default: "/api/v2/smart-elements/<identifier>.json"
  },
  xml: {
    structure: "/api/v2/devel/config/smart/structures/<identifier>.xml",
    element: "/api/v2/smart-elements/<identifier>.xml",
    workflow: `/api/v2/devel/config/smart/workflows/<identifier>.xml`,
    search: `/api/v2/devel/config/smart/searches/<identifier>.xml`,
    default: "/api/v2/smart-elements/<identifier>.xml"
  }
};

export default {
  props: ["elementId", "elementType", "formatType"],
  components: {
    RawView
  },
  data() {
    return {
      urls: URLS,
      content: null
    };
  },
  computed: {
    isTypeParsed() {
      return this.formatType === "json";
    }
  },
  watch: {
    elementId() {
      this.content = null;
      this.displayRawContent();
    },
    elementType() {
      this.content = null;
      this.displayRawContent();
    },
    formatType() {
      this.content = null;
      this.displayRawContent();
    }
  },
  mounted() {
    this.displayRawContent();
  },
  devCenterRefreshData() {
    this.displayRawContent();
  },
  methods: {
    displayRawContent() {
      kendo.ui.progress(this.$(this.$el), true);
      let url = this.urls[this.formatType][this.elementType] || this.urls[this.formatType].default;
      url = url.replace("<identifier>", this.elementId);
      this.$http
        .get(url)
        .then(response => {
          if (this.formatType === "json") {
            this.content = response.data.data;
          } else {
            this.content = response.data;
          }
          kendo.ui.progress(this.$(this.$el), false);
        })
        .catch(err => {
          console.error(err);
          kendo.ui.progress(this.$(this.$el), false);
        });
    },
    getFilter() {
      return {
        formatType: this.formatType
      };
    },
    openView() {
      const splitter = this.$refs.splitter.kendoWidget();
      splitter.expand(".k-pane:last");
    }
  }
};
