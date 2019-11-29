import { Vue } from "vue-property-decorator";
import Axios from "axios";

import WorkflowRights from "devComponents/WorkflowRights/WorkflowRights.vue";

const axios = Axios.create();
Vue.prototype.$http = axios;
Vue.prototype.$route = { name: window.workflowId };
Vue.prototype.$ = kendo.jQuery;
new Vue({
  el: "#workflow-content",
  components: {
    "ank-workflow-rights": WorkflowRights
  },
  template: `<ank-workflow-rights style="height: 100%" :wid="workflowId"></ank-workflow-rights>`,
  data() {
    return {
      workflowId: window.workflowId
    };
  }
});
