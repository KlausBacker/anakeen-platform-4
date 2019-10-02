import PropertiesAssertion from "./properties";
import RightsAssertion from "./rights";

declare global {
  export namespace Chai {
    // tslint:disable-next-line:interface-name
    interface Assertion {
      haveProfile(smartElementName: string): Promise<void>,
      haveViewControl(smartElementName: string): Promise<void>,
      haveFieldAccess(smartElementName: string): Promise<void>,
      haveWorkflow(smartElementName: string): Promise<void>
    }
  }
}

export default function chaiPlugins(chai: any) {
  chai.use(PropertiesAssertion);
  chai.use(RightsAssertion);
};