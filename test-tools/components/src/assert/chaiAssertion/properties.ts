import SmartElement from "../../context/utils/SmartElement";
import { ISmartElementValues } from "../../context/AbstractContext";

export default function chaiPropertyPlugin(chai: Chai.ChaiStatic) {

  const getCommonOptions = function getDefaultOptions(assertion: Chai.AssertionStatic) {
    const options: any = {};
    const login = chai.util.flag(assertion, 'login');
    if (login) {
      options.login = login;
    }
    return options;
  }

  chai.Assertion.addMethod("profile", async function (this: Chai.AssertionStatic, smartElement: string | SmartElement) {
    const target: SmartElement = this._obj;
    const profid = parseInt(await target.getPropertyValue("profid"), 10);
    const dprofid = parseInt(await target.getPropertyValue("dprofid"), 10);
    const expectedMessage = "expected profile to equal #{exp} but was #{act}";
    const notExpectedMessage = "expected profile not to equal #{exp} but was #{act}";
    let expectedProfile: number = 0;

    if (typeof smartElement === "string") {
      const response = await this._obj.fetchApi(`/api/v2/smart-elements/${smartElement}.json`);
      const responseData = await response.json();
      expectedProfile = responseData.document.properties.initid;
    } else if (smartElement instanceof SmartElement) {
      expectedProfile = await smartElement.getPropertyValue("initid");
    }
    if (expectedProfile === dprofid) {
      this.assert(expectedProfile === dprofid, expectedMessage, notExpectedMessage, expectedProfile, dprofid);
    } else {
      this.assert(expectedProfile === profid, expectedMessage, notExpectedMessage, expectedProfile, profid);
    }
  });

  chai.Assertion.addMethod("workflow", async function (this: Chai.AssertionStatic, smartElementLogicalName: string) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const wId = await target.getPropertyValue("workflow", options);

    const response = await this._obj.fetchApi(`/api/v2/smart-elements/${smartElementLogicalName}.json`);
    const responseData = await response.json();

    const expectedMessage = "expected profile to equal #{exp} but was #{act}";
    const notExpectedMessage = "expected profile not to equal #{exp} but was #{act}";

    this.assert(
      responseData.data.document.properties.initid === wId.id,
      expectedMessage,
      notExpectedMessage,
      responseData.data.document.properties.initid,
      wId.id
    );
  });

  chai.Assertion.addMethod("alive", async function (this: Chai.AssertionStatic, dryRun: boolean = false) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const doctype = await target.getPropertyValue("doctype", options);

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

  chai.Assertion.addMethod("locked", async function (this: Chai.AssertionStatic, dryRun: boolean = false) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const locked = await target.getPropertyValue("locked", options);

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

  // chai.Assertion.addMethod("canChangeState", async function (this: Chai.AssertionStatic, stateReference: string, dryRun: boolean = false) {
  //   const target: SmartElement = this._obj;
  //   const options = getCommonOptions(this);
  //   const state = await target.getPropertyValue("state", options);
  //   console.log(state);

  //   const expectedMessage = "expected state is #{exp} but was #{act}";
  //   const notExpectedMessage = "expected state is not #{exp} but was #{act}";

  //   this.assert(
  //     state.reference === stateReference,
  //     expectedMessage,
  //     notExpectedMessage,
  //     stateReference,
  //     state.reference
  //   );
  // })

  chai.Assertion.addMethod("state", async function (this: Chai.AssertionStatic, stateReference: string, dryRun: boolean = false) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const state = await target.getPropertyValue("state", options);

    const expectedMessage = "expected state is #{exp} but was #{act}";
    const notExpectedMessage = "expected state is not #{exp} but was #{act}";

    this.assert(
      state.reference === stateReference,
      expectedMessage,
      notExpectedMessage,
      stateReference,
      state.reference
    );
  })

  chai.Assertion.addMethod("values", async function (this: Chai.AssertionStatic, smartField: string, smartFieldValue: string) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const value = await target.getValue(smartField, options);
    // console.log(value);

    const expectedMessage = "expected value is #{exp} but was #{act}";
    const notExpectedMessage = "expected value is not #{exp} but was #{act}";

    this.assert(
      value.value === smartFieldValue,
      expectedMessage,
      notExpectedMessage,
      smartFieldValue,
      value.value
    );
  })


  chai.Assertion.addMethod("canSave", async function (this: Chai.AssertionStatic, values: ISmartElementValues) {
    const target: SmartElement = this._obj;
    const updateValue = await target.updateValues(values);
    const seId = await updateValue.getPropertyValue("initid");
    // console.log(seId);

    let result = false;
    const response = await this._obj.fetchApi(`/api/v2/test-tools/smart-elements/${seId}.json`, {
      headers: {
        Accept: "application/json"
      }
    });
    const responseData = await response.json();
    result = responseData.success;

    const expectedMessage = "expected state is #{exp} but was #{act}";
    const notExpectedMessage = "expected state is not #{exp} but was #{act}";

    this.assert(
      result === true,
      expectedMessage,
      notExpectedMessage,
      seId
    );
  })

  chai.Assertion.addMethod("fieldAccess", async function (this: Chai.AssertionStatic, smartElementLogicalName: string | SmartElement, dryRun: boolean = false) {
    const target: SmartElement = this._obj;
    const security = await target.getPropertyValue("security");
    const fallId = security.fieldAccess ? security.fieldAccess.id : -1;

    const response = await this._obj.fetchApi(`/api/v2/smart-elements/${smartElementLogicalName}.json`);
    const responseData = await response.json();

    const expectedMessage = "expected field access to equal #{exp} but was #{act}";
    const notExpectedMessage = "expected field access not to equal #{exp} but was #{act}";

    this.assert(
      responseData.data.document.properties.initid === fallId,
      expectedMessage,
      notExpectedMessage,
      responseData.data.document.properties.initid,
      fallId
    );
  });

  chai.Assertion.addMethod("viewControl", async function (this: Chai.AssertionStatic, smartElementLogicalName: string | SmartElement, dryRun: boolean = false) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const cvId = await target.getPropertyValue("viewController", options);

    const response = await this._obj.fetchApi(`/api/v2/smart-elements/${smartElementLogicalName}.json`);
    const responseData = await response.json();

    const expectedMessage = "expected viewControllrer to equal #{exp} but was #{act}";
    const notExpectedMessage = "expected viewController not to equal #{exp} but was #{act}";

    this.assert(
      responseData.data.document.properties.initid === cvId.id,
      expectedMessage,
      notExpectedMessage,
      responseData.data.document.properties.initid,
      cvId.id
    );
  });

  chai.Assertion.addMethod("smartElementRight", async function (this: Chai.AssertionStatic, acl: string) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const seId = await target.getPropertyValue("initid", options);
    
    let result = false;
    const response = await this._obj.fetchApi(`/api/v2/test-tools/smart-elements/${seId}/rights/${acl}`, {
      headers: {
        Accept: "application/json"
      }
    });
    const responseData = await response.json();
    result = responseData.success;

    const expectedMessage = `expected access to #{exp} but was not because: ${responseData.message || responseData.exceptionMessage}`;
    const notExpectedMessage = "expected not access to #{exp} but was";

    this.assert(
      result === true,
      expectedMessage,
      notExpectedMessage,
      acl
    );
  });

  // chai.Assertion.addMethod("transitionRight", async function (this: Chai.AssertionStatic, transition: string) {
  //   const target: SmartElement = this._obj;
  //   const options = getCommonOptions(this);
  //   const seId = await target.getPropertyValue("initid", options);
  //   let result = false;
  //   const response = await this._obj.fetchApi(`/api/v2/test-tools/smart-elements/${seId}/rights/${transition}`, {
  //     headers: {
  //       Accept: "application/json"
  //     }
  //   });
  //   const responseData = await response.json();
  //   result = responseData.success;

  //   const expectedMessage = `expected access to #{exp} but was not because: ${responseData.message || responseData.exceptionMessage}`;
  //   const notExpectedMessage = "expected not access to #{exp} but was";

  //   this.assert(
  //     result === true,
  //     expectedMessage,
  //     notExpectedMessage,
  //     transition
  //   );
  // });

  chai.Assertion.addMethod("viewAccess", async function (this: Chai.AssertionStatic, viewId: string) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const seId = await target.getPropertyValue("initid", options);
    let result = false;
    const response = await this._obj.fetchApi(`/api/v2/test-tools/smart-elements/${seId}/views/${viewId}`, {
      headers: {
        Accept: "application/json"
      }
    });
    const responseData = await response.json();
    // console.log(responseData);
    result = responseData.success;

    const expectedMessage = `expected access to view #{exp} but was not because: ${responseData.message || responseData.exceptionMessage}`;
    const notExpectedMessage = "expected not access to view #{exp} but was";

    this.assert(
      result === true,
      expectedMessage,
      notExpectedMessage,
      viewId
    );
  });

  chai.Assertion.addChainableMethod("for", function (this: Chai.AssertionStatic, login: Account) {
    chai.util.flag(this, 'login', login);
  });

}