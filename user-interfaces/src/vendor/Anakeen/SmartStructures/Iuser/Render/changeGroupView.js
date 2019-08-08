import "@progress/kendo-ui/js/kendo.treeview";
import "./changeGroupView.css";

window.ank.smartElement.globalController.registerFunction("iuserGroup", controller => {
  let getGroupTreeSource;
  let checkedGroups;
  let currentDoc;
  let notified = false;
  const getGroups = () => {
    return fetch("/api/v2/admin/account/groups/", {
      credentials: "same-origin"
    })
      .then(response => {
        return response.json();
      })
      .then(response => {
        return response.groups;
      });
  };

  const initTreeGroup = groups => () => {
    return new kendo.data.HierarchicalDataSource({
      filter: {},
      transport: {
        read: options => {
          groups
            .then(groups => {
              Object.values(groups).forEach(currentData => {
                currentData.expanded = true;
                currentData.items = currentData.items || [];
                currentData.parents.forEach(parentData => {
                  try {
                    groups[parentData].items = groups[parentData].items || [];
                    groups[parentData].items.push(currentData);
                  } catch (e) {
                    //no need to handle the error
                  }
                });
              });
              //Suppress first level elements
              Object.values(groups).forEach(currentData => {
                if (currentData.parents.length > 0) {
                  delete groups[currentData.accountId];
                }
              });

              try {
                //Suppress refs elements and keep only values
                groups = Object.values(JSON.parse(JSON.stringify(groups)));
              } catch (e) {
                groups = [];
              }
              const addUniqId = (currentElement, id = "") => {
                currentElement.hierarchicalId = id ? id + "/" + currentElement.documentId : currentElement.documentId;
                if (currentElement.items) {
                  currentElement.items.forEach(childrenElement => {
                    addUniqId(childrenElement, currentElement.hierarchicalId);
                  });
                }
              };
              groups.forEach(currentGroup => {
                addUniqId(currentGroup);
              });
              const restoreCheckedTree = checked => {
                return function analyzeChecked(data) {
                  data.forEach(currentData => {
                    currentData.checked = false;
                    if (checked[currentData.accountId]) {
                      currentData.checked = true;
                    }
                    if (currentData.items && currentData.items.length) {
                      analyzeChecked(currentData.items);
                    }
                  });
                };
              };
              if (checkedGroups) {
                restoreCheckedTree(checkedGroups)(groups);
              }
              const hasChildChecked = data => {
                return data.reduce((accumulator, currentData) => {
                  if (currentData.items && currentData.items.length) {
                    if (hasChildChecked(currentData.items)) {
                      currentData.hasChildChecked = true;
                      return true;
                    }
                  }
                  return accumulator || currentData.checked;
                }, false);
              };
              hasChildChecked(groups);
              options.success(groups);
            })
            .catch(error => {
              console.error("Unable to get group", error);
            });
        }
      },
      schema: {
        model: {
          id: "hierarchicalId",
          children: "items"
        }
      }
    });
  };

  controller.addEventListener(
    "beforeRender",
    {
      name: "changeGroupBeforeRender.changeGroup",
      check: documentObject => {
        const serverData = controller.getCustomServerData();
        return documentObject.renderMode === "edit" && serverData["GROUP_ANALYZE"];
      }
    },
    () => {
      if (!getGroupTreeSource) {
        getGroupTreeSource = initTreeGroup(getGroups());
      }
    }
  );
  controller.addEventListener("afterSave", { name: "changeGroupSave.changeGroup" }, function reloadInConsultation() {
    getGroupTreeSource = initTreeGroup(getGroups());
  });
  controller.addEventListener(
    "ready",
    {
      name: "changeGroupReady.changeGroup",
      check: documentObject => {
        const serverData = controller.getCustomServerData();
        currentDoc = documentObject.id;
        return documentObject.renderMode === "edit" && serverData["GROUP_ANALYZE"];
      }
    },
    () => {
      const serverData = controller.getCustomServerData();
      checkedGroups = serverData.groups;
      let filterTitle = null;

      const updateTreeSource = kendoTree => {
        return (force = false) => {
          let groupTreeSource = kendoTree.dataSource;
          const filter = filterTitle ? { field: "title", operator: "contains", value: filterTitle } : {};
          if (force) {
            const newTreeSource = getGroupTreeSource();
            newTreeSource.read().then(() => {
              kendoTree.setDataSource(newTreeSource);
              newTreeSource.filter(filter);
            });
          } else {
            groupTreeSource.filter(filter);
          }
        };
      };

      const getChecked = checked => currentEventNode => {
        return function analyzeChecked(dataSource) {
          const data = dataSource instanceof kendo.data.HierarchicalDataSource && dataSource.data();
          if (data === false) {
            return;
          }
          data.forEach(currentNode => {
            let isChecked = null;
            if (currentEventNode.accountId === currentNode.accountId) {
              isChecked = currentEventNode.checked;
            }
            if (isChecked === null && currentNode.accountId && currentNode.checked) {
              isChecked = true;
            }
            if (isChecked) {
              checked[currentNode.accountId] = true;
            }
            if (currentNode.children) {
              analyzeChecked(currentNode.children);
            }
          });
        };
      };

      let updateListOfGroup;
      $("#listOfGroups").kendoTreeView({
        checkboxes: true,
        dataSource: getGroupTreeSource(),
        select: event => {
          event.preventDefault();
        },
        template:
          "<span # if(item.hasChildChecked) {# class='hasChildChecked' #}# data-accountId='#= item.accountId #' data-se-id='#= item.documentId #'>#= item.title # (#= item.nbUser #) </span>",
        check: function onTreeCheck(event) {
          const eventNode = this.dataItem(event.node);
          const checked = {};
          if (eventNode.documentId !== currentDoc.toString() && !eventNode.id.includes(currentDoc.toString())) {
            getChecked(checked)(eventNode)(event.sender.dataSource);
            checkedGroups = checked;
            controller.addCustomClientData({
              parentGroups: checkedGroups
            });
            updateListOfGroup(true);
          } else {
            if (!notified) {
              $("body").dcpNotification("showError", {
                title: "Move group",
                message: "Can't set self or children groups as parent group"
              });
              notified = true;
            }
            $("#listOfGroups input.k-checkbox[id=_" + eventNode.uid + "]")
              .prop("checked", false)
              .trigger("change");
          }
          notified = false;
        }
      });
      $("#formFilter").on("submit", event => {
        event.preventDefault();
        filterTitle = document.getElementById("filterTree").value
          ? document.getElementById("filterTree").value.toLowerCase()
          : "";
        updateListOfGroup();
      });

      updateListOfGroup = updateTreeSource($("#listOfGroups").data("kendoTreeView"));
    }
  );
});
