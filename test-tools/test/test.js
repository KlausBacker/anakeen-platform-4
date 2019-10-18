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
    smartElementTest = await testContext.getSmartElement({ smartStructure: "DEVBILL"}, { bill_title: "Test State Smart Element"});
    testDeleted = await testContext.getSmartElement({ smartStructure: "DEVBILL"}, { bill_title: "Test Delete Smart Element"});
    smartElement = await testContext.getSmartElement("2405");

    // testPropertieValue = await testContext.getPropertyValue("");
    // testRole = await testContext.getAccount({ type: "role", login: "anakeen_test_user_role"}); // create custom test role

    // 2- Test Account
    testAccount = await testContext.getAccount("anakeen_test_user"); // Get user
    testAccount = await testAccount.addRole("anakeen_test_user_role"); // Add existing role
    testAccount = await testAccount.addRole("rtstdduiboss"); // Add custom test role

  });

  //On nettoie après le test
  after(async () => {
    await testContext.clean();
  });

  describe("1- Test Smart Element ==> 1 : on vérifie si l'état est 'wfam_bill_e1', 2 : on met à jour bill_title = 'Test State Smart Element Is Updated', 3 : on vérifie que le statut est 'alive', 4 : on passe une transition pour passer de state 'wfam_bill_e1' à 'wfam_bill_e2', 5 : On set le state à 'wfam_bill_e1', 6 : on test si le SE est Locked, 7 : on test si le SE à le workflow donné", () => {
    //On créé un SE et on test les droits qui nous intéresse
    it("1 : testAssertState", async () => {
      await expect(smartElementTest).has.state("wfam_bill_e1");
      await expect(smartElementTest).has.not.state("wfam_bill_e12");

      await expect(smartElementTest).for('anakeen_test_user').has.state("wfam_bill_e1");
      await expect(smartElementTest).for('anakeen_test_user').has.not.state("wfam_bill_e12");
    });

    it("2 : testUpdateAssert", async () => {
      const expectedValue = "Test State Smart Element Is Updated";
      const updateSmartElement = await smartElementTest.updateValues({ bill_title: expectedValue}); // Mise à jour de bill_title    
      // const getUpdatedValue = await updateSmartElement.getValue("bill_title").value; // à vérifier !
      const updatedValue = updateSmartElement.smartFields.bill_title.value; // Récupération de la nouvelle valeur bill_title
      expect(updatedValue).is.equal(expectedValue);
    });

    it("3 : testAliveAssert", async () => {
      await expect(smartElementTest).is.alive();
    });

    it("4 : testTransitionStateAssert", async () => {
      const setState = await smartElementTest.changeState({ transition: "t_wfam_bill_e1_e2" });
      await expect(setState).is.state("wfam_bill_e2");
    });

    it("5 : testSetStateAssert", async () => {
      const setState = await smartElementTest.setState("wfam_bill_e1");
      await expect(setState).is.state("wfam_bill_e1");
    });

    it("6 : testLockedAssert", async () => {
      await expect(smartElementTest).is.not.locked();
    });

    it("7 : testWorkflowAssert", async () => {
      await expect(smartElementTest).is.workflow("2170");
    });

    // it("6 : testDeletedAssert", async () => {
    //   await expect(testDeleted).is.alive();
    //   const del = await testDeleted.destroy();
    //   console.log(del.properties);
    //   await expect(del).is.not.alive(); // à tester
    // });
  });

  describe("2- Test Account ==> 1 : on vérifie si le role pour l'account 'anakeen_test_user' est 'rtstdduiboss', 2 : on test si ce smart element à la viewcontroller 'CV_IUSER_ACCOUNT'; 3 : on test si c'est un SE de type Account", () => {

    it("1 : testAccountRole", async () => {
      const roleExist = !!testAccount.roles.find((role) => role.login === "rtstdduiboss") // On définit un nom de rôle pour vérifier s'il existe pour ce login 
      expect(roleExist).is.true;
    });

    it("2 : testViewControlAccount", async () => {
      await expect(smartElement).viewControl("CV_IUSER_ACCOUNT");      
    });

    it("3 : testProfileAccount", async () => {
      await expect(smartElementTest).is.profile();
      await expect(smartElement).is.not.profile();
    });

    // it("3 : testViewAccess", async () => {
    //   await expect(smartElement).viewAccess("EGROUP");
    //   // expect(testeu).to.be.a("function");
    // });

    // it("4 : testSmartElementRight", async () => {
    //   await expect(smartElement).smartElementRight("EGROsUP");
    //   // expect(testeu).to.be.a("function");
    // });

  }); 

});
