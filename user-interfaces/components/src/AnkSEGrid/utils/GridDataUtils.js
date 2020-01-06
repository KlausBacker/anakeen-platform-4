import kendo from "@progress/kendo-ui/js/kendo.core";
import GridEvent from "./GridEvent";
import AbstractGridUtil from "./AbstractGridUtil";
import ActionTemplate from "../templates/GridAction.template.kd.js";
import ActionMenuTemplate from "../templates/GridActionMenu.template.kd.js";

function isset(jsObj) {
  return !(jsObj === null || jsObj === undefined);
}

function empty(jsObj) {
  if (!isset(jsObj)) {
    return true;
  }
  if (Array.isArray(jsObj) && !jsObj.length) {
    return true;
  }
  return typeof jsObj === "object" && !Object.keys(jsObj).length;
}

export default class GridDataUtils extends AbstractGridUtil {
  getDataSourceModel(colsConfig = []) {
    const model = {
      id: "initid",
      fields: {
        initid: {
          from: "rowData.initid"
        }
      }
    };
    if (colsConfig.length) {
      colsConfig.forEach(col => {
        if (col.field) {
          let type = "string";
          switch (col.smartType) {
            case "text":
            case "longtext":
            case "htmltext":
              type = "string";
              break;
            case "int":
            case "double":
            case "money":
              type = "string";
              break;
            case "date":
            case "time":
              type = "string";
              break;
            case "":
              break;
            default:
              type = "string";
              break;
          }
          model.fields[col.field] = {
            type
          };
        }
      });
    }
    return model;
  }

  /**
   * Format the value in function of the type of the smart field
   * @param {Object} colConfig - Configuration of the column (field id, type, multiple...)
   * @return {Function} - Template function for kendo grid column
   */
  formatAnkAttributesValue(colConfig) {
    const emptyValue = this.vueComponent.emptyCell;
    return dataItem => {
      let resultRender = "";
      let currentValue = null;
      const type = colConfig.smartType;

      if (dataItem.rowData) {
        currentValue = dataItem.rowData[colConfig.field];
        // Convert kendo dataItem object in js array if multiple attribute
        if (colConfig.multiple) {
          const currentMultipleValue = [];
          Object.keys(currentValue).forEach(key => {
            const index = parseInt(key);
            if (!isNaN(index)) {
              currentMultipleValue.push(currentValue[key]);
            }
          });
          currentValue = currentMultipleValue;
        }
        if (empty(currentValue) || (typeof currentValue === "object" && currentValue.value === null)) {
          // Empty value case
          resultRender = emptyValue;
        } else if (colConfig.template && typeof colConfig.template !== "function") {
          // Execute template if already provided
          resultRender = kendo.template(colConfig.template)(dataItem);
        } else {
          // Compute typed-template for a single value
          let unitValueTemplate = "";
          switch (type) {
            case "account":
            case "docid":
              unitValueTemplate = `<a href="#: url#" target="_blank" class="grid-cell-docid-link" data-initid="#: initid#" data-revision="#: revision#"> <img src="#: icon#" alt="#: displayValue# icon" class="grid-cell-icon" />#: displayValue#</a>`;
              break;
            case "image":
              unitValueTemplate = `<img src="# if (data.url) { # #: data.url# # } else { # #:data# # } #" alt="#if (data.displayValue) { # #: data.displayValue# # } else { # #:data# # }#"/>`;
              break;
            case "icon":
              unitValueTemplate = `<img src="#: data#"/>`;
              break;
            case "password":
              unitValueTemplate = `# if (data.displayValue) { return data.displayValue.replace(/./g, '•'); } else { return "N/C" } #`;
              break;
            case "color":
              unitValueTemplate = `<div class="color-value" style="display: flex; align-items: center"><span style="background-color: #= value#; border-radius: 50%; width: 16px; height: 16px; margin-right: .5rem"></span> <span> #= displayValue#</span></div>`;
              break;
            case "int":
              unitValueTemplate = `#= kendo.toString(parseInt(data.value), '\\#\\#,\\#') #`;
              break;
            case "double":
              unitValueTemplate = `#= kendo.toString(parseFloat(data.value), '\\#\\#\\#,\\#\\#\\#.\\#\\#\\#\\#\\#\\#') #`;
              break;
            case "money":
              unitValueTemplate = `#= kendo.toString(parseFloat(data.value), 'c2') #`;
              break;
            case "date":
              unitValueTemplate = "#= kendo.toString(new Date(data.value), 'd') #";
              break;
            case "time":
              unitValueTemplate = "#= kendo.toString(new Date(data.value), 'T') #";
              break;
            case "timestamp":
              unitValueTemplate = "#= kendo.toString(new Date(data.value), 'G') #";
              break;
            case "text":
              if (colConfig.property && colConfig.field === "title" && currentValue.iconUrl) {
                unitValueTemplate = `<span> <img src="#: iconUrl#" alt="#: value# icon" class="grid-cell-icon" />#: displayValue#</span>`;
              } else {
                unitValueTemplate = `#= data.displayValue || data.value || data #`;
              }
              break;
            default:
              unitValueTemplate = `#= data.displayValue || data.value || data #`;
              break;
          }
          unitValueTemplate = `<div class="grid-cell-value">${unitValueTemplate}</div>`;

          // If multiple value, concat unit typed-templates separated by <br/> tag
          if (colConfig.multiple) {
            resultRender = currentValue
              .map(elem => {
                if (Array.isArray(elem)) {
                  // Second level of multiplicity
                  const secondLevelRender = elem
                    .map(subElem => {
                      if (empty(subElem) || (typeof subElem === "object" && subElem.value === null)) {
                        // Empty value case
                        return emptyValue;
                      }
                      return kendo.template(unitValueTemplate)(subElem);
                    })
                    .join("<div class='grid-cell-content-br'></div>");
                  return `<div class="grid-cell-multiple-value">${secondLevelRender}</div>`;
                }
                if (empty(elem) || (typeof elem === "object" && elem.value === null)) {
                  // Empty value case
                  return emptyValue;
                }
                return kendo.template(unitValueTemplate)(elem);
              })
              .join("<hr class='grid-cell-content-hr'/>");
          } else {
            resultRender = kendo.template(unitValueTemplate)(currentValue);
          }
        }
      } else {
        currentValue = dataItem;
        resultRender = dataItem.toString();
      }
      resultRender = `<div class="grid-cell-content grid-cell-content--${type}">${resultRender}</div>`;
      if (this.vueComponent) {
        const event = new GridEvent(
          {
            cellData: currentValue,
            rowData: dataItem.rowData || dataItem,
            columnConfig: colConfig,
            cellRender: this.vueComponent.$(resultRender)
          },
          null,
          false,
          "GridCellRenderEvent"
        );
        this.vueComponent.$emit("before-grid-cell-render", event);
        if (event && event.data && event.data.cellRender) {
          return this.vueComponent
            .$(event.data.cellRender)
            .wrapAll("<div>")
            .parent()
            .html();
        }
      }
      return resultRender;
    };
  }

  parseData(data) {
    if (data) {
      return data.map(el => {
        const attributes = {};
        const properties = {};
        if (el.attributes) {
          Object.keys(el.attributes).forEach(attrid => {
            attributes[attrid] = el.attributes[attrid];
          });
        }
        if (el.properties) {
          Object.keys(el.properties).forEach(propid => {
            properties[propid] = el.properties[propid];
          });
        }
        return {
          rowData: {
            ...attributes,
            ...properties
          }
        };
      });
    }
    return [];
  }

  formatActionsColumn(actionsConfig) {
    const actionsColumn = {
      title: actionsConfig.title || "",
      command: [],
      headerAttributes: {
        "data-id": "ank-se-grid-actions"
      },
      attributes: {
        class: "grid-cell-vertical-align-center"
      },
      width: "12rem"
    };
    const allActionsConfig = actionsConfig.actionConfigs;
    if (!actionsConfig.actionConfigs) {
      return actionsColumn;
    }
    const subCommands = [];
    allActionsConfig.forEach((config, index, selfArray) => {
      const action = this.vueComponent.gridActions.getAction(config.action);
      if (selfArray.length <= 2 || index < 1) {
        actionsColumn.command.push({
          name: config.action,
          text: config.title || action.title,
          iconClass: config.iconClass !== undefined ? config.iconClass : action.iconClass,
          template: ActionTemplate,
          click: e => action.click(e, config.action)
        });
      } else {
        subCommands.push({
          name: config.action,
          text: config.title || action.title,
          iconClass: config.iconClass !== undefined ? config.iconClass : action.iconClass,
          click: e => action.click(e, config.action)
        });
      }
    });
    if (subCommands.length) {
      actionsColumn.command.push({
        name: "_subcommands",
        text: "",
        iconClass: "k-icon k-i-more-vertical",
        template: this.vueComponent.$kendo.template(ActionMenuTemplate)({
          subCommands: subCommands,
          iconClass: "k-icon k-i-more-vertical"
        }),
        click: e => {
          e.preventDefault();
        }
      });
    }
    return actionsColumn;
  }

  computeTotalExport(grid) {
    const selectedRows = grid.selectedKeyNames();
    const countTotals = grid.dataSource.total();
    let countRows = countTotals;
    if (!this.vueComponent.isFullSelectionState) {
      countRows = selectedRows.length ? selectedRows.length : 0;
    } else {
      countRows = countRows - this.getUncheckRowsList().length;
    }
    const $exportSelectionButton = this.vueComponent
      .$(".grid-export-action-menu", this.vueComponent.$el)
      .find(".k-item[data-export-action=selection] .k-link");

    const $exportAllButton = this.vueComponent
      .$(".grid-export-action-menu", this.vueComponent.$el)
      .find(".k-item[data-export-action=all] .k-link");

    const template = count => `<span class="export-total">${count}</span>`;
    let totalExport = $exportSelectionButton.find(".export-total");
    if (totalExport.length) {
      totalExport.replaceWith(template(countRows));
    } else {
      $exportSelectionButton.append(template(countRows));
    }

    const kendoMenu = this.vueComponent.$(".grid-export-action-menu", this.vueComponent.$el).data("kendoMenu");
    if (kendoMenu) {
      if (countRows === 0) {
        kendoMenu.enable($exportSelectionButton, false);
      } else {
        kendoMenu.enable($exportSelectionButton, true);
      }
    }

    totalExport = $exportAllButton.find(".export-total");
    if (totalExport.length) {
      totalExport.replaceWith(template(countTotals));
    } else {
      $exportAllButton.append(template(countTotals));
    }
  }

  getUncheckRowsList() {
    const result = [];
    Object.keys(this.vueComponent.uncheckRows).forEach(key => {
      if (this.vueComponent.uncheckRows[key] === true) {
        result.push(key);
      }
    });
    return result;
  }
}
