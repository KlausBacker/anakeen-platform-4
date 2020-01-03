//On charge les dépendances
const { Context, AnakeenAssertion } = require("../components/lib/TestTools");
const chai = require("chai");
const expect = chai.expect;

chai.use(AnakeenAssertion);

let currentContext;

//on crée et vérifie le lien avec le context Anakeen Platform
before(async () => {
  currentContext = await new Context("http://localhost:10083", "admin", "anakeen");
});

//On décrit le test
describe("Test basique :", function() {
  let testContext;
  let seTest;
  let allType;
  let userBill;

  // On initialisz un contexte de test
  before(async () => {
    testContext = currentContext.initTest();

    userBill = await testContext.createAccount({
      login: "testbill1",
      roles: ["bill_writer", "bill_reader"]
    });
    // Create a Smart Element for test
    seTest = await testContext.createSmartElement(
      {
        smartStructure: "DEVBILL"
      },
      {
        bill_title: "Hello world",
        bill_content: "This is my message"
      }
    );

    allType= await testContext.getSmartElement("TST_ALL_COMPLET");
  });

  // On nettoie après le test
  after(async () => {
    await testContext.clean();
  });

  describe("test the title", () => {
    it("1 : testTitle", async () => {
      // Test with user "testbill1"
      await expect(seTest).for(userBill.login).has.title("Hello world");
      await expect(allType).has.value("test_ddui_all__integer_array", [
        244, 68732, -4563
      ]);
      let props=await allType.getPropertiesValues();
    console.log(props);

    });
  });
});
