const packageInfo = require("../../package.json");

export { default as Context } from "./context/Context";
export { default as Account } from "./context/utils/Account";
export { default as SmartElement } from "./context/utils/SmartElement";
export { default as AnakeenAssertion } from "./assert/chaiAssertion";
export const version: string = packageInfo.version;
