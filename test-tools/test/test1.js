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

  // On initialisz un contexte de test
  before(async () => {
    testContext = currentContext.initTest();

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
  });

  // On nettoie après le test
  after(async () => {
    await testContext.clean();
  });

  describe("test the title", () => {
    it("1 : testTitle", async () => {
      await expect(seTest).has.title("Hello world");
    });
  });
  describe("test a field value", () => {
    it("1 : testAFieldValue", async () => {
      await expect(seTest).has.value("bill_content", "This is my message");
    });
  });
});
