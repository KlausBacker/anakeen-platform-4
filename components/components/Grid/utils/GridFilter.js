/**
 * Functions to display column filters by type
 */
class FilterTemplate {
  constructor(kendojQuery) {
    this.$ = kendojQuery;
  }
  dateFilterTemplate(e) {
    let $inputDate = this.$("<input/>");
    $inputDate.insertBefore(e.element);
    e.element.addClass("filter-input--data");
    e.element.hide();
    $inputDate.kendoDatePicker({
      change: function() {
        e.element.val(
          FilterTemplate.convertDateToPseudoIsoString(this.value())
        );
        e.element.trigger("change");
      }
    });
    e.element.data("kendoTarget", $inputDate.data("kendoDatePicker"));

    $inputDate.addClass("filter-input");
  }

  timeFilterTemplate(e) {
    let $inputDate = this.$("<input/>");
    $inputDate.insertBefore(e.element);
    e.element.addClass("filter-input--data");
    e.element.hide();
    $inputDate.kendoTimePicker({
      change: function() {
        e.element.val(
          FilterTemplate.convertDateToPseudoIsoString(this.value(), {
            date: false,
            time: true
          })
        );
        e.element.trigger("change");
      }
    });
    e.element.data("kendoTarget", $inputDate.data("kendoTimePicker"));
    $inputDate.addClass("filter-input");
  }

  timestampFilterTemplate(e) {
    let $inputDate = this.$("<input/>");
    $inputDate.insertBefore(e.element);
    e.element.addClass("filter-input--data");
    e.element.hide();
    $inputDate.kendoDateTimePicker({
      change: function() {
        e.element.val(
          FilterTemplate.convertDateToPseudoIsoString(this.value(), {
            time: true,
            date: true
          })
        );
        e.element.trigger("change");
      }
    });
    e.element.data("kendoTarget", $inputDate.data("kendoDateTimePicker"));
    $inputDate.addClass("filter-input");
  }

  static moneyFilterTemplate(e) {
    e.element.kendoNumericTextBox({
      format: "c2",
      decimals: 2
    });
    e.element.addClass("filter-input");
  }

  static doubleFilterTemplate(e) {
    e.element.kendoNumericTextBox({
      //format: "n10",
      decimals: 10
    });
    e.element.addClass("filter-input");
  }

  static intFilterTemplate(e) {
    e.element.kendoNumericTextBox({
      format: "n0",
      decimals: 0
    });
    e.element.addClass("filter-input");
  }

  static enumFilterTemplate(e, enumId) {
    e.element.kendoDropDownList({
      dataTextField: "label",
      dataValueField: "key",
      dataSource: {
        transport: {
          read: {
            url: "/api/v2/enumerates/" + enumId + "/",
            dataType: "json"
          }
        },
        schema: {
          data: result => {
            return result.data.enumItems;
          }
        }
      }
    });

    e.element.addClass("filter-input");
  }

  smartEltFilterTemplate(e, structureid) {
    let $selectEle = this.$("<select/>").attr(
      "data-field",
      e.element.data("field")
    );
    $selectEle.insertBefore(e.element);
    $selectEle.addClass("filter-input--user");
    e.element.addClass("filter-input--data");
    e.element.hide();

    $selectEle.kendoComboBox({
      change: function() {
        e.element.val(this.value());
        e.element.trigger("change");
      },
      autoBind: false,
      dataTextField: "title",
      dataValueField: "key",
      filter: "contains",
      dataSource: {
        serverFiltering: true,
        transport: {
          read: {
            url: "/api/v2/ui/searches/" + structureid + "/",
            dataType: "json",
            data: {
              fields: "document.properties.title"
            }
          }
        },
        schema: {
          data: response => {
            let results = [];
            response.data.smartElements.forEach(item => {
              results.push({
                key: item.properties.initid,
                title: item.properties.title
              });
            });
            return results;
          }
        }
      }
    });
    e.element.data("kendoTarget", $selectEle.data("kendoComboBox"));
    e.element.addClass("filter-input");
  }
  static convertDateToPseudoIsoString(dateObject, options) {
    let padNumber = number => {
      if (number < 10) {
        return "0" + number;
      }
      return number;
    };
    let sDate = "";

    if (!dateObject) {
      return "";
    }
    options = options || { date: true };

    if (options.date === true) {
      sDate =
        dateObject.getFullYear() +
        "-" +
        padNumber(dateObject.getMonth() + 1) +
        "-" +
        padNumber(dateObject.getDate());
    }
    if (options.date === true && options.time === true) {
      sDate += "T";
    }

    if (options.time === true) {
      sDate +=
        padNumber(dateObject.getHours()) +
        ":" +
        padNumber(dateObject.getMinutes()) +
        ":" +
        padNumber(dateObject.getSeconds());
    }
    return sDate;
  }
}

/**
 * Class to manage row filters in grid
 */
export default class GridFilter {
  // méthode constructor
  constructor(grid) {
    this.grid = grid;
  }

  /**
   * Verify if a column use an aextra operator
   * @param fieldId
   * @returns {boolean}
   */
  isExtraOperator(fieldId) {
    let filter = this.grid.kendoGrid.dataSource.filter();
    let fieldCount = {};
    let isExtra = false;

    if (filter && filter.filters) {
      filter.filters.forEach(aFilter => {
        if (aFilter.field === fieldId && !isExtra) {
          if (!fieldCount[aFilter.field]) {
            fieldCount[aFilter.field] = 0;
          }
          fieldCount[aFilter.field]++;
          isExtra = aFilter.filters || fieldCount[aFilter.field] > 1;
        } else if (aFilter.filters && aFilter.filters.length > 0) {
          aFilter.filters.forEach(subFilter => {
            if (subFilter.field === fieldId) {
              isExtra = true;
            }
          });
        }
      });
    }
    return isExtra;
  }

  bindFilterEvents() {
    let $dropDownOperator = this.grid
      .$(this.grid.$refs.kendoGrid)
      .find("input.k-dropdown-operator");
    if ($dropDownOperator.length) {
      $dropDownOperator.data("kendoDropDownList").list.width("auto");

      $dropDownOperator.each((k, item) => {
        let $Drop = this.grid.$(item);
        let kDrop = $Drop.data("kendoDropDownList");
        let isBinded = $Drop.data("isBinded");
        let fieldId = $Drop.closest("th").data("field");
        if (kDrop && !isBinded) {
          let $label = $Drop.closest("th").find(".operator-label");

          kDrop.dataSource.add({
            text: this.grid.translations.extraOperator,
            value: "extra"
          });
          kDrop.list.width("auto");
          kDrop.bind("open", e => {
            let listContainer = e.sender.list.closest(".k-list-container");
            listContainer.width("auto");
            listContainer.addClass("smart-element-grid-operator-list");

            if (this.isExtraOperator(fieldId)) {
              kDrop.value("extra");
            }
          });

          $label.text(kDrop.dataSource.data()[0].text);
          /**
           * On operator select : display operator label
           * Add class in case of empty operator to display that this kind of filter as no inputs
           */
          kDrop.bind("select", e => {
            let $label = this.grid
              .$(e.sender.element)
              .closest("th")
              .find(".operator-label");
            let $cellInput = this.grid.$(e.sender.element).closest("th");
            let $input = $cellInput.find(".filter-input");
            $label.text(this.grid.$(e.item).text());

            if (
              e.dataItem.value === "isempty" ||
              e.dataItem.value === "isnotempty"
            ) {
              $cellInput.addClass("filter-no-input");
              $input.prop("disabled", true);
            } else if (e.dataItem.value === "extra") {
              let fieldId = this.grid
                .$(e.sender.element)
                .closest("th")
                .data("field");
              $cellInput.addClass("filter-no-input");
              $input.prop("disabled", true);
              this.grid
                .$(this.grid.$refs.kendoGrid)
                .find('th[data-field="' + fieldId + '"]')
                .find(".k-grid-filter")
                .trigger("click");
              //e.preventDefault();
            } else {
              $cellInput.removeClass("filter-no-input");
              $input.prop("disabled", false);
              $input.focus();
            }
          });
          $Drop.data("isBinded", true);
        }
      });
    }
  }

  getFilterOperatorLabel(fieldId) {
    let filter = this.grid.kendoGrid.dataSource.filter();
    let labels = [];
    let columns = this.grid.kendoGrid.columns;
    let fieldFilters = [];
    let colInfo = columns.filter(item => {
      return item.field === fieldId;
    });
    if (filter && filter.filters) {
      filter.filters.forEach(aFilter => {
        if (aFilter.field === fieldId) {
          fieldFilters.push(aFilter);
        } else if (aFilter.filters && aFilter.filters.length > 0) {
          aFilter.filters.forEach(subFilter => {
            if (subFilter.field === fieldId) {
              fieldFilters.push(subFilter);
            }
          });
        }
      });
    }

    fieldFilters.forEach(aFilter => {
      if (colInfo) {
        labels.push(colInfo[0].filterable.operators.string[aFilter.operator]);
      }
    });

    return labels.join(" and ");
  }
  /**
   * Display operator label from dataSource Filter
   */
  refreshOperatorLabel() {
    let filter = this.grid.kendoGrid.dataSource.filter();
    if (filter && filter.filters) {
      filter.filters.forEach(aFilter => {
        let fieldId = aFilter.field;
        if (!fieldId) {
          if (aFilter.filters && aFilter.filters.length > 0) {
            fieldId = aFilter.filters[0].field;
          }
        }
        let $th = this.grid
          .$(this.grid.$refs.kendoGrid)
          .find("th[data-field=" + fieldId + "]");
        let $input = $th.find(".filter-input--data");
        let $label = $th.find(".operator-label");
        let filterLabel = this.getFilterOperatorLabel(fieldId);

        if (aFilter.value) {
          $input.val(aFilter.value);
        }
        $label.text(filterLabel);
        if (!this.isExtraOperator(fieldId)) {
          $th.removeClass("filter-no-input");
          if (
            aFilter.operator === "isempty" ||
            aFilter.operator === "isnotempty"
          ) {
            $th.addClass("filter-no-input");
          }
        } else {
          $th.addClass("filter-no-input");
        }
      });
    }
  }

  getColumnFilterTemplate(e, col) {
    let $th = e.element.closest("th");
    let $form = e.element.closest("form");
    let $and = $form.find(".k-filter-and");
    let filterTemplate = new FilterTemplate(this.grid.$);

    $and.prop("disabled", true);
    $form.addClass("smart-element-grid-filter-menu");
    $th.attr("data-field", col.field);
    $th.prepend(this.grid.$('<div class="operator-label"/>'));

    e.element.attr("data-field", col.field);
    switch (col.smartType) {
      case "date":
        filterTemplate.dateFilterTemplate(e);
        break;
      case "time":
        filterTemplate.timeFilterTemplate(e);
        break;
      case "timestamp":
        filterTemplate.timestampFilterTemplate(e);
        break;
      case "money":
        FilterTemplate.moneyFilterTemplate(e);
        break;
      case "double":
        FilterTemplate.doubleFilterTemplate(e);
        break;
      case "int":
        FilterTemplate.intFilterTemplate(e);
        break;
      case "enum":
        FilterTemplate.enumFilterTemplate(e, col.relation);
        break;
      case "account":
      case "docid":
        filterTemplate.smartEltFilterTemplate(e, col.relation);
        break;
      default:
        e.element.addClass("k-textbox filter-input");
    }
  }

  onfilterMenuInit(e) {
    const $container = e.container;
    const additionalDropdown = $container
      .find(".k-dropdown[role=listbox] select")
      .last()
      .data("kendoDropDownList");
    if (additionalDropdown) {
      additionalDropdown.text(this.grid.translations.selectOperator);
    }
  }

  onfilterMenuOpen(e) {
    let $container = e.container;
    $container.find(".filter-input--data").each((k, item) => {
      let field = this.grid.$(item).data("field");
      let $origin = this.grid.$(
        ".k-filter-active .filter-input--user[data-field=" + field + "]"
      );
      let kOrigin = $origin.data("kendoComboBox");
      let kTarget = this.grid.$(item).data("kendoTarget");

      if (kOrigin) {
        // Need to copy data source form row input to menu input
        let data = kOrigin.dataSource.data();
        let targetDataKeys = kTarget.dataSource.data().map(datum => {
          return datum.key;
        });
        if (data) {
          data.forEach(datum => {
            if (targetDataKeys.indexOf(datum.key) < 0) {
              kTarget.dataSource.add(datum);
            }
          });
        }
      }
      kTarget.value(item.value);
    });
  }

  static beforeFilterGrid(e) {
    let field = e.field;
    let $filterButton = e.sender.element.find(
      ".k-filter-row th[data-field=" + field + "]"
    );

    if (e.filter && e.filter.filters[0].operator === "extra") {
      e.preventDefault();
    }

    if (e.filter == null || e.filter.filters[0].value === "") {
      // filter has been cleared
      $filterButton.removeClass("k-filter-active");
      $filterButton.find("input").val("");
    } else {
      e.filter.filters.forEach(aFilter => {
        // Need to use only key value : for enum smart type
        if (aFilter.value && aFilter.value.key) {
          aFilter.value = aFilter.value.key;
        }
      });
      $filterButton.addClass("k-filter-active");
    }
  }
}
