import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import SmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import { Component, Mixins } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import * as $ from "jquery";

@Component({
  components: {
    "ank-se-grid": AnkSEGrid,
    AnkSmartElement: () => SmartElement,
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class AdminCenterTrashController extends Mixins(AnkI18NMixin) {
  public $refs!: {
    [key: string]: any;
  };
  public selectedTrash = "";
  public NbReference: any = 0;
  public content: string;
  public selectedTrashBool = false;

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
      case "consult":
        this.selectedTrashBool = true;
        if (!$(".k-grid-display", e.target).hasClass("k-state-disabled")) {
          this.selectedTrash = e.data.row.properties.id.toString();
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
        this.selectedTrash = e.data.row.properties.id.toString();
        const contentRestore = `<p ref='content_confirm' class='content-confirm' >${this.$t(
          "AdminCenterTrash.Warning restore"
        )}</p> <div class='button_wrapper'> <button class='k-cancel' ref='cancel'>${this.$t(
          "AdminCenterTrash.Cancel"
        )}</button><button class='k-restore' ref='deleteElement'>${this.$t(
          "AdminCenterTrash.Restore element"
        )}</button> </div>`;

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
                    this.$refs.grid._loadGridContent();
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
            title: this.$t("AdminCenterTrash.Confirm restoration") + e.data.row.properties.title
          })
          .data("kendoWindow")
          .center()
          .open();

        break;

      /***
        When the user click on the "delete" button
      ***/
      case "delete":
        this.selectedTrash = e.data.row.properties.id.toString();
        const thisPointer = this;

        this.$http.get("/api/v2/admin/trash/" + this.selectedTrash).then(response => {
          const data = response.data.data;
          thisPointer.NbReference = response.data.data.length;
          const deleteReference = `<p ref='content_confirm' class='content-confirm' ><span style='color:red;font-weight: bold; font-size: larger'>${this.$t(
            "AdminCenterTrash.Warning"
          )}</span>: ${this.$t(
            "AdminCenterTrash.you are about to"
          )} <span style='font-weight: bold; font-size: larger'>${this.$t(
            "AdminCenterTrash.definitively"
          )}</span> ${this.$t("AdminCenterTrash.delete Smart Element referenced")} <b>`;
          const deleteButtonForm = `<div class='button_wrapper'> <button class='k-cancel' ref='cancel'>${this.$t(
            "AdminCenterTrash.Cancel"
          )}</button><button class='k-delete' ref='deleteElement'>${this.$t(
            "AdminCenterTrash.Delete from trash"
          )}</button> </div>`;
          if (Number(thisPointer.NbReference) === 0) {
            this.content = `<span ref='content_confirm' class='content-confirm' ><span style='color:red;font-weight: bold; font-size: larger'>${this.$t(
              "AdminCenterTrash.Warning"
            )}</span>: ${this.$t(
              "AdminCenterTrash.you are about to"
            )} <span style='font-weight: bold; font-size: larger'>${this.$t(
              "AdminCenterTrash.definitively"
            )}</span> ${this.$t(
              "AdminCenterTrash.delete Smart Element not referenced"
            )}</p> <div class='button_wrapper'> <button class='k-cancel' ref='cancel'>${this.$t(
              "AdminCenterTrash.Cancel"
            )}</button><button class='k-delete' ref='deleteElement'>${this.$t(
              "AdminCenterTrash.Delete from trash"
            )}</button> </div>`;
          } else if (Number(thisPointer.NbReference) <= 2) {
            let str = "";
            data.forEach(item => {
              str += `<li>${item.stitle}</li>`;
            });
            this.content = `${deleteReference}<ul>${str}</ul>` + deleteButtonForm;
          } else {
            this.content =
              deleteReference +
              thisPointer.NbReference +
              `</b> ${this.$t("AdminCenterTrash.other Smart Elements")}</p>` +
              deleteButtonForm;
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
                      this.$refs.grid._loadGridContent();
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
              title: `${e.data.row.properties.title} : ${this.$t("AdminCenterTrash.Confirm deletion")}`
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
