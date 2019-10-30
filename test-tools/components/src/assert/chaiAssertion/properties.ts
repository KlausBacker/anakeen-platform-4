import SmartElement from "../../context/utils/SmartElement";
import { ISmartElementValues } from "../../context/AbstractContext";
import { searchParams, SFValues } from "../../utils/routes";

/*
Set const enableXdebug = TRUE for activate PHP debugger
 */
const enableXDebug = true;

export default function chaiPropertyPlugin(chai: Chai.ChaiStatic) {

  const getCommonOptions = function getDefaultOptions(assertion: Chai.AssertionStatic) {
    const options: any = {};
    const login = chai.util.flag(assertion, 'login');
    if (login) {
      options.login = login;
    }
    if(enableXDebug) {
      options.searchParams = {
        XDEBUG_SESSION_START : 'true'
      }
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

    const options = getCommonOptions(this);
    const baseUrl = `/api/v2/smart-elements/${smartElement}.json`;
    const searchParameters = searchParams(options);
    const url = `${baseUrl}?${searchParameters}`

    if (typeof smartElement === "string") {
      const response = await this._obj.fetchApi(url);
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
    
    const baseUrl = `/api/v2/smart-elements/${smartElementLogicalName}.json`;
    const searchParameters = searchParams(options);
    const url = `${baseUrl}?${searchParameters}`

    const response = await this._obj.fetchApi(url);
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
    options.searchParams = {
      ...options.searchParams || {},
      useTrash: "true"
    }
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

  chai.Assertion.addMethod("value", async function (this: Chai.AssertionStatic, smartField: string, expectedValue: any) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const value = await target.getValue(smartField, options);
    // console.log(value);

    const expectedMessage = "expected value is #{exp} but was #{act}";
    const notExpectedMessage = "expected value is not #{exp} but was #{act}";

    this.assert(
      value.value === expectedValue,
      expectedMessage,
      notExpectedMessage,
      expectedValue,
      value.value
    );
  })

  chai.Assertion.addMethod("values", async function (this: Chai.AssertionStatic, smartFields: {smartField: string, expectedValue: any}) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const value = await target.getValue(smartFields.smartField, options);
    console.log(value);

    const expectedMessage = "expected value is #{exp} but was #{act}";
    const notExpectedMessage = "expected value is not #{exp} but was #{act}";

    this.assert(
      value === smartFields.expectedValue,
      expectedMessage,
      notExpectedMessage,
      smartFields.expectedValue,
      value
    );
  })


  chai.Assertion.addMethod("canSave", async function (this: Chai.AssertionStatic, values: ISmartElementValues) {
    const target: SmartElement = this._obj;
    // const options = getCommonOptions(this);
    let success = false;
    let error = "";
    try {
      const updatedSe = await target.updateValues(values, { dryRun: true });
      // console.log(updatedSe);
      success = true;
    } catch (e) {
      error = e.message;
    }

    const expectedMessage = "smart element has been updated with success";
    const notExpectedMessage = "smart element has not been updated with success: #{act}";

    this.assert(
      success,
      expectedMessage,
      notExpectedMessage,
      true,
      error
    );
  })

  chai.Assertion.addMethod("canChangeState", async function (this: Chai.AssertionStatic, transition: string, askValues?: object) {
    const target: SmartElement = this._obj;
    // const options = getCommonOptions(this);
    let success = false;
    let error = "";
    try {
      await target.changeState({transition, askValues}, { dryRun: true });
      success = true;
    } catch (e) {
      error = e.message;
    }

    const expectedMessage = "Transition was expected to be allowed, but failed with error #{act}";
    const notExpectedMessage = "Transition was not expected to be allowed, but succeeded";

    this.assert(
      success,
      expectedMessage,
      notExpectedMessage,
      true,
      error
    );
  })

  chai.Assertion.addMethod("viewControl", async function (this: Chai.AssertionStatic, smartElementLogicalName: string | SmartElement, dryRun: boolean = false) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const cvId = await target.getPropertyValue("viewController", options);
    
    const baseUrl = `/api/v2/smart-elements/${smartElementLogicalName}.json`;
    const searchParameters = searchParams(options);
    const url = `${baseUrl}?${searchParameters}`

    const response = await this._obj.fetchApi(url);
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

  chai.Assertion.addMethod("viewAccess", async function (this: Chai.AssertionStatic, viewId: string) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const seId = await target.getPropertyValue("initid", options);
    const baseUrl = `/api/v2/test-tools/smart-elements/${seId}/views/${viewId}`;
    const searchParameters = searchParams(options);
    const url = `${baseUrl}?${searchParameters}`

    let result = false;
    const response = await this._obj.fetchApi(url, {
      headers: {
        Accept: "application/json"
      }
    });
    const responseData = await response.json();
    result = responseData.success;

    const expectedMessage = `expected user to have access to view #{exp} but got: ${responseData.message || responseData.exceptionMessage}`;
    const notExpectedMessage = "expected user to have access to view #{exp} but he has access";

    this.assert(
      result === true,
      expectedMessage,
      notExpectedMessage,
      viewId
    );
  });

  chai.Assertion.addMethod("fieldAccess", async function (this: Chai.AssertionStatic, smartElementLogicalName: string | SmartElement, dryRun: boolean = false) {
    const target: SmartElement = this._obj;
    const security = await target.getPropertyValue("security");
    const fallId = security.fieldAccess ? security.fieldAccess.id : -1;
    
    const options = getCommonOptions(this);
    const baseUrl = `/api/v2/smart-elements/${smartElementLogicalName}.json`;
    const searchParameters = searchParams(options);
    const url = `${baseUrl}?${searchParameters}`

    const response = await this._obj.fetchApi(url);
    const responseData = await response.json();
    // console.log(fallId);
    // console.log(responseData.data.document.properties);

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

  chai.Assertion.addMethod("smartElementRight", async function (this: Chai.AssertionStatic, acl: string) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const docid = await target.getPropertyValue("initid", options);
    
    const baseUrl = `/api/v2/test-tools/smart-elements/${docid}/rights/${acl}`;
    const searchParameters = searchParams(options);
    const url = `${baseUrl}?${searchParameters}`

    let result = false;
    const response = await this._obj.fetchApi(url, {
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

  chai.Assertion.addMethod("smartFieldRight", async function (this: Chai.AssertionStatic, acl: string, smartField: string) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const docid = await target.getPropertyValue("initid", options);
    // const sF = await target.getValue("us_meid");
    // console.log(sF);
    
    const baseUrl = `/api/v2/test-tools/smart-elements/${docid}/rights/${acl}`;
    const searchParameters = searchParams(options);
    const url = `${baseUrl}?${searchParameters}`

    let result = false;
    const response = await this._obj.fetchApi(url, {
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

  chai.Assertion.addMethod("transitionRight", async function (this: Chai.AssertionStatic, transition: string) {
    const target: SmartElement = this._obj;
    const options = getCommonOptions(this);
    const docid = await target.getPropertyValue("initid", options);
    
    const baseUrl = `/api/v2/test-tools/smart-elements/${docid}/workflows/transitions/right/${transition}`;
    const searchParameters = searchParams(options);
    const url = `${baseUrl}?${searchParameters}`

    let result = false;
    const response = await this._obj.fetchApi(url, {
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
      transition
    );
  });

  chai.Assertion.addChainableMethod("for", function (this: Chai.AssertionStatic, login: Account) {
    chai.util.flag(this, 'login', login);
  });

}