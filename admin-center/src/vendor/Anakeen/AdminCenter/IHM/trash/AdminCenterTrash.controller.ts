import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
import SmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement";
import Vue from "vue";
import { Component } from "vue-property-decorator";

@Component({
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-smart-element": SmartElement,
    "ank-split-panes": AnkPaneSplitter
  }
})

export default class AdminCenterTrashController extends Vue {

  public $refs!: {
    [key: string]: any;
  };
  public selectedTrash: string = "";
  public selectTrash(e) {

    switch (e.data.type) {
      case "display":
        this.selectedTrash = e.data.row.name || e.data.row.id.toString();
        // @ts-ignore
        this.$nextTick(() => {
          // @ts-ignore
          this.$refs.trashSmartElement.fetchSmartElement({
            initid: this.selectedTrash
          });
        });
        break;
      case "restore":
        this.selectedTrash = e.data.row.name || e.data.row.id.toString();
        // @ts-ignore


        // mettre le restore

        const jsonRestore = {
          "document": {
            "properties": {
              "status": "alive"
            }
          }
        }
        this.$http
          .put(`/api/v2/trash/${encodeURIComponent(this.selectedTrash)}`, JSON.stringify(jsonRestore), {
            headers: {
              "Content-Type": "application/json"
            }
          })
          .then(response => {
            if (response.status === 200 && response.statusText === "OK") {
              this.$refs.grid.reload();
              console.log(this.selectedTrash);
            } else {
              throw new Error(response.data);
            }
          })
          .catch(error => {
            console.error("Unable to get options", error);
          });
        break;
      case "delete":
        console.log("delete");

        this.selectedTrash = e.data.row.name || e.data.row.id.toString();
        // @ts-ignore

        // mettre le delete

        break;
    }
  }
}
