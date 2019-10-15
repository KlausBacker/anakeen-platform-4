/* eslint-disable no-case-declarations */
import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
import SmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement";
import Vue from "vue";
import { Component } from "vue-property-decorator";
import { timingSafeEqual } from 'crypto';

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
  public NbReference: any = 0;

  public onClose(e) {
    e.sender.element
      .closest("[data-role=window]")
      .data("kendoWindow")
      .destroy();
  }
  // $(this.$refs.confirm).data("kendoWindow").close();


  public selectTrash(e) {
    switch (e.data.type) {
      case "display":
        if (!$(".k-grid-display", e.target).hasClass("k-state-disabled")) {
          this.selectedTrash = e.data.row.name || e.data.row.id.toString();
          // @ts-ignore
          this.$nextTick(() => {
            // @ts-ignore
            this.$refs.trashSmartElement.fetchSmartElement({
              initid: this.selectedTrash
            });
          });
        }
        else {
          this.$emit("notify", "You don't have access to see this element", "permission denied");
        }
        break;
      case "restore":
        this.selectedTrash = e.data.row.name || e.data.row.id.toString();
        // @ts-ignore
        const that = this;
        // const title = "Confirm the restoration of : " + e.data.row.title.value;
        const contentRestore =
          "<p ref='content_confirm' class='content-confirm' >Warning to restore the element</p> <div class='button_wrapper'> <button class='k-cancel' ref='cancel'>Cancel</button><button class='k-delete' ref='deleteElement'>Restore Element</button> </div>";

        $(this.$refs.confirm)
          .kendoWindow({
            actions: ["close"],
            activate: () => {
              const that = this;
              $(".k-cancel").kendoButton({
                click(e) {
                  $(that.$refs.confirm)
                    .data("kendoWindow")
                    .destroy();
                }
              });
              $(".k-delete").kendoButton({
                click(e) {

                  const docid = that.selectedTrash;

                  $.ajax({
                    async: false,
                    type: "PUT",
                    url: "/api/v2/admin/trash/" + docid,
                    success: function (response) {
                      that.$refs.grid.reload();
                    },
                    error: function (res, status, error) {
                      that.$emit("notify", "Restore error in request");
                    }
                  });

                  $(that.$refs.confirm)
                    .data("kendoWindow")
                    .destroy();
                }
              });
            },
            appendTo: this.$el,
            close: this.onClose,
            content: { template: contentRestore },
            iframe: false,
            title: "Confirm the restoration of : " + e.data.row.title.value,
          })
          .data("kendoWindow")
          .center()
          .open();

        break;
      case "delete":
        this.selectedTrash = e.data.row.name || e.data.row.id.toString();
        const thisPointer = this;
         $.ajax({
          async: false,
          type: "GET",
          url: "/api/v2/admin/trash/" + this.selectedTrash,
          success: function (response) {
            thisPointer.NbReference = response.data;
            if (thisPointer.NbReference === 0) {
              this.content =
                "<p ref='content_confirm' class='content-confirm' >Warning to the definitive deletion, the document is not referenced in any other element</p> <div class='button_wrapper'> <button class='k-cancel' ref='cancel'>Cancel</button><button class='k-delete' ref='deleteElement'>Delete Element</button> </div>";
            }
            else {
              this.content =
                "<p ref='content_confirm' class='content-confirm' >Warning to the definitive deletion, the document is referenced in <b>" +
                thisPointer.NbReference +
                "</b> other documents</p> <div class='button_wrapper'> <button class='k-cancel' ref='cancel'>Cancel</button><button class='k-delete' ref='deleteElement'>Delete Element</button> </div>";
              }
              $(thisPointer.$refs.confirm)
              .kendoWindow({
                actions: ["close"],
                activate: () => {
                  const that = thisPointer;
                  $(".k-cancel").kendoButton({
                    click(e) {
                      $(that.$refs.confirm)
                        .data("kendoWindow")
                        .destroy();
                    }
                  });
                  $(".k-delete").kendoButton({
                    click(e) {
                      const docid = that.selectedTrash;
    
                      $.ajax({
                        async: false,
                        type: "DELETE",
                        url: "/api/v2/admin/trash/" + docid,
                        success: function (response) {
                          that.$refs.grid.reload();
                        },
                        error: function (res, status, error) {
                          that.$emit("notify", "Error in the delete request");
                        }
                      });
    
                      $(that.$refs.confirm)
                        .data("kendoWindow")
                        .destroy();
                    }
                  });
                },
                appendTo: thisPointer.$el,
                close: thisPointer.onClose,
                content: { template: this.content },
                iframe: false,
                title: "Confirm the deletion of : " + e.data.row.title.value,
              })
              .data("kendoWindow")
              .center()
              .open();    
          },
          error : function(res, status, error){
            thisPointer.$emit("notify", "Error for get the number of reference in other element");
          }
        });
        break;
    }
  }

  protected onGridDataBound() {
    $(".k-grid-display", $(this.$el)).each((index, item) => {
      const rowData = this.$refs.grid.kendoGrid.dataItem($(item).closest("tr")).rowData;
      if (!rowData.auth.value) {
        $(item).parent().css({
          "cursor": "not-allowed",
        })
        // $(item).addClass("button-disable");
        $(item).addClass("k-state-disabled");
      }
    });
  }
}
