import "./searchRender.css";
import searchUISEGridProcess from "./searchUISEGrid";
import reportViewGridProcess from "../../Report/Render/reportViewGrid";
import searchUIEventEditProcess from "./searchUIEventEdit";
import searchUIEventViewProcess from "./searchUIEventView";
import ankController from "../../../../../../components/lib/AnkController";
import SearchConditions from "./Refactor/SearchConditions.vue";

import Vue from "vue";

ankController.on("controllerReady", inner => {
  inner.registerFunction("dSearch", controller => {
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