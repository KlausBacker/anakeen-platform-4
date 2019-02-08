import { HubElement } from "@anakeen/hub-components";
import SubHello from "./SubHello.vue";

export default {
  extends: HubElement,
  props: {
    msg: {
      type: String
    },
    test: {
      type: Boolean,
      default: false
    }
  },
  hubRoutes: [
    {
      path: "tutu",
      component: SubHello
    }
  ]
};
