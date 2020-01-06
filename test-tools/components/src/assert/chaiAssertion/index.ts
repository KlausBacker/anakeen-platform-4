// eslint-disable-next-line no-unused-vars
import SmartElement from "../../context/utils/SmartElement";
import PropertyPlugin from "./properties";
import RightsPlugin from "./rights";

declare global {
  export namespace Chai {
    // tslint:disable-next-line:interface-name
    interface Assertion {
      haveProfile(smartElement: string | SmartElement): Promise<void>;
      haveViewControl(smartElement: string | SmartElement): Promise<void>;
      haveWorkflow(smartElement: string | SmartElement): Promise<void>;
      haveWorkflow(smartElement: string | SmartElement): Promise<void>;
      haveFieldAccess(smartElement: string | SmartElement): Promise<void>;
    }
  }
}

export default function chaiPlugin(chai: Chai.ChaiStatic) {
  chai.use(PropertyPlugin);
  chai.use(RightsPlugin);
};