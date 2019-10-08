//On charge les dépendances
const { Context, AnakeenAssertion } = require("../components/lib/TestTools");
const chai = require("chai");

const expect = chai.expect;

chai.use(AnakeenAssertion);

let currentContext;

//on crée et vérifie le lien avec le context Anakeen Platform
before(async () => {
  currentContext = await new Context("http://localhost:8080", "admin", "anakeen");
});

//On décrit le test
describe("profileCompteRendu", () => {
  let testContext;
  let compteRendu;
  //On init un contexte de test
  //On créé les éléments qu'on va utiliser
  before(async () => {
    testContext = currentContext.initTest();
    // const user = await testContext.getAccount("anakeen_user6");
    compteRendu = await testContext.getSmartElement({ smartStructure: "DEVBILL"}, { bill_title: "Mon titre à la création"});
    // const role = await testContext.getAccount("anakeen_user_test_role");
    // const result2 = await user.removeRole(role);
  
  });

  //On nettoie après le test
  after(async () => {
    await testContext.clean();
  });

  //On créé un SE et on test les droits qui nous intéresse
  it("compteRenduProfile", async () => {
  
    const updatedValue = "Mon titre a été modifié";
    const updateCompteRendu = await compteRendu.updateValues({ bill_title: updatedValue});
    expect(updateCompteRendu.getValue("bill_title").value).to.be.equal(updatedValue);
  });
});
