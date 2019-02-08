import { HubStation } from "@anakeen/hub-components";
import HubEntries from "./utils/hubEntry";

export default {
  name: "ank-hub",
  components: {
    HubStation
  },
  data() {
    return {
      config: [],
      hubId: ""
    };
  },
  created() {
    this.hubEntries = new HubEntries(this);
    let route = window.location.href;
    this.hubId = route.match(/\/hub\/station\/([0-9]+)/)[1];
  },
  mounted() {
    this.getConfig();
  },
  methods: {
    getConfig() {
      this.$http
        .get(`/hub/config/${this.hubId}`)
        .then(response => {
          const data = response.data.data;
          // this.config = data;
          this.config = [
            ...data,
            {
              position: {
                dock: "LEFT",
                innerPosition: "CENTER",
                order: null
              },
              component: {
                name: "hello-world",
                props: {
                  msg: "HELLO"
                }
              },
              entryOptions: {
                route: "hello",
                selectable: true,
                selected: false
              }
            },
            {
              position: {
                dock: "RIGHT",
                innerPosition: "CENTER",
                order: null
              },
              component: {
                name: "hello-world",
                props: {
                  msg: "HELLO 2"
                }
              },
              entryOptions: {
                route: "hello2",
                selectable: true,
                selected: false
              }
            },
            {
              position: {
                dock: "TOP",
                innerPosition: "CENTER",
                order: null
              },
              component: {
                name: "hello-world",
                props: {
                  msg: "HELLO 3"
                }
              },
              entryOptions: {
                route: "hello3",
                selectable: true,
                selected: false
              }
            },
            {
              position: {
                dock: "BOTTOM",
                innerPosition: "CENTER",
                order: null
              },
              component: {
                name: "hello-world",
                props: {
                  msg: "HELLO 4"
                }
              },
              entryOptions: {
                route: "hello4",
                selectable: true,
                selected: false
              }
            }
          ];
        })
        .catch(error => {
          console.error(error);
        });
    }
  }
};
