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
describe("testAssert", () => {
  let testContext;
  let testState;
  let testDeleted;
  let testRole;
  let testPropertyValue;

  //On init un contexte de test
  //On créé les éléments qu'on va utiliser
  before(async () => {
    testContext = currentContext.initTest();

    testPropertyValue = await testContext.getSmartElement({ smartStructure: "DEVBILL"}, { bill_title: "Test Property value"});
    // // testPropertyValue = await testContext.getSmartElement("2427");
    // testPropertieValue = await testContext.getPropertyValue("");
    // testDeleted = await testContext.getSmartElement({ smartStructure: "DEVBILL"}, { bill_title: "Test Delete Smart Element"});
    // testDeleted = await testDeleted.destroy();

    // testRole = await testContext.getAccount({ type: "role", login: "anakeen_test_user_role"}); // create custom test role
    // testAccount = await testContext.getAccount("anakeen_test_user"); // Create user
    // testAccount = await testAccount.addRole("anakeen_test_user_role"); // Add existing role
    // testAccount = await testAccount.addRole("rtstdduiboss"); // Add custom test role

  });

  //On nettoie après le test
  after(async () => {
    // await testContext.clean();
  });

  //On créé un SE et on test les droits qui nous intéresse
  // it("testAssertState", async () => {
  //   await expect(testState).is.state("wfam_bill_e1");
  // });

  // it("testUpdateAssert", async () => {
  //   const expectedValue = "Update test assert title";
  //   const updateSmartElement = await testState.updateValues({ bill_title: expectedValue});
  //   const updatedValue = updateSmartElement.getValue("bill_title").value;
  //   expect(updatedValue).is.equal(expectedValue);
  // });

  // it("testAliveAssert", async () => {
  //   await expect(testState).is.alive();
  // });

  // it("testDeletedAssert", async () => {
  //   await expect(testDeleted).is.not.alive();
  // });

  // it("testAccountAssert", async () => {
  //   const roleExist = !!testAccount.roles.find((role) => role.login === "toto")
  //   expect(roleExist).is.true;
  // });

  // it("testStateAssert", async () => {
  //   const setState = await testState.setState("wfam_bill_e2");
  //   await expect(setState).is.state("wfam_bill_e2");
  // });

  // it("testStateAssert", async () => {
  //   const setState = await testState.changeState({ transition: "t_wfam_bill_e1_e2" });
  //   await expect(setState).is.state("wfam_bill_e2");
  // });

  it("test", async () => {
    // const setState = await testState.changeState({ transition: "t_wfam_bill_e1_e2" });
    // await expect(setState).is.state("wfam_bill_e2");
    const propValues = await testPropertyValue.getPropertiesValues();
    const fieldValue = await testPropertyValue.getValue("bill_title");

    expect(propValues.title).is.equal(fieldValue.value);

    // console.log(testPropertyValue);
  });

});
