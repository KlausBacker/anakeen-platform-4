import axiosAdmin from "./admin";
import axiosApiV2 from "./api";
import axiosGlobal from "./global";

const identity = r => r;

export default function install(
  Vue,
  options = {
    // jscs:ignore disallowFunctionDeclarations
    onSuccessResponse: identity,
    onErrorResponse: identity,
    onSuccessRequest: identity,
    onErrorRequest: identity
  }
) {
  [axiosAdmin, axiosApiV2, axiosGlobal].forEach(axios => {
    axios.interceptors.request.use(
      options.onSuccessRequest,
      options.onErrorRequest
    );
    axios.interceptors.response.use(
      options.onSuccessResponse,
      options.onErrorResponse
    );
  });

  Vue.ankApi = Vue.prototype.$ankApi = axiosApiV2;
  Vue.ankAdmin = Vue.prototype.$ankAdmin = axiosAdmin;
  Vue.axios = Vue.prototype.$axios = axiosGlobal;
}
