import Chai from "chai";
import ChaiDom from "chai-dom";
import SmartFormTestData from "./lib/SmartFormTestData";

import sfTestData from "./data/SFToutType.json";

Chai.use(ChaiDom);
const expect = Chai.expect;

const SF1 = new SmartFormTestData("SF100 : Test des onglets");
SF1.readme = `
# Test SF100 :ox:

## Test affichage des onglets

Tests des valeurs prérenseignées dans le formulaire.
Le formulaire contient 2 champs textes et un tableau de 2 colonnes.
`;
SF1.formConfig = {
  structure: sfTestData.structure,
  values: sfTestData.values
};

SF1.automaticTests = [
  // ------------------- SF101-AT01 -----------
  {
    testId: "SF010-AT11",

    title: "Vérification de la position des onglets",

    testCallback: async function() {
      const selector = ".dcpDocument__tabs.k-tabstrip-top";

      expect(document.querySelector(selector) || {}, "Les onglets ne sont pas détectés en haut").to.not.be.empty;
      expect(document.querySelector(selector) || {}, "Les onglets ne sont pas visibles").to.be.visible;
      return `Les onglets sont détectées en haut`;
    },
    testCallbackArgs: {}
  }
];
export default SF1;
