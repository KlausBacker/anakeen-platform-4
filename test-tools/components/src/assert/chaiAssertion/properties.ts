import SmartElement from "../../context/utils/SmartElement";

export default function chaiPropertyPlugin(chai: Chai.ChaiStatic) {
  chai.Assertion.addMethod("haveProfile", async function(
    this: Chai.AssertionStatic,
    smartElement: string | SmartElement
  ) {
    const elementToTest: SmartElement = this._obj;
    const profid = parseInt(elementToTest.getPropertyValue("profid"), 10);
    const dprofid = parseInt(elementToTest.getPropertyValue("dprofid"), 10);
    const expectedMessage = "expected profile to equal #{exp} but was #{act}";
    const notExpectedMessage = "expected profile not to equal #{exp} but was #{act}";
    let expectedProfile;

    if (typeof smartElement === "string") {
      expectedProfile = 963;
    } else if (smartElement instanceof SmartElement) {
      expectedProfile = smartElement.getPropertyValue("initid");
    }
    if (expectedProfile === dprofid) {
      this.assert(expectedProfile === dprofid, expectedMessage, notExpectedMessage, expectedProfile, dprofid);
    } else {
      this.assert(expectedProfile === profid, expectedMessage, notExpectedMessage, expectedProfile, profid);
    }
  });

  chai.Assertion.addMethod("haveWorkflow", async function(this: Chai.AssertionStatic, smartElementLogicalName: string) {
    const target: SmartElement = this._obj;
    const wId = target.getPropertyValue("wid");

    const response = await fetch(`/api/v2/smart-elements/${smartElementLogicalName}.json`);
    const responseData = await response.json();

    const expectedMessage = "expected profile to equal #{exp} but was #{act}";
    const notExpectedMessage = "expected profile not to equal #{exp} but was #{act}";

    this.assert(
      responseData.data.document.properties.id === wId,
      expectedMessage,
      notExpectedMessage,
      responseData.data.document.properties.id,
      wId
    );
  });

  chai.Assertion.addMethod("alive", async function(this: Chai.AssertionStatic, dryRun: boolean = false) {
    const target: SmartElement = this._obj;
    const doctype = target.getPropertyValue("doctype");

    const expectedMessage = "expected smart element is alive but was in doctype #{act}";
    const notExpectedMessage = "expected smart element is not alive but was in doctype #{act}";

    this.assert(
      doctype !== "Z",
      expectedMessage,
      notExpectedMessage,
      true,
      doctype
    );
  });

  chai.Assertion.addMethod("locked", async function(this: Chai.AssertionStatic, dryRun: boolean = false) {
    const target: SmartElement = this._obj;
    const locked = target.getPropertyValue("locked");
    // console.log(target);
    const expectedMessage = "expected smart element is locked but was in locked #{act}";
    const notExpectedMessage = "expected smart element is not locked but was in locked #{act}";

    this.assert(
      locked !== 0,
      expectedMessage,
      notExpectedMessage,
      true,
      locked
    );
  });

  chai.Assertion.addMethod("state", async function(this: Chai.AssertionStatic, stateReference: string, dryRun: boolean = false) { 
    const target: SmartElement = this._obj;
    const state = target.getPropertyValue("state");

    const expectedMessage = "expected state is #{exp} but was #{act}";
    const notExpectedMessage = "expected state is not #{exp} but was #{act}";

    this.assert(
      state.reference === stateReference,
      expectedMessage,
      notExpectedMessage,
      stateReference,
      state.reference
    );
  });

  // chai.Assertion.addMethod("fieldAccess", async function(this: Chai.AssertionStatic, smartElementLogicalName: string | SmartElement, dryRun: boolean = false) {
  //   const target: SmartElement = this._obj;
  //   const smartFields = target.getPropertyValue("smartFields");

  //   const expectedMessage = "expected smart element is #{act} but was in smartFields #{act}";
  //   const notExpectedMessage = "expected smart element is not #{act} but was in smartFields #{act}";

  //   this.assert(
  //     smartFields === null,
  //     expectedMessage,
  //     notExpectedMessage,
  //     true,
  //     smartFields
  //   );
  // });
}
