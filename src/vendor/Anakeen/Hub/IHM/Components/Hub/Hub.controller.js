import AnkNotifier from "@anakeen/internal-components/lib/Notifier";
import HubStation from "@anakeen/hub-components/lib/HubStation";
import HubEntries from "./utils/hubEntry";

//noinspection JSUnusedGlobalSymbols
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
            displayTime: 0
          });
          return Promise.reject(error);
        }
      );
    },

    sendNotif({
      title = "",
      displayTime = 10000,
      type = "info",
      textContent = ""
    }) {
      this.$refs.ankNotifier.publishNotification(
        new CustomEvent("ankNotification", {
          detail: [
            {
              content: {
                title: title,
                textContent: textContent
              },
              options: {
                displayTime: displayTime,
                closable: true
              },
              type: type
            }
          ]
        })
      );
    },
    getConfig() {
      kendo.ui.progress($(this.$el), true);
      this.$http
        .get(`/hub/config/${this.hubId}`)
        .then(response => {
          const data = response.data.data;
          const globalAssets = data.globalAssets || [];
          this.hubEntries.contents = [{ assets: globalAssets }].concat(
            data.hubElements
          );
          return this.hubEntries.loadAssets().then(() => {
            kendo.ui.progress($(this.$el), false);
            this.hubEntries.useComponents();
            this.config = data;
          });
        })
        .catch(error => {
          console.error(error);
          this.sendNotif({
            title: "Loading error",
            textContent: "The entries components cannot be loaded",
            type: "error"
          });
          kendo.ui.progress($(this.$el), false);
        });
    },
    onNotify(notification) {
      this.$store.dispatch("hubNotify", notification);
    }
  }
};
