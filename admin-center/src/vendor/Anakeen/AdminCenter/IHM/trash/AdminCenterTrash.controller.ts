/* eslint-disable no-case-declarations */
import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import SmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import { Component, Vue } from "vue-property-decorator";

@Component({
  components: {
    "ank-se-grid": AnkSEGrid,
    AnkSmartElement: () => SmartElement,
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class AdminCenterTrashController extends Vue {
  public $refs!: {
    [key: string]: any;
  };
  public selectedTrash: string = "";
  public NbReference: any = 0;
  public content: string;
  public selectedTrashBool: boolean = false;

  /*** 
    This function close the confirmation pop-up (for restore or delete one element) 
  ***/
  public onClose(e) {
    e.sender.element
      .closest("[data-role=window]")
      .data("kendoWindow")
      .destroy();
  }
  public selectTrash(e) {
    switch (e.data.type) {
      /***
        When the user click on the "display" button
      ***/
      case "display":
        this.selectedTrashBool = true;
        if (!$(".k-grid-display", e.target).hasClass("k-state-disabled")) {
          this.selectedTrash = e.data.row.name || e.data.row.id.toString();
          this.$nextTick(() => {
          this.$refs.trashSmartElement.fetchSmartElement({
                initid: this.selectedTrash
              });
          });
        }
        break;

      /***
        When the user click on the "restore" button
      ***/
      case "restore":
        this.selectedTrash = e.data.row.name || e.data.row.id.toString();
        const contentRestore =
          "<p ref='content_confirm' class='content-confirm' >Warning : you are about to restore this element</p> <div class='button_wrapper'> <button class='k-cancel' ref='cancel'>Cancel</button><button class='k-restore' ref='deleteElement'>Restore Element</button> </div>";

        $(this.$refs.confirm)
          .kendoWindow({
            actions: ["close"],
            activate: () => {
              $(".k-cancel").kendoButton({
                click: e => {
                  $(this.$refs.confirm)
                    .data("kendoWindow")
                    .destroy();
                }
              });
              $(".k-restore").kendoButton({
                click: e => {
                  const docid = this.selectedTrash;

                  this.$http.put("/api/v2/admin/trash/" + docid).then(response => {
                    this.$refs.grid.reload();
                    if (this.selectedTrashBool && this.$refs.trashSmartElement.getProperty("id") === parseInt(docid)) {
                      this.$refs.trashSmartElement.fetchSmartElement({
                        initid: this.selectedTrash,
                        viewId: "!defaultConsultation"
                      });
                    }
                  });

                  $(this.$refs.confirm)
                    .data("kendoWindow")
                    .destroy();
                }
              });
            },
            appendTo: this.$el,
            close: this.onClose,
            content: { template: contentRestore },
            iframe: false,
            title: "Confirm restoration of : " + e.data.row.title.value
          })
          .data("kendoWindow")
          .center()
          .open();

        break;

      /***
        When the user click on the "delete" button
      ***/
      case "delete":
        this.selectedTrash = e.data.row.name || e.data.row.id.toString();
        const thisPointer = this;

        this.$http.get("/api/v2/admin/trash/" + this.selectedTrash).then(response => {
          thisPointer.NbReference = response.data.data;
          if (Number(thisPointer.NbReference) === 0) {
            this.content =
              "<p ref='content_confirm' class='content-confirm' >Warning : you are about to definitively delete this Smart Element which is not referenced in any other Smart Element</p> <div class='button_wrapper'> <button class='k-cancel' ref='cancel'>Cancel</button><button class='k-delete' ref='deleteElement'>Delete from trash</button> </div>";
          } else {
            this.content =
              "<p ref='content_confirm' class='content-confirm' >Warning : you are about to definitively delete this Smart Element which is referenced in <b>" +
              thisPointer.NbReference +
              "</b> other Smart Elements</p> <div class='button_wrapper'> <button class='k-cancel' ref='cancel'>Cancel</button><button class='k-delete' ref='deleteElement'>Delete from trash</button> </div>";
          }
          $(thisPointer.$refs.confirm)
            .kendoWindow({
              actions: ["close"],
              activate: () => {
                $(".k-cancel").kendoButton({
                  click: e => {
                    $(this.$refs.confirm)
                      .data("kendoWindow")
                      .destroy();
                  }
                });
                $(".k-delete").kendoButton({
                  click: e => {
                    const docid = this.selectedTrash;
                    this.$http.delete("/api/v2/admin/trash/" + docid).then(response => {
                      this.$refs.grid.reload();
                    });

                    $(this.$refs.confirm)
                      .data("kendoWindow")
                      .destroy();
                  }
                });
              },
              appendTo: thisPointer.$el,
              close: thisPointer.onClose,
              content: { template: this.content },
              iframe: false,
              title: `"${e.data.row.title.value}" : Confirmation deletion`
            })
            .data("kendoWindow")
            .center()
            .open();
        });
        break;
    }
  }

  /*** 
    This function put the "display" button in disable 
  ***/
  protected onGridDataBound() {
    $(".k-grid-display", $(this.$el)).each((index, item) => {
      const rowData = this.$refs.grid.kendoGrid.dataItem($(item).closest("tr")).rowData;
      if (!rowData.auth.value) {
        $(item)
          .parent()
          .css({
            cursor: "not-allowed"
          });
        $(item).addClass("k-state-disabled");
      }
    });
  }
}
