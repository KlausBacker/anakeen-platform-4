/* eslint-disable no-unused-vars,no-undef */
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
  let billLogin = "billythekid";
  let simpleuserLogin;
  let simpleuser2Login;
  let zooUser1Login;
  let accountsManagerUserLogin;
  let simpleUser;
  let accountsManagerUser;
  let seBill;
  let seSimpleUser;
  let seBilltoBeDeleted;
  let seZooUser1;

  //On init un contexte de test
  //On créé les éléments qu'on va utiliser
  before(async () => {
    testContext = currentContext.initTest();

    // 1- Accounts

    simpleuserLogin = "test_tools_user1";
    simpleuser2Login = "test_tools_user2";
    zooUser1Login = "zoo.user1";
    accountsManagerUserLogin = "accounts_manager_user1";

    simpleUser = await testContext.createAccount({
      login: simpleuserLogin
    });

    await testContext.createAccount({
      login: simpleuser2Login
    });

    await testContext.createAccount({
      login: billLogin,
      roles: ["bill_writer", "bill_reader"]
    });

    accountsManagerUser = await testContext.createAccount({
      login: accountsManagerUserLogin,
      roles: ["accounts_manager_role"]
    });

    await accountsManagerUser.addRole("accounts_manager_role");

    // 2 - Smart Elements

    seBill = await testContext.createSmartElement(
      {
        smartStructure: "DEVBILL"
      },
      {
        bill_title: "Test State Smart Elements",
        bill_content: "Content State Smart Element"
      }
    );

    seBilltoBeDeleted = await testContext.createSmartElement(
      {
        smartStructure: "DEVBILL"
      },
      {
        bill_title: "Test State Smart Element Deleted",
        bill_content: "Content State Smart Element"
      }
    );
    seSimpleUser = await testContext.getSmartElement(simpleUser.fid);

    seZooUser1 = await testContext.getSmartElement("TST_DDUI_USER1");
  });

  //On nettoie après le test
  after(async () => {
    await testContext.clean();
  });

  describe("1- Test Smart Element ==> \n\t>1 : on vérifie si l'état est 'wfam_bill_e1',\n\t>2 : on met à jour bill_title = 'Test State Smart Element Is Updated', \n\t>3 : on vérifie que le statut est 'alive',\n\t>4 : on passe une transition pour passer de state 'wfam_bill_e1' à 'wfam_bill_e2', \n\t>5 : On set le state à 'wfam_bill_e1', \n\t>6 : on test si le SE est Locked, \n\t>7 : on test si le SE à le workflow donné", () => {
    // On créé un SE et on test les droits qui nous intéresse
    it("1 : testAssertState", async () => {
      await expect(seBill).has.state("wfam_bill_e1");
      await expect(seBill).has.not.state("wfam_bill_e12");
    });

    it("2 : testUpdateAssert", async () => {
      const expectedValue = "Test State Smart Element Is Updated";
      const updateSmartElement = await seBill.updateValues({
        bill_title: expectedValue,
        bill_content: expectedValue
      }); // Mise à jour de bill_title
      await expect(updateSmartElement).to.have.value("bill_title", expectedValue);
      await expect(updateSmartElement).to.have.value("bill_content", expectedValue);
      await expect(updateSmartElement).to.have.values({
        bill_title: expectedValue,
        bill_content: expectedValue
      });
    });

    it("3 : testAliveAssert", async () => {
      await expect(seBill).is.alive();
      await expect(seBill)
        .for(billLogin)
        .is.alive();
      await expect(seBill)
        .for(simpleuserLogin)
        .to.not.have.smartElementRight("view");
    });

    it("4 : testTransitionStateAssert", async () => {
      await expect(seBill).to.have.state("wfam_bill_e1");
      await expect(seBill).canExecuteTransition("t_wfam_bill_e1_e2");
      await expect(seBill).to.have.state("wfam_bill_e1");
      await expect(seBill).not.canExecuteTransition("t_wfam_bill_e1_e3");
      await expect(seBill).to.have.state("wfam_bill_e1");
    });

    it("5 : testSetStateAssert", async () => {
      await expect(seBill).to.have.state("wfam_bill_e1");
      const setState = await seBill.setState("wfam_bill_e2");
      await expect(setState).to.have.state("wfam_bill_e2");
    });

    it("6 : testLockedAssert", async () => {
      await expect(seBill).is.not.locked();
    });

    it("7 : testWorkflowAssert", async () => {
      await expect(seBill).is.workflow("WDOC_BILL");
    });

    it("8 : testDeletedAssert", async () => {
      await expect(seBilltoBeDeleted).is.alive();
      const seDeleted = await seBilltoBeDeleted.destroy();
      await expect(seDeleted).is.not.alive();
    });

    it("9 : testAssertCanUpdateValues", async () => {
      await expect(seBill).to.have.value("bill_title", "Test State Smart Element Is Updated");
      await expect(seBill).canUpdateValues({ bill_title: "expectedValue" });
      await expect(seBill).to.have.value("bill_title", "Test State Smart Element Is Updated");
    });
  });

  describe("2- Test Account ==> \n\t>1 : on vérifie si le role pour l'account 'anakeen_test_user' est 'rtstdduiboss', \n\t>2 : on test si ce smart element à la viewcontroller 'CV_IUSER_ACCOUNT'; \n\t>3 : on test si c'est un SE de type Account", () => {
    it("2 : testViewControlAccount", async () => {
      await expect(seSimpleUser)
        .for("zoo.user1")
        .viewControl("CV_IUSER_ACCOUNT");
    });

    it("3 : viewControl", async () => {
      await expect(seSimpleUser)
        .for(accountsManagerUserLogin)
        .viewControl("CV_IUSER_ACCOUNT");
    });

    it("4 : fieldAccess", async () => {
      await expect(seSimpleUser)
        .for(accountsManagerUserLogin)
        .fieldAccess("FALL_IUSER");
    });

    it("5 : viewAccess", async () => {
      await expect(seSimpleUser)
        .for(accountsManagerUserLogin)
        .to.have.viewAccess("EUSER");
      await expect(seSimpleUser)
        .for(simpleuserLogin)
        .to.not.have.viewAccess("EUSER");
      await expect(seSimpleUser)
        .for(accountsManagerUserLogin)
        .to.not.have.viewAccess("INEXISTENT_VIEW");
    });

    it("6 : testProfile", async () => {
      await expect(seSimpleUser).is.profile(seSimpleUser);
      await expect(seSimpleUser).is.profile("PRF_IUSER_OWNER");
    });
  });

  describe("Test access rights", () => {
    it("smartElementRight", async () => {
      await expect(seSimpleUser)
        .for(simpleuserLogin)
        .to.have.smartElementRight("view");
      await expect(seSimpleUser)
        .for(simpleuser2Login)
        .to.have.smartElementRight("view");
      await expect(seSimpleUser)
        .for(simpleuserLogin)
        .to.have.smartElementRight("edit");
      await expect(seSimpleUser)
        .for(simpleuser2Login)
        .to.not.have.smartElementRight("edit");
      await expect(seSimpleUser)
        .for(accountsManagerUserLogin)
        .to.have.smartElementRight("view");
      await expect(seSimpleUser)
        .for(accountsManagerUserLogin)
        .to.have.smartElementRight("edit");
      await expect(seSimpleUser)
        .for(accountsManagerUserLogin)
        .to.have.smartElementRight("delete");
    });
    it("transitionRight", async () => {
      await expect(seBill)
        .for(billLogin)
        .have.transitionRight("t_wfam_bill_e1_e2");
    });
    it("transitionNotRight", async () => {
      await expect(seBill)
        .for(simpleuserLogin)
        .have.not.transitionRight("t_wfam_bill_e1_e2");
    });
    it("smartFieldRight", async () => {
      await expect(seZooUser1)
        .for(zooUser1Login)
        .smartFieldRight("us_passwd1", "write");
      await expect(seZooUser1)
        .for(zooUser1Login)
        .smartFieldRight("us_meid", "none");
      await expect(seZooUser1)
        .for(zooUser1Login)
        .smartFieldRight("us_login", "read");
      await expect(seZooUser1)
        .for(zooUser1Login)
        .smartFieldRight("us_fr_intranet", "none");
    });
    it("values", async () => {
      await expect(seBill).to.have.values(
        { bill_title: "Test State Smart Element Is Updated" },
        { bill_content: "Test State Smart Elements" }
      );
    });
  });
});
