import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";

import { Component, Prop, Vue, Watch } from "vue-property-decorator";

Vue.use(ButtonsInstaller);

// noinspection JSUnusedGlobalSymbols
@Component({
  name: "TeTaskInfo"
})
export default class TeTaskInfo extends Vue {
  public data: any = { info: {}, histo: [] };
  public deleting: boolean = false;
  @Prop() public taskData: any;

  public get statusClass() {
    return "cell-status-value cell-status--" + this.data.info.status;
  }

  @Watch("taskData")
  public onTaskChange() {
    this.updateTaskInfo();
  }

  public deleteTask() {
    this.deleting = true;
    this.$http.delete("/api/admin/transformationengine/tasks/" + this.taskData.tid).then(() => {
      this.$emit("task-deleted", this.taskData.tid);
    });
  }

  public mounted() {
    this.updateTaskInfo();
  }

  protected updateTaskInfo() {
    this.deleting = false;
    this.$http.get("/api/admin/transformationengine/tasks/" + this.taskData.tid).then(response => {
      this.data = response.data.data;
      this.data.info.cdate = this.taskData.cdate;
    });
  }
}
