import devCenter from "./modules/app";

import * as actions from "./actions";
import * as getters from "./getters";
export default {
  ...devCenter,
  actions,
  getters
};
