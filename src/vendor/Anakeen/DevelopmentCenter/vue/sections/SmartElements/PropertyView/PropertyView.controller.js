export default {
  props: {
    elementId: {
      type: [String, Number],
      required: true
    }
  },
  watch: {
    elementId(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.fetchProperties(newValue);
      }
    }
  },
  data() {
    return {
      element: {}
    };
  },
  computed: {
    elementProperties() {
      if (this.element.properties) {
        return this.element.properties;
      }
      return {};
    },
    isReady() {
      return this.elementProperties !== {};
    }
  },
  mounted() {
    this.fetchProperties(this.elementId);
  },
  methods: {
    fetchProperties(eId) {
      this.$http
        .get(`/api/v2/documents/${eId}.json?fields=document.properties.all`)
        .then(response => {
          if (
            response &&
            response.data &&
            response.data.data &&
            response.data
          ) {
            this.element = response.data.data.document;
          }
        })
        .catch(err => {
          console.error(err);
        });
    },
    formatDate(date) {
      if (!date) {
        return "";
      }
      return kendo.toString(new Date(date), "g");
    }
  }
};
