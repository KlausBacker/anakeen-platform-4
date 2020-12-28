import Chai from "chai";
import ChaiDom from "chai-dom";
import SmartFormTestData from "./lib/SmartFormTestData";

import sfTestData from "./data/SFToutType.json";

Chai.use(ChaiDom);
const expect = Chai.expect;

const SF1 = new SmartFormTestData("SF101 : Test des onglets à gauche");
SF1.readme = `
# Test SF101 :ox:

## Test affichage des onglets à gauche

`;
SF1.formConfig = {
  structure: sfTestData.structure,
  values: sfTestData.values,
  renderOptions: {
    document: {
      tabPlacement: "left"
    }
  }
};

SF1.automaticTests = [
  // ------------------- SF101-AT01 -----------
  {
    testId: "SF010-AT11",

    title: "Vérification de la position des onglets",

    testCallback: async function() {
      const selector = ".dcpDocument__tabs.k-tabstrip-left";
      expect(document.querySelector(selector) || {}, "Les onglets ne sont pas détectés à gauche").to.not.be.empty;
      expect(document.querySelector(selector) || {}, "Les onglets ne sont pas visibles").to.be.visible;

      return `Les onglets sont détectées à gauches`;
    },
    testCallbackArgs: {}
  }
];
export default SF1;
