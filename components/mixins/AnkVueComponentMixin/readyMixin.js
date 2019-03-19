//A mixin to indicate if the component is ready
export const AnkVueReady = {
  data() {
    return {
      ready: false
    };
  },
  methods: {
    isReady: function() {
      return this.ready;
    },
    _enableReady: function() {
      const ready = () => {
        this.ready = true;
        if (this.$emitAnkEvent) {
          this.$emitAnkEvent("se-authent-ready");
        }
      };
      if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", ready);
      } else {
        ready();
      }
    }
  }
};

export default AnkVueReady;
