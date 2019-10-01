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

  chai.Assertion.addMethod('haveWorkflow', async function (this: any, smartElementLogicalName: string) {
    let target: SmartElement = this._obj;
    const wId = target.getPropertyValue("wid");

    const response = await fetch(`/api/v2/smart-elements/${smartElementLogicalName}.json`);
    const responseData = await response.json();

    let expectedMessage = 'expected profile to equal #{exp} but was #{act}';
    let notExpectedMessage = 'expected profile not to equal #{exp} but was #{act}'

    this.assert(responseData.data.document.properties.id === wId, expectedMessage, notExpectedMessage, responseData.data.document.properties.id, wId);
  });
}
