/* eslint-disable no-unused-vars */
import Vue from "vue";
import setup from "@anakeen/user-interfaces/components/lib/setup.esm";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm.esm";

//import AnkSmartForm from "../../../user-interfaces/components/src/AnkSmartForm";
import AnkSmartFormVue from "../../../user-interfaces/components/src/AnkSmartForm/AnkSmartForm.vue";

import "@anakeen/user-interfaces/components/scss/AnkSmartElement.scss";

import { addons } from "@storybook/addons";

Vue.use(setup);

/**
 * Initialize test from Smart Form data
 * @param SFXXXData
 * @returns {any}
 */
function initTestData(SFXXXData) {
  const TestSFXXX = Template.bind({});
  TestSFXXX.parameters = {
    anakeenReadme: SFXXXData.readme,
    automaticTests: SFXXXData.automaticTests,
    userTests: SFXXXData.userTests
  };
  TestSFXXX.storyName = SFXXXData.title;
  if (!SFXXXData.formConfig.title) {
    SFXXXData.formConfig.title = SFXXXData.title;
  }

  const callbacks = {};
  SFXXXData.automaticTests.forEach(test => {
    callbacks[test.testId] = {
      callback: test.testCallback,
      args: test.testCallbackArgs
    };
  });

  TestSFXXX.args = {
    closeConfirmation: false,
    configForm: JSON.stringify(SFXXXData.formConfig, null, 2),
    sfData: SFXXXData
  };
  return TestSFXXX;
}

export default {
  title: "Ui Component/Smart Form/Tests",
  component: AnkSmartFormVue,
  argTypes: {
    configForm: { description: "Configuration du <b>Smart Form</b>", control: { type: "text" } },
    config: { description: "Configuration du <b>Smart Form</b>", control: { type: "object" } },

    options: { description: "Options avancées de configuration", control: { type: "object" } },
    initid: { table: { disable: true } },
    viewId: { table: { disable: true } },
    sfData: { table: { disable: true } },
    revision: { table: { disable: true } },
    autoUnload: { table: { disable: true } },
    browserHistory: { table: { disable: true } }
  }
};

// noinspection JSUnusedLocalSymbols
const Template = (args, { argTypes }) => ({
  props: {
    closeConfirmation: { type: Boolean, default: false },
    configForm: { type: String, default: "" },
    sfData: { type: Object, default: () => {} }
  },

  components: {
    "ank-smart-form": () => {
      return AnkSmartForm;
    }
  },
  template:
    '<ank-smart-form ref="smartFormTest" @smartElementLoaded="onLoaded"  :config="getConfig" :options="getOptions" />',

  beforeCreate() {},

  methods: {
    onLoaded() {
      const channel = addons.getChannel();
      const controller = this.$refs.smartFormTest.smartElementWidget;
      channel.removeAllListeners("executeCallbackTest");
      channel.on("executeCallbackTest", (testId, eventId) => {
        const callbackRef = this.callbackTests[testId];
        if (callbackRef) {
          this.sfData.smartController = controller;
          const ft = callbackRef.callback;
          const arg = callbackRef.args;
          ft.bind(this.sfData)(arg)
            .then(message => {
              channel.emit(eventId, { success: true, message: message });
            })
            .catch(err => {
              channel.emit(eventId, { success: false, message: err.message });
            });
        } else {
          channel.emit(eventId, { message: "Cannot test : no callback declared" });
        }
      });
      if (this.sfData && this.sfData.listeners) {
        for (const [listenKey, listenCallback] of Object.entries(this.sfData.listeners)) {
          if (listenCallback) {
            if (listenKey === "loaded") {
              listenCallback.bind(this.sfData)(controller);
            } else {
              controller.addEventListener(listenKey, {}, (...$args) => {
                listenCallback.bind(this.sfData)(...$args);
              });
            }
          }
        }
      }
    }
  },

  computed: {
    getConfig: function() {
      return JSON.parse(this.configForm);
    },

    getOptions: function() {
      return {
        withCloseConfirmation: this.closeConfirmation
      };
    },

    callbackTests: function() {
      const callbacks = {};
      if (this.sfData) {
        this.sfData.automaticTests.forEach(test => {
          callbacks[test.testId] = {
            callback: test.testCallback,
            args: test.testCallbackArgs
          };
        });
      }
      return callbacks;
    }
  }
});

// ---------------- SF001 -------------------------------
import SF001Data from "./SmartFormData/SmartFormSF001.js";
export const TestSF001 = initTestData(SF001Data);

// ---------------- SF002 -------------------------------
import SF002Data from "./SmartFormData/SmartFormSF002.js";
export const TestSF002 = initTestData(SF002Data);

// ---------------- SF010 -------------------------------
import SF010Data from "./SmartFormData/SmartFormSF010.js";
export const TestSF010 = initTestData(SF010Data);

// ---------------- SF011 -------------------------------
import SF011Data from "./SmartFormData/SmartFormSF011.js";
export const TestSF011 = initTestData(SF011Data);
// ---------------- SF0100 -------------------------------
import SF100Data from "./SmartFormData/SmartFormSF100.js";
export const TestSF100 = initTestData(SF100Data);
// ---------------- SF0101 -------------------------------
import SF101Data from "./SmartFormData/SmartFormSF101.js";
export const TestSF101 = initTestData(SF101Data);
