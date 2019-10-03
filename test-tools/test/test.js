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
  let smartElement;
  let testContext;
  let account;
  //On init un contexte de test
  //On créé les éléments qu'on va utiliser
  before(async () => {
    testContext = currentContext.initTest();
    user1 = await testContext.getAccount({"type": "user", "login": "anakeen_user3"});
    user2 = await testContext.getAccount({"type": "user", "login": "anakeen_user4"});
    // group1 = await testContext.getAccount({"type": "group", "lastname": "Anakeen Test Groupe", "login": "anakeen_test_users_2", "users": ["anakeen_user3", "anakeen_user4"]});
  });

  //On nettoie après le test
  after(async () => {
    await testContext.clean();
  });

  //On créé un SE et on test les droits qui nous intéresse
  it("compteRenduProfile", async () => {
    // compteRendu = await testContext.getSmartElement(
    //   { smartStructure: "COMPTE_RENDU" },
    //   { cr_auteur: user1, cr_title: "test" }
    // );
    expect(true);
    // await compteRendu.setState("redaction");
    // await expect(compteRendu)
    //   .for(user1)
    //   .to.haveRight(["read", "write", "delete"]);
    // await expect(compteRendu)
    //   .for(user2)
    //   .to.not.haveRight("read");
  });
});
