import SmartElement from "../../context/utils/SmartElement";
import fetch from "node-fetch";

export default function chaiProperties(chai: any) {
    chai.Assertion.addMethod('haveProfile', async function (this: any, smartElementLogicalName: string) {
        let target: SmartElement = this._obj;
        const profileId = target.getPropertyValue("security").profile.id;

        const response = await fetch(`/api/v2/smart-elements/${smartElementLogicalName}.json`);
        const responseData = await response.json();

        let expectedMessage = 'expected profile to equal #{exp} but was #{act}';
        let notExpectedMessage = 'expected profile not to equal #{exp} but was #{act}'

        this.assert(responseData.data.document.properties.id === profileId, expectedMessage, notExpectedMessage, responseData.data.document.properties.id, profileId);
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