/* eslint-disable no-case-declarations */
import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
import SmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement";
import Vue from "vue";
import { Component } from "vue-property-decorator";


// declare var $;



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
          "<p ref='content_confirm' class='content-confirm' >Warning : you are about to restore this element</p> <div class='button_wrapper'> <button class='k-cancel' ref='cancel'>Cancel</button><button class='k-delete' ref='deleteElement'>Restore Element</button> </div>";

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



                  // this.$http
                  // .put("/api/v2/admin/trash/" + docid)
                  // .then(response => {
                  //   that.$refs.grid.reload();
                  // });

                  $.ajax({
                    async: false,
                    type: "PUT",
                    url: "/api/v2/admin/trash/" + docid,
                    success: function (response) {
                      that.$refs.grid.reload();
                      that.selectedTrash = "";
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
      /*** 
        When the user click on the "delete" button 
      ***/
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
                "<p ref='content_confirm' class='content-confirm' >Warning : you are about to definitively delete this Smart Element which is not referenced in any other Smart Element</p> <div class='button_wrapper'> <button class='k-cancel' ref='cancel'>Cancel</button><button class='k-delete' ref='deleteElement'>Delete Element</button> </div>";
            } else {
              this.content =
                "<p ref='content_confirm' class='content-confirm' >Warning : you are about to definitively delete this Smart Element which is referenced in <b>" +
                thisPointer.NbReference +
                "</b> other Smart Elements</p> <div class='button_wrapper'> <button class='k-cancel' ref='cancel'>Cancel</button><button class='k-delete' ref='deleteElement'>Delete Element</button> </div>";
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
                        error: function (res) {
                          console.log(res);
                          that.$emit("notify", "Error : " + res.responseText);
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
                title: "Confirmation of " + e.data.row.title.value + "'s deletion",
              })
              .data("kendoWindow")
              .center()
              .open();
          },
          error: function (res) {
            thisPointer.$emit("notify", "Error :" + + res.responseText);
          }
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
        $(item).parent().css({
          "cursor": "not-allowed",
        })
        $(item).addClass("k-state-disabled");
      }
    });
  }
}
