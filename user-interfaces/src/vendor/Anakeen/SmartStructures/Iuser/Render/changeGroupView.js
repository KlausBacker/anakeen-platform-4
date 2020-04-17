import kendo from "@progress/kendo-ui/js/kendo.core";
import $ from "jquery";
import "@progress/kendo-ui/js/kendo.data";
import "@progress/kendo-ui/js/kendo.pager";
import "@progress/kendo-ui/js/kendo.listview";
import "./changeGroupView.scss";

window.ank.smartElement.globalController.registerFunction("iuserGroup", controller => {
  let addedGroups = [];
  let deletedGroups = [];
  let parentGroupdata = null;
  let availableGroupdata = null;

  const initParentGroupList = ($list, $pager, $template, $form, se) => {
    let filterValue = "";
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
          url: "/api/v2/ui/account/groups/" + se.id,
          data: filter => {
            if ($list.data("filter") === "not") {
              filter.not = true;
            }
            filter.filter = filterValue;
            filter.addedGroups = addedGroups;
            filter.deletedGroups = deletedGroups;
            return filter;
          }
        }
      },
      pageSize: 100
    });

    $pager.kendoPager({
      dataSource: dataSource
      // pageSizes: [10, 25, 50]
    });

    $list.kendoListView({
      dataSource: dataSource,
      selectable: "multiple",
      template: kendo.template($template.html())
    });

    $list.on("click", ".igroup-item.delete-group", function() {
      const id = $(this).data("id");

      const pos = addedGroups.indexOf(id);
      if (pos >= 0) {
        addedGroups.splice(pos, 1);
      } else {
        deletedGroups.push(id);
      }

      parentGroupdata.read();
      availableGroupdata.read();
    });
    $list.on("click", ".igroup-item.add-group", function() {
      const id = $(this).data("id");
      const pos = deletedGroups.indexOf(id);
      if (pos >= 0) {
        deletedGroups.splice(pos, 1);
      } else {
        addedGroups.push(id);
      }
      parentGroupdata.read();
      availableGroupdata.read();
    });

    $list.on("click", ".igroup-item .expand-pathes", function(event) {
      const $ul = $(event.currentTarget)
        .parent()
        .find("ul");
      const $button = $(event.currentTarget)
        .parent()
        .find("button span");
      event.preventDefault();
      event.stopPropagation();

      if ($ul.is(":visible")) {
        $ul.hide();
        $button.addClass("fa-caret-right");
        $button.removeClass("fa-caret-down");
      } else {
        $ul.show();
        $button.addClass("fa-caret-down");
        $button.removeClass("fa-caret-right");
      }
    });
    $form.on("submit", event => {
      event.preventDefault();
      filterValue = $(event.target)
        .find("input")
        .val();
      dataSource.read();
    });
    return dataSource;
  };

  controller.addEventListener("ready", {}, (event, se) => {
    $(event.target)
      .find(".dcpDocument")
      .addClass("smart-change-group");

    addedGroups = [];
    deletedGroups = [];
    parentGroupdata = null;
    availableGroupdata = null;

    let $parentGroupList = $(event.target).find('div[name="parentGroupList"]');
    let $parentGrouppager = $(event.target).find('div[name="parentGroupPager"]');
    let $parentGrouptemaplate = $(event.target).find('script[name="parentGroupTemplate"]');
    let $parentGroupform = $(event.target).find('form[name="parentGroupForm"]');

    parentGroupdata = initParentGroupList(
      $parentGroupList,
      $parentGrouppager,
      $parentGrouptemaplate,
      $parentGroupform,
      se
    );

    let $availableGroupList = $(event.target).find('div[name="availableGroupList"]');
    let $availableGrouppager = $(event.target).find('div[name="availableGroupPager"]');
    let $availableGrouptemaplate = $(event.target).find('script[name="availableGroupTemplate"]');
    let $availableGroupform = $(event.target).find('form[name="availableGroupForm"]');

    availableGroupdata = initParentGroupList(
      $availableGroupList,
      $availableGrouppager,
      $availableGrouptemaplate,
      $availableGroupform,
      se
    );
  });

  controller.addEventListener("beforeSave", {}, (event, se, request, custom) => {
    const data = parentGroupdata.data();
    const parentGroups = addedGroups;
    for (let i = 0; i < data.length; i++) {
      parentGroups.push(data[i].accountId);
    }
    custom.parentGroups = parentGroups;
  });
});
