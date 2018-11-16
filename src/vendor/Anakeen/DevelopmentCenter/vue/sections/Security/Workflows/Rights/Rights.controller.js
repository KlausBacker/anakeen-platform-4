import WorkflowRights from "devComponents/WorkflowRights/WorkflowRights.vue";

export default {
  components: {
    "workflow-rights": WorkflowRights
  },
  props: ["workflowId"],
  data() {
    return {
      msg: "Hello vue !"
    };
  }
};
