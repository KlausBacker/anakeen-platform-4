import "./searchRender.css";
import searchUISEGridProcess from "./searchUISEGrid";
import reportViewGridProcess from "../../Report/Render/reportViewGrid";
import searchUIEventEditProcess from "./searchUIEventEdit";
import searchUIEventViewProcess from "./searchUIEventView";
import SearchConditions from "./Refactor/SearchConditions.vue";
import setup from "@anakeen/user-interfaces/components/lib/setup.esm";

import Vue from "vue";
Vue.use(setup);

window.ank.smartElement.globalController.registerFunction("dSearch", controller => {
  Vue.$_globalI18n.recordCatalog().then(() => {
    searchUISEGridProcess(controller);
    reportViewGridProcess(controller);
    searchUIEventEditProcess(controller);
    searchUIEventViewProcess(controller);

    controller.addEventListener(
      "ready",
      {
        name: "report:edit:condition",
        check: document => {
          return document.renderMode === "edit" && document.type === "search";
        }
      },
      () => {
        new (Vue.extend(SearchConditions))({
          el: ".search-conditions-component",
          propsData: {
            controllerProxy: (action, ...args) => controller[action](...args)
          }
        });
      }
    );
  });
});
