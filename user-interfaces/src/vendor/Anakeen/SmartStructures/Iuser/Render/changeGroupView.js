import kendo from "@progress/kendo-ui/js/kendo.core";
import $ from "jquery";
import "@progress/kendo-ui/js/kendo.data";
import "@progress/kendo-ui/js/kendo.pager";
import "@progress/kendo-ui/js/kendo.listview";
import "./changeGroupView.css";

window.ank.smartElement.globalController.registerFunction("iuserGroup", controller => {

  let addedGroups=[];
  let deletedGroups=[];

  const initParentGroupList = ($list, $pager, $template, se) => {
    console.log(se);
    const dataSource = new kendo.data.DataSource({
      schema: {
        data: response => {
          return response.data;
        },
        total: function(response) {
          return response.total; // total is returned in the "total" field of the response
        }
      },
      serverPaging: true,
      serverFiltering: true,
      transport: {
        read: {
          url: "/api/v2/ui/account/groups/"+se.id ,
          data: filter => {
            if ($list.data("filter") === "not") {
              filter.not = true;
            }
            filter.addedGroups=addedGroups;
            filter.deletedGroups=deletedGroups;
            return filter;
          }
        }
      },
      pageSize: 21
    });

    $pager.kendoPager({
      dataSource: dataSource,
      pageSizes: [10, 25, 50]
    });

    $list.kendoListView({
      dataSource: dataSource,
      selectable: "multiple",
      template: kendo.template($template.html())
    });

    $list.on("click", "button.delete-group", function() {
      console.log(this);
      const id=$(this).data("id");
      deletedGroups.push(id);
      dataSource.read();
    });
    $list.on("click", "button.add-group", function() {
      console.log(this);
      const id=$(this).data("id");
      addedGroups.push(id);
      dataSource.read();
    });

    $("#formFilter").on("submit", event => {
      event.preventDefault();
      filterTitle = document.getElementById("filterTree").value
        ? document.getElementById("filterTree").value.toLowerCase()
        : "";
    });
  };
/*
  controller.addEventListener(
    "beforeRender",
    {
      name: "changeGroupBeforeRender.changeGroup",
      check: documentObject => {
        const serverData = controller.getCustomServerData();
        return documentObject.renderMode === "edit" && serverData["GROUP_ANALYZE"];
      }
    },
    () => {}
  );
  controller.addEventListener("afterSave", { name: "changeGroupSave.changeGroup" }, function reloadInConsultation() {
    //  TODO
  });

 */
  controller.addEventListener(
    "ready",
    {
      name: "changeGroupReady.changeGroup",
      check: documentObject => {
        const serverData = controller.getCustomServerData();
        return documentObject.renderMode === "edit" && serverData["GROUP_ANALYZE"];
      }
    },
    (event, se) => {
      const serverData = controller.getCustomServerData();
      let filterTitle = null;
      let $parentGroupList = $(event.target).find('div[name="parentGroupList"]');
      let $parentGrouppager = $(event.target).find('div[name="parentGroupPager"]');
      let $parentGrouptemaplate = $(event.target).find('script[name="parentGroupTemplate"]');

      initParentGroupList($parentGroupList, $parentGrouppager, $parentGrouptemaplate, se);


      let $availableGroupList = $(event.target).find('div[name="availableGroupList"]');
      let $availableGrouppager = $(event.target).find('div[name="availableGroupPager"]');
      let $availableGrouptemaplate = $(event.target).find('script[name="availableGroupTemplate"]');

      initParentGroupList($availableGroupList, $availableGrouppager, $availableGrouptemaplate, se);


    }
  );
});
