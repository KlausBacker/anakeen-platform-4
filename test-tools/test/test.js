const chai = require("chai");
const { Context, AnakeenAssertion } = require("../components/lib/TestTools.js")
//On charge nos asserts maison
/* import { Context, AnakeenAssertion } from "../components/src/index"; */
const expect = chai.expect;

chai.use(AnakeenAssertion);

let currentContext = new Context("http://localhost:8080/", "admin", "anakeen");



//On décrit le test
describe("haveProfileTest", () => {

    let smartElement1;
    let user2;
    let testContext;


    //On init un contexte de test
    //On créé les éléments qu'on va utiliser
    before(async () => {

        testContext = currentContext.initTest();
        smartElement1 = await testContext.getSmartElement("ADMINCENTER");
        // console.log(smartElement1.getPropertyValue("security").profil);
    });
    
    //On nettoie après le test
   /*  after(async () => {
        await testContext.clean();
    });
     */
    //On créé un SE et on test les droits qui nous intéresse
    it("droitCompteRenduRedaction", async () => {
        expect(smartElement1).to.haveProfile("ADMINCENTER");
        // expect(smartElement1).to.haveWorkflow("ADMINCENTER");
    });

});
