import AnkAxios from "../Components/utils/axios";
import { notify } from "../Components/utils/xhrErrors";

export default Vue => {
  Vue.use(AnkAxios, {
    onErrorResponse: err => {
      notify(err);
      return err;
    }
  });
  Vue.jQuery = Vue.jquery = Vue.prototype.$ = kendo.jQuery;
  Vue.kendo = Vue.prototype.$kendo = kendo;
};
