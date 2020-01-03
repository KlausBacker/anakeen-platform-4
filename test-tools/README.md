# Anakeen Platform Test tools Documentation


**Démarrer les tests**

Dans le dossier du module test-tools lancer la commande :
`./node_modules/.bin/mocha`

**Fonctionnement d'un test**

* La fonction `describe()` permet de décrire le test.
```js
describe("Desciption du test", () => {
});
```


* La fonction `before()` permet de définir ce qu'il se passe avant le test. On init un contexte de test et on créé les éléments qu'on va utiliser.
Exemple :
```js
before(async () => {
    let testContext;
    let simpleuserLogin;
    let simpleUser;

    testContext = currentContext.initTest();

    // On créer un Account
    simpleuserLogin = "test_tools_user1";
    simpleUser = await testContext.getAccount({
      login: simpleuserLogin,
      type: "user",
      roles: []
    });
});
```


* La fonction `after()` permet de définir ce qu'il se passe après le test. On nettoie après le test tout ce qu'on à créé ou modifié gràce à la fonction `clean()`.
Exemple :
```js
after(async () => {
    await testContext.clean();
});
```


* Exemple d'un test complet :
```js
describe("Test d'assertions :", () => {
  let testContext;
  let simpleuserLogin;
  let simpleUser;
  let seBill;
  let seSimpleUser;

  //On init un contexte de test
  //On créé les éléments qu'on va utiliser
  before(async () => {
    testContext = currentContext.initTest();

    // 1- Accounts
    simpleuserLogin = "test_tools_user1";

    simpleUser = await testContext.getAccount({
      login: simpleuserLogin,
      type: "user",
      roles: []
    });

    // 2 - Smart Elements
    seBill = await testContext.getSmartElement({
      smartStructure: "DEVBILL"
    },
    {
      bill_title: "Test State Smart Elements",
      bill_content: "Content State Smart Element"
    });

    seSimpleUser = await testContext.getSmartElement(simpleUser.fid);

    describe("Test d'assertions :", () => {
        // Notre test
        it("1 : testAssertState", async () => {
            await expect(seBill).has.state("wfam_bill_e1");
            await expect(seBill).has.not.state("wfam_bill_e12");
        });
    });

    //On nettoie après le test
    after(async () => {
        await testContext.clean();
    });

});
```


**Chaines de langage :**

Les éléments suivants sont fournis en tant que getters pouvant être chaînés pour améliorer la lisibilité des assertions.

* to
* be
* been
* is
* that
* which
* and
* has
* have
* with
* at
* of
* same
* but
* does
* still

**Liste des méthodes de test :**

Droit

* for(account: string)
* smartFieldRight(smartfield: string, acl: string)
* smartElementRight(acl: string)
* viewAccess(viewId: string)
* transitionRight(transition: string)

Propriété

* profile(smartElement: string | SmartElement)
* viewControl(smartElementLogicalName: string | SmartElement)
* workflow(smartElementLogicalName: string)
* fieldAccess(smartElementLogicalName: string | SmartElement)
* state(stateReference: string)
* locked()
* alive()

Attribut

* values(smartFieldValues: object)
* value(smartField: string, expectedValue: any)

Comportement

* canChangeState(transition: string, askValues?: object)
* canSave(values: ISmartElementValues)

**Descriptions des méthodes :**

**Droit**


* `for(account: string)` permet de changer d’utilisateur pour un test. Prend un argument account de type string qui est le login name du compte.
Exemple :
```js
it("transitionRight", async () => {
    await expect(seBill).for("zo.user1").have.transitionRight("t_wfam_bill_e1_e2");
});
```


* `smartfieldRight(smartfield: string, acl: string)` permet de tester les droits d’accès (read, write et none) d’un smart field. Elle prend 2 arguments : le nom du smartfield et le nom du droit acl.
Exemple :
```js
it("smartFieldRight", async () => {
    await expect(seZooUser1).for(zooUser1Login).smartFieldRight("us_passwd1", "write");
    await expect(seZooUser1).for(zooUser1Login).smartFieldRight("us_meid", "write");
});
```


* `smartElementRight(acl: string)` permet de verifier les droits d’accès d’un smart element. Elle prend 1 argument acl de type string : le nom du droit.
Exemple :
```js
it("smartElementRight", async () => {
    await expect(seSimpleUser).for(simpleuserLogin).to.have.smartElementRight("view");
    await expect(seSimpleUser).for(simpleuserLogin).to.have.smartElementRight("edit");
    await expect(seSimpleUser).for(accountsManagerUserLogin).to.have.smartElementRight("delete");
});
```


* `viewAccess(viewId: string)` permet de contrôler qu’un utilisateur à accès à telle vue d’un smart element. Prend 1 argument viewId de type string : le nom de la vue.
Exemple :
```js
it("5 : viewAccess", async () => {
    await expect(seSimpleUser).for(accountsManagerUserLogin).to.have.viewAccess("EUSER");  
    await expect(seSimpleUser).for(accountsManagerUserLogin).to.not.have.viewAccess("INEXISTENT_VIEW");  
});
```


* `transitionRight(transition: string)` permet de verifier qu'un utlisateur a le droit d'effectuer une transition. Elle prend 1 argument transition : le nom de la tranisition.
Exemple :
```js
it("transitionRight", async () => {
    await expect(seBill).for(simpleuserLogin).have.transitionRight("t_wfam_bill_e1_e2");
});
```


**Propriété**


* `profile(smartElement: string | SmartElement)` permet de vérifier qu'un smart element a le profil donné. 1 arguement smartElement de type string ou SmartElement.
Exemple : 
```js
it("testProfile", async () => {
    await expect(seSimpleUser).is.profile(seSimpleUser);
    await expect(seSimpleUser).is.profile("PRF_IUSER_OWNER");
});
```


* `viewControl(smartElementLogicalName: string | SmartElement)` permet de vérifier si le smart element à le contrôle de vu donné. 1 argument : le LogicalName.
Exemple : 
```js
it("testViewControlAccount", async () => {
    await expect(seSimpleUser).for("zoo.user1").viewControl("CV_IUSER_ACCOUNT");      
});
```


* `workflow(smartElementLogicalName: string)` permet de vérifier si le smart element a un workflow. 1 argument : le LogicalName du SE.
Exemple : 
```js
it("7 : testWorkflowAssert", async () => {
    await expect(seBill).is.workflow("WDOC_BILL");
});
```


* `fieldAccess(smartElementLogicalName: string | SmartElement)` permet de vérifier que le smart element a le field access donné. 1 argument : le LogicalName.
Exemple : 
```js
it("fieldAccess", async () => {
    await expect(seSimpleUser).for(accountsManagerUserLogin).fieldAccess("FALL_IUSER");
});
```


* `state(stateReference: string)` permet de vérifier l'état du SE donné en paramètre. 1 argument : la référence de l'état.
Exemple : 
```js
it("testAssertState", async () => {
    await expect(seBill).has.state("wfam_bill_e1");
    await expect(seBill).has.not.state("wfam_bill_e12");
});
```


* `locked()` permet de vérifier si le SE est vérouillé.
Exemple : 
```js
it("testLockedAssert", async () => {
    await expect(seBill).is.not.locked();
});
```


* `alive()` permet de vérifier l'état du SE.
Exemple : 
```js
it("testAliveAssert", async () => {
    await expect(seBill).is.alive();
});
```


**Attribut**


* `values(smartFieldValues: object)` permet de vérifier la valeur de plusieurs smarts fields. 1 argument : un objet avec clé et la valeur expectée.
Exemple : 
```js
it("values", async () => {
    await expect(seBill).to.have.values( {bill_title: "Test State Smart Elements"}, {bill_content: seBill});
});
```


* `value(smartField: string, expectedValue: any)` permet de vérifier la valeur d'un smart field. 2 argument : le nom du SF et la valeur expectée.
Exemple : 
```js
it("value", async () => {
    await expect(updateSseBillmartElement).to.have.value("bill_title", "Test State Smart Elements");
});
```


**Comportement**


* `canChangeState(transition: string, askValues?: object)` permet de vérifier qu'un utilisateur peut faire un changement d'état, un revert est automatiquement exécuté pour revenir à l'état d'origine. 2 argument : la référence de la transition et un objet ask facultatif.
Exemple : 
```js
it("testTransitionStateAssert", async () => {
    await expect(seBill).to.have.state("wfam_bill_e1");
    await expect(seBill).canChangeState("t_wfam_bill_e1_e2");
    await expect(seBill).to.have.state("wfam_bill_e1");
});
```


* `canSave(values: ISmartElementValues)` permet de mettre à jour les valeurs des smarts fields , un revert est automatiquement exécuté pour revenir aux valeurs d'origine. 1 argument : un objet avec pour clé le nom du SF et la valeur expectée.
Exemple : 
```js
it("testAssertCanSave", async () => {
    await expect(seBill).to.have.value("bill_title", "Test State Smart Element Is Updated");
    await expect(seBill).canSave({ bill_title: "expectedValue"});
    await expect(seBill).to.have.value("bill_title", "Test State Smart Element Is Updated");
});
```