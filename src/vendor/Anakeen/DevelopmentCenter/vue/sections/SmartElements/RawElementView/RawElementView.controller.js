import RawView from "./RawView/RawView.vue";

const URLS = {
  json: {
    structure: "/api/v2/families/<identifier>/views/structure",
    element: "/api/v2/documents/<identifier>.json",
    default: "/api/v2/documents/<identifier>.json"
  },
  xml: {
    structure: "/api/v2/devel/config/smart/structures/<identifier>.xml",
    element: "/api/v2/documents/<identifier>.xml",
    workflow: `/api/v2/devel/config/smart/workflows/<identifier>.xml`,
    default: "/api/v2/documents/<identifier>.xml"
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
      let url =
        this.urls[this.formatType][this.elementType] ||
        this.urls[this.formatType].default;
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
    openView() {
      const splitter = this.$refs.splitter.kendoWidget();
      splitter.expand(".k-pane:last");
    }
  }
};
