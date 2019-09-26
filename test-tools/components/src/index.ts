const packageInfo = require("../../package.json");

import Accounts from "./Utils/Accounts";
import SmartElements from "./Utils/SmartElements";

export { Accounts, SmartElements };

export default {
  Accounts,
  SmartElements,
  version: packageInfo.version
};
