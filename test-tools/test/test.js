//On charge les dépendances
const { Context, AnakeenAssertion } = require("../components/lib/TestTools");
const chai = require("chai");

const expect = chai.expect;

chai.use(AnakeenAssertion);

let currentContext;

//on crée et vérifie le lien avec le context Anakeen Platform
before(async () => {
  currentContext = await new Context("http://localhost:8080", "admin", "anakeen");
  console.log(context);
});

//On décrit le test
describe("Test d'assertions :", () => {
  let testContext;
  let smartElementTest;
  let testDeleted;
  let testRole;
  let smartElement;

  //On init un contexte de test
  //On créé les éléments qu'on va utiliser
  before(async () => {
    testContext = currentContext.initTest();

    // 1- Test Smart Element
    smartElementTest = await testContext.getSmartElement({ smartStructure: "DEVBILL"}, { bill_title: "Test State Smart Elements", bill_content: "Content State Smart Element"});
    testDeleted = await testContext.getSmartElement({ smartStructure: "DEVBILL"}, { bill_title: "Test Delete Smart Element"});
    smartElement = await testContext.getSmartElement("2405");
    // smartElement2 = await testContext.getSmartElement("2407");

    // testPropertieValue = await testContext.getPropertyValue("");
    // testRole = await testContext.getAccount({ type: "role", login: "anakeen_test_user_role"}); // create custom test role

    // 2- Test Account
    testAccount = await testContext.getAccount("admin"); // Get user
    testAccount = await testAccount.addRole("anakeen_test_user_role"); // Add existing role
    testAccount = await testAccount.addRole("rtstdduiboss"); // Add custom test role

  });

  //On nettoie après le test
  after(async () => {
    await testContext.clean();
  });

  describe("1- Test Smart Element ==> 1 : on vérifie si l'état est 'wfam_bill_e1', 2 : on met à jour bill_title = 'Test State Smart Element Is Updated', 3 : on vérifie que le statut est 'alive', 4 : on passe une transition pour passer de state 'wfam_bill_e1' à 'wfam_bill_e2', 5 : On set le state à 'wfam_bill_e1', 6 : on test si le SE est Locked, 7 : on test si le SE à le workflow donné", () => {
    //On créé un SE et on test les droits qui nous intéresse
    // it("1 : testAssertState", async () => {
    //   await expect(smartElementTest).has.state("wfam_bill_e1");
    //   await expect(smartElementTest).has.not.state("wfam_bill_e12");
    // });

    // it("TEST : testAssertCanSave", async () => {
    //   await expect(smartElementTest).to.have.value("bill_title", "Test State Smart Elements");
    //   await expect(smartElementTest).canSave({ bill_title: "expectedValue"});
    //   await expect(smartElementTest).to.have.value("bill_title", "Test State Smart Elements");
    //   // console.log(smartElementTest);
    //   const test = await smartElementTest.getPropertiesValues();
    //   console.log(test);
    // });

    // it("2 : testUpdateAssert", async () => {
    //   const expectedValue = "Test State Smart Element Is Updatedsss";
    //   const updateSmartElement = await smartElementTest.updateValues({ bill_title: expectedValue, bill_content: expectedValue}); // Mise à jour de bill_title   
    //   await expect(updateSmartElement).to.have.value("bill_title", expectedValue);
    //   await expect(updateSmartElement).to.have.value("bill_content", expectedValue);
    //   // console.log(updateSmartElement);
    // });

    // it("2 : testUpdateAssert", async () => {
    //   const expectedValue = "Test State Smart Element Is Updated";
    //   const updateSmartElement = await smartElementTest.updateValues({ bill_title: expectedValue}); // Mise à jour de bill_title   
    //   // await expect(smartElementTest).to.have.value({"bill_title": expectedValue});
    // });

    // it("3 : testAliveAssert", async () => {
    //   await expect(smartElementTest).is.alive();
    // });

    it("4 : testTransitionStateAssert", async () => {
      await expect(smartElementTest).canChangeState("t_wfam_bill_e1_e2", {test: "test"});
      // await expect(smartElementTest).canChangeState("t_wfam_bill_e1_e2");
      await expect(smartElementTest).to.have.state("wfam_bill_e1");
      // await expect(smartElementTest).not.canChangeState("t_wfam_bill_e1_e3");
      // await expect(smartElementTest).to.have.state("wfam_bill_e1");
    });

    // it("5 : testSetStateAssert", async () => {
    //   const setState = await smartElementTest.setState("wfam_bill_e1");
    //   await expect(setState).is.state("wfam_bill_e1");
    // });

    // it("6 : testLockedAssert", async () => {
    //   await expect(smartElementTest).is.not.locked();
      // await expect(smartElementTest).for('admin').is.not.locked();
    // });

    // it("7 : testWorkflowAssert", async () => {
    //   await expect(smartElementTest).is.workflow("2170");
    // });

    // it("8 : testDeletedAssert", async () => {
    //   await expect(testDeleted).is.alive();
    //   const del = await testDeleted.destroy();
    //   // console.log(del.properties.initid);
    //   await expect(del).for("admin").is.not.alive(); // à tester
    // });
  });

  describe("2- Test Account ==> 1 : on vérifie si le role pour l'account 'anakeen_test_user' est 'rtstdduiboss', 2 : on test si ce smart element à la viewcontroller 'CV_IUSER_ACCOUNT'; 3 : on test si c'est un SE de type Account", () => {

    // it("1 : testAccountRole", async () => {
    //   const roleExist = !!testAccount.roles.find((role) => role.login === "rtstdduiboss") // On définit un nom de rôle pour vérifier s'il existe pour ce login 
    //   expect(roleExist).is.true;
    // });

    // it("2 : testViewControlAccount", async () => {
    //   await expect(smartElement).viewControl("CV_IUSER_ACCOUNT");      
    // });

    // it("3 : testProfileAccount", async () => {
    //   await expect(smartElementTest).is.profile();
    //   await expect(smartElement).is.not.profile();
    // });

    // it("4 : testViewAccess", async () => {
    //   await expect(smartElement).for('anakeen_test_user').has.viewAccess("EUSER");
    //   // expect(testeu).to.be.a("function");
    // });

    // it("4 : testSmartElementRight", async () => {
    //   await expect(smartElement2).smartElementRight("EUSER");
    // });

    // it("4 : testSmartElementRight", async () => {
    //   await expect(smartElement).transitionRight("t_wfam_bill_e1_e2");
    // });

  });

});
