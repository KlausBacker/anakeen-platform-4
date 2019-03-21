import AnkNotifier from "@anakeen/internal-components/lib/Notifier";
import HubStation from "@anakeen/hub-components/lib/HubStation";
import HubEntries from "./utils/hubEntry";

export default {
  name: "ank-hub",
  components: {
    AnkNotifier,
    HubStation
  },
  data() {
    return {
      config: {},
      hubId: ""
    };
  },
  created() {
    this.hubEntries = new HubEntries(this);
    this.hubId = window.AnkHubInstanceId;
    this.$store.subscribe(mutationPayload => {
      if (mutationPayload.type === "SET_NOTIFICATION") {
        if (mutationPayload.payload) {
          this.$refs.ankNotifier.publishNotification(
            new CustomEvent("ankNotification", {
              detail: [mutationPayload.payload]
            })
          );
        }
      }
    });
  },
  mounted() {
    this.getConfig();
    this.interceptRequest();
  },

  methods: {
    interceptRequest() {
      return this.$http.interceptors.response.use(
        response => {
          if (response.data && response.data.messages) {
            response.data.messages.forEach(msg => {
              if (!msg.type || msg.type === "message") {
                msg.type = "info";
              }
              this.sendNotif({
                textContent: msg.contentText,
                type: msg.type,
                displayTime: 10000
              });
            });
          }
          return response;
        },
        error => {
          let errorMsg = "";
          if (
            error.response &&
            error.response.data &&
            error.response.data.error
          ) {
            errorMsg = error.response.data.error;
          } else if (
            error.response &&
            error.response.data &&
            error.response.data.message
          ) {
            errorMsg = error.response.data.message;
          } else if (
            error.response &&
            error.response.data &&
            error.response.data.exceptionMessage
          ) {
            errorMsg = error.response.data.exceptionMessage;
          } else if (error.message) {
            errorMsg = error.message;
          } else {
            errorMsg = "Unexpected error. See console for more details";
          }

          this.sendNotif({
            textContent: errorMsg,
            type: "error",
            displayTime: 99990
          });
          return Promise.reject(error);
        }
      );
    },
    sendNotif(data) {
      this.$refs.ankNotifier.publishNotification(
        new CustomEvent("ankNotification", {
          detail: [
            {
              content: {
                title: data.title,
                textContent: data.textContent
              },
              options: {
                displayTime: data.displayTime || 10000,
                closable: true
              },
              type: data.type || "info"
            }
          ]
        })
      );
    },
    getConfig() {
      this.$kendo.ui.progress(this.$(this.$el), true);
      this.$http
        .get(`/hub/config/${this.hubId}`)
        .then(response => {
          const data = response.data.data;
          const globalAssets = data.globalAssets || [];
          this.hubEntries.contents = [{ assets: globalAssets }].concat(
            data.hubElements
          );
          this.hubEntries.loadAssets().then(() => {
            this.$kendo.ui.progress(this.$(this.$el), false);
            this.hubEntries.useComponents();
            this.config = data;
          });
        })
        .catch(error => {
          console.error(error);
          this.$kendo.ui.progress(this.$(this.$el), false);
        });
    },
    onNotify(notification) {
      this.$store.dispatch("hubNotify", notification);
    }
  }
};
