import "./docGrid.css";

(function initGrid() {
  "use strict";

  var init_grid = function require_grid(_, $) {
    /**
     * Attach an overlay to all a.overlay : open href in an iframe
     * @param element
     */
    var addOverlay = function addOverlay(element) {
      element.on("click.docGrid", "a.overlay", function click(e) {
        var eventOverlay = jQuery.Event("overlay.docGrid");
        $(this).trigger(eventOverlay);
        if (eventOverlay.isDefaultPrevented()) {
          e.preventDefault();
          return;
        }
        e.preventDefault();
        //noinspection JSJQueryEfficiency
        var $kendoWindow = $("#kendoWindow");
        if ($kendoWindow.length > 0) {
          $kendoWindow.remove();
        }
        $("body").append('<div id="kendoWindow"></div>');
        //noinspection JSJQueryEfficiency
        $kendoWindow = $("#kendoWindow");
        $kendoWindow.addClass("grid-overlay");
        $kendoWindow.kendoWindow({
          position: { top: "50px", left: "10%" },
          pinned: true,
          modal: true,
          width: "80%",
          height: "90%",
          content: this.href,
          title: "",
          iframe: true,
          close: function onClose(event) {
            if ($kendoWindow.data("canBeClose") !== true) {
              $kendoWindow.find("iframe").attr("src", "Images/1x1.gif");
              event.preventDefault();
            }
          },
          open: function onOpen() {
            var overlay = this;
            $kendoWindow.kendoWindow("center");
            $kendoWindow.find("iframe").on("load", function onLoad() {
              var doc = $kendoWindow.find("iframe").contents()[0];
              if (
                doc &&
                doc.location &&
                doc.location.href &&
                doc.location.href.toLowerCase().indexOf("images/1x1.gif") > -1
              ) {
                $kendoWindow.data("canBeClose", true);
                overlay.close();
              }
            });
          }
        });
      });
    };

    /**
     * Basic error log (in console if exist)
     *
     * @param error
     */
    var onError = function onError(error) {
      console.error(error);
    };

    var htmlEncode = function htmlEncode(value) {
      if (!value) {
        return "";
      }
      return $("<div/>")
        .text(value)
        .html();
    };

    /**
     * Column render
     *
     * All function used in column rendering
     *
     * @type {Object}
     */
    var columnRender = {
      /** @namespace this.withIcon */
      /** @namespace this.withColor */
      /** @namespace data.properties */
      /** @namespace rowObj.aData */
      /**
       * Handle multiplicity
       *
       * Take the value, a formatter callback return an html fragment
       *
       * @param value
       * @param formatterCallBack
       * @return {String} html fragment
       * @private
       */
      _multiplicatorAttributeAnalysis: function _multiplicatorAttributeAnalysis(
        value,
        formatterCallBack
      ) {
        var htmlValue = "";
        formatterCallBack =
          formatterCallBack ||
          function defaultFormatter(data) {
            return data;
          };
        if (_.isArray(value)) {
          value = _.map(value, function map(currentValue) {
            if (_.isArray(currentValue)) {
              currentValue = _.map(currentValue, function map(currentValue) {
                return (
                  '<span class="multiple secondLevel">' +
                  formatterCallBack(currentValue) +
                  "</span>"
                );
              });
              currentValue = currentValue.join(" ");
            } else {
              currentValue = formatterCallBack(currentValue);
            }
            return currentValue;
          });
          _.each(value, function each(currentValue) {
            htmlValue +=
              '<span class="multiple firstLevel">' + currentValue + "</span>";
          });
        } else {
          htmlValue = formatterCallBack(value);
        }
        return htmlValue;
      },
      /**
       * Help to format element that handle icon
       *
       * @param data
       * @return {String}
       */
      _icon: function _icon(data) {
        var icon = data.icon || "";
        if (icon) {
          return (
            '<img class="docIcon gridIcon" src="' + htmlEncode(icon) + '"/>'
          );
        }
        return "";
      },
      /**
       * Format state element
       *
       * @param cellData
       * @return {String}
       */
      state: function state(cellData) {
        /** @namespace state.stateLabel */
        /** @namespace state.activity */
        var currentState = cellData.properties.state || {},
          html = "",
          label = currentState.displayValue || "";
        if (this.withColor && currentState.color) {
          html =
            '<span class="stateText stateColor" style="border-color: ' +
            htmlEncode(currentState.color) +
            '">' +
            htmlEncode(label) +
            "</span>";
        } else if (label) {
          html = '<span class="stateText">' + htmlEncode(label) + "</span>";
        }
        return html;
      },
      /**
       * Format title element
       *
       * @param cellData
       * @return {String}
       */
      title: function title(cellData) {
        var currentTitle = cellData.properties.title || "",
          html = "";
        if (this.withIcon) {
          html = columnRender._icon(cellData.properties);
        }
        html +=
          '<span class="docTitle">' + htmlEncode(currentTitle) + "</span>";
        return html;
      },
      /**
       * Format openTitle element
       *
       * @param cellData
       * @return {String}
       */
      openTitle: function openTitle(cellData) {
        var url = columnRender.prepareUrl(cellData),
          title = cellData.properties.title || "",
          html = "";
        if (this.withIcon) {
          html = columnRender._icon(cellData.properties);
        }
        if (url) {
          html += $("<a/>")
            .addClass("openDoc overlay")
            .attr("href", url)
            .attr("data-docid", cellData.properties.initid)
            .attr("data-revision", cellData.properties.revision)
            .text(cellData.properties.title)[0].outerHTML;
        } else {
          html += title;
        }
        return html;
      },
      /**
       * Format openDoc element
       *
       * @param cellData
       * @return {String}
       */
      openDoc: function openDoc(cellData) {
        var url = columnRender.prepareUrl(cellData),
          html = "";
        if (url) {
          html += $('<a><span class="fa fa-external-link"></span></a>')
            .addClass("openDoc overlay btn btn-outline-secondary btn-xs")
            .attr("href", url)
            .attr("data-docid", cellData.properties.initid)
            .attr("data-revision", cellData.properties.revision)[0].outerHTML;
        }
        return html;
      },
      /**
       * Format docid element
       *
       * @param cellData
       * @return {String}
       */
      docid: function docid(cellData) {
        var attributes = cellData.attributes || [],
          value = attributes[this.id] || "",
          withIcon = this.withIcon;
        value = columnRender._multiplicatorAttributeAnalysis(
          value,
          function formatter(data) {
            var html = "",
              revision = _.isNumber(data.revision) ? data.revision : -1,
              url;
            if (withIcon) {
              html += columnRender._icon(data);
            }
            if (data.value) {
              url =
                "api/v2/documents/" +
                window.encodeURIComponent(data.value) +
                ".html";
              if (revision !== -1) {
                url =
                  "api/v2/documents/" +
                  window.encodeURIComponent(data.value) +
                  "/revisions/" +
                  revision +
                  ".html";
              }
              html += $("<a/>")
                .addClass("openDoc overlay")
                .attr("href", url)
                .attr("data-docid", data.initid)
                .attr("data-revision", revision)
                .text(data.displayValue)[0].outerHTML;
            } else {
              html += htmlEncode(data.displayValue);
            }
            return html;
          }
        );
        return value;
      },
      /**
       * Format account element
       *
       * @param cellData
       * @return {String}
       */
      account: function account(cellData) {
        var renderDocId = _.bind(columnRender.docid, this);
        return renderDocId(cellData);
      },
      /**
       * Format image element
       *
       * @param cellData
       * @return {String}
       */
      image: function image(cellData) {
        var renderFile = _.bind(columnRender.file, this);
        return renderFile(cellData);
      },
      /**
       * Format file element
       *
       * @param cellData
       * @return {String}
       */
      file: function file(cellData) {
        var attributes = cellData.attributes || [],
          value = attributes[this.id] || "",
          withIcon = this.withIcon;
        value = columnRender._multiplicatorAttributeAnalysis(
          value,
          function formatter(data) {
            var html = "";
            if (withIcon) {
              html += columnRender._icon(data);
            }
            if (data.url) {
              html +=
                '<a href="' +
                data.url +
                '">' +
                htmlEncode(data.displayValue) +
                "</a>";
            } else {
              html += data.displayValue || "";
            }
            return html;
          }
        );
        return value;
      },
      /**
       * Format color element
       *
       * @param cellData
       * @return {String}
       */
      color: function color(cellData) {
        var attributes = cellData.attributes || [],
          value = attributes[this.id] || "";
        value = columnRender._multiplicatorAttributeAnalysis(
          value,
          function formatter(data) {
            if (data.value) {
              return (
                '<div class="colorElement" style="background-color: ' +
                htmlEncode(data.value) +
                '"></div>'
              );
            } else {
              return "";
            }
          }
        );
        return value;
      },
      /**
       * Format text element (use simple formatter)
       *
       * @param cellData
       * @return {String}
       */
      text: function text(cellData) {
        var renderSimple = _.bind(columnRender.simple, this);
        return renderSimple(cellData);
      },
      /**
       * Format htmltext element use direct htmlvalue
       *
       * @param cellData
       * @return {String}
       */
      htmltext: function htmltext(cellData) {
        var attributes = cellData.attributes || [],
          value = attributes[this.id] || "";
        value = columnRender._multiplicatorAttributeAnalysis(
          value,
          function formatter(data) {
            if (data.value) {
              return '<div class="htmlElement">' + data.value + "</div>";
            } else {
              return "";
            }
          }
        );
        return value;
      },
      /**
       * Basic formatter
       *
       * handle multiplicty and display displayValue
       *
       * @param cellData
       * @return {String}
       */

      simple: function simple(cellData) {
        var attributes = cellData.attributes || [],
          value = cellData.properties[this.id] || attributes[this.id] || "";
        if (!cellData.properties[this.id]) {
          value = columnRender._multiplicatorAttributeAnalysis(
            value,
            function formatter(data) {
              return htmlEncode(data.displayValue);
            }
          );
        }
        return value;
      },

      prepareUrl: function documentPrepareUrl(cellData) {
        var revision = _.isNumber(cellData.properties.revision)
            ? cellData.properties.revision
            : -1,
          url =
            "api/v2/documents/" +
            window.encodeURIComponent(cellData.properties.initid) +
            ".html";
        if (revision !== -1) {
          url =
            "api/v2/documents/" +
            window.encodeURIComponent(cellData.properties.initid) +
            "/revisions/" +
            revision +
            ".html";
        }
        return url;
      }
    };
    var propertiesList = {
      id: true,
      owner: true,
      icon: true,
      title: true,
      revision: true,
      version: true,
      initid: true,
      fromid: true,
      doctype: true,
      locked: true,
      allocated: true,
      lmodify: true,
      profid: true,
      usefor: true,
      cdate: true,
      revdate: true,
      comment: true,
      classname: true,
      state: true,
      wid: true,
      postitid: true,
      cvid: true,
      name: true,
      dprofid: true,
      atags: true,
      prelid: true,
      lockdomainid: true,
      domainid: true,
      confidential: true,
      svalues: true,
      ldapdn: true
    };

    var isProperty = function currentElement(element) {
      return Boolean(propertiesList[element]);
    };

    /**
     * Call server and return data
     *
     * Handle error
     *
     * @param data
     * @param callback
     */
    var serverData = function serverData(data, callback) {
      var url,
        filters,
        order,
        putFirst = false,
        addSeparator = function addSeparator(url) {
          if (putFirst === false) {
            url += "?";
            putFirst = true;
          } else {
            url += "&";
          }
          return url;
        },
        currentWidget = this,
        constructData = {},
        error = _.bind(this._error, this),
        _trigger = _.bind(this._trigger, this);

      if (this.options.autoload) {
        url = this.options.gridDataSourceUrl;
        /** Construct get part **/
        if (this.options.temporarySearch) {
          url = this.options.temporarySearch;
        }
        if (this.options.dataTableOptions.columnDefs) {
          url = addSeparator(url);
          url += "fields=";
          putFirst = true;
          _.each(
            this.options.dataTableOptions.columnDefs,
            function iterateColumn(columnContent) {
              if (!columnContent.id) {
                return;
              }
              if (isProperty(columnContent.id)) {
                url +=
                  "document.properties." +
                  window.encodeURIComponent(columnContent.id) +
                  ",";
              } else {
                url +=
                  "document.attributes." +
                  window.encodeURIComponent(columnContent.id) +
                  ",";
              }
            }
          );
          url = url + "document.properties.revision,document.properties.icon";
        }
        filters = this.getFilters();
        if (filters) {
          url = addSeparator(url);
          url +=
            "filters=" + window.encodeURIComponent(JSON.stringify(filters));
        }
        if (data.start) {
          url = addSeparator(url);
          url += "offset=" + window.encodeURIComponent(data.start);
        }
        if (data.length) {
          url = addSeparator(url);
          if (data.length === -1) {
            url += "slice=all";
          } else {
            url += "slice=" + window.encodeURIComponent(data.length);
          }
        }
        if (
          data.order &&
          data.order[0] &&
          _.isNumber(data.order[0].column) &&
          data.order[0].dir
        ) {
          order = _.find(
            this.options.dataTableOptions.columnDefs,
            function findElement(currentColumn) {
              return (
                currentColumn && currentColumn.targets === data.order[0].column
              );
            }
          );
          order.id = order.id || "title";
          url = addSeparator(url);
          url += "orderBy=" + order.id + ":" + data.order[0].dir;
        }
        url = addSeparator(url);
        //noinspection JSCheckFunctionSignatures
        if (!this.options.temporarySearch) {
          if (this.options.collection) {
            constructData.collection = this.options.collection;
          }
          constructData.criterias = this.options.criterias;
          $.post(url, constructData)
            .pipe(
              function success(response) {
                if (response.success) {
                  currentWidget.options.temporarySearch =
                    response.data.searchContentUrl;
                  return response;
                } else {
                  return $.Deferred().reject(response);
                }
              },
              function fail(response) {
                if (response && response.responseJSON) {
                  return {
                    success: false,
                    result: null,
                    data: response.responseJSON,
                    error: response.responseJSON.messages[0].contentText
                  };
                } else {
                  return {
                    success: false,
                    result: null,
                    error:
                      "Unexpected error: " +
                      response.status +
                      " " +
                      response.statusText
                  };
                }
              }
            )
            .then(
              function success(response) {
                _trigger("change", {}, response);
                callback({
                  recordsTotal: response.data.resultTotal,
                  recordsFiltered: response.data.resultFiltered,
                  data: response.data.documents
                });
              },
              function fail(response) {
                error(response.error || response);
                callback({ recordsTotal: 0, recordsFiltered: 0, data: [] });
              }
            );
        } else {
          $.get(url)
            .pipe(
              function success(response) {
                if (response.success) {
                  return response;
                } else {
                  return $.Deferred().reject(response);
                }
              },
              function fail(response) {
                if (response.responseJSON) {
                  if (response.responseJSON.exceptionMessage) {
                    return {
                      success: false,
                      result: null,
                      error: response.responseJSON.exceptionMessage
                    };
                  } else {
                    if (response.responseJSON.messages.length > 0) {
                      return {
                        success: false,
                        result: null,
                        error: response.responseJSON.messages[0].contentText
                      };
                    } else {
                      return {
                        success: false,
                        result: null,
                        error: response.responseText
                      };
                    }
                  }
                } else {
                  return {
                    success: false,
                    result: null,
                    error:
                      "Unexpected error: " +
                      response.status +
                      " " +
                      response.statusText
                  };
                }
              }
            )
            .then(
              function success(response) {
                _trigger("change", {}, response);
                callback({
                  recordsTotal: response.data.resultTotal,
                  recordsFiltered: response.data.resultFiltered,
                  data: response.data.documents
                });
              },
              function fail(response) {
                error(response.error || response);
                callback({ recordsTotal: 0, recordsFiltered: 0, data: [] });
              }
            );
        }
      } else {
        this.options.autoload = true;
        callback({ recordsTotal: 0, recordsFiltered: 0, data: [] });
      }
    };

    $.widget("dcp.docGrid", {
      defaults: {
        offlineColumnsDef: false,
        autoload: true,
        collection: null,
        columnsDef: {},
        columns: [],
        withOverlay: true,
        sortable: true,
        filterable: false,
        criterias: [],
        temporarySearch: null,
        gridDataSourceUrl: "api/v2/documentGrid/content/",
        columnsDefUrl: "api/v2/documentGrid/columnsDefinition/",
        filterEnumContentUrl:
          "/api/v2/families/<%= famId %>/enumerates/<%= attrId %>",
        filterStateContentUrl: "/api/v2/documentGrid/states/<%= famId %>/",
        columnsUserSelection: false,
        dataTableOptions: {}
      },

      _initiated: false,

      _dataTableDefaultOptions: {
        dom:
          "<'row'<'col-sm-12'<'pull-left'>l<'clearfix'>>>rt<' dt-info'<'col-sm-12'<'pull-left'i><'pull-right'p><'clearfix'>>>",
        pagingType: "full",
        serverSide: true,
        autoWidth: false,
        processing: true,
        filter: false,
        order: null,
        language: {
          url: "?app=DOCUMENT_GRID_HTML5&action=GETDATATABLELOCAL",
          lengthMenu: "Show _MENU_ Rows",
          search: ""
        }
      },

      _create: function _create() {
        this.options = _.extend({}, this.defaults, this.options);

        var initiateDocGrid = _.bind(this._initiateDocGrid, this),
          _mergeColumnsDef = _.bind(this._mergeColumnsDef, this),
          error = _.bind(this._error, this),
          sendData = {
            columns: this.options.columnsDef.columns
          };
        if (
          !this.options.offlineColumnsDef &&
          this.options.columnsDef &&
          this.options.columnsDef.columns
        ) {
          sendData.columns = _.map(
            _.filter(this.options.columnsDef.columns, function filter(
              currentValue
            ) {
              return currentValue.id !== undefined;
            }),
            function map(currentColumns) {
              return currentColumns.id;
            }
          );
          if (sendData.columns.length > 0) {
            this._handleAjaxRequest(
              $.getJSON(this.options.columnsDefUrl, {
                famId: this.options.columnsDef.defaultFam || false,
                columns: sendData.columns.join(","),
                familyColumns: this.options.columnsUserSelection
              }),
              function success(response) {
                _mergeColumnsDef(response.data);
                initiateDocGrid();
              },
              function fail(response) {
                error(response.error || response);
              }
            );
          } else {
            _mergeColumnsDef();
            initiateDocGrid();
          }
        } else {
          _mergeColumnsDef();
          initiateDocGrid();
        }
      },

      //refresh the dataTable
      refresh: function refresh() {
        var table = this.element.DataTable();
        if (_.isFunction(table.draw)) {
          table.draw();
        }
      },

      // destroy the widget
      _destroy: function destroy() {
        if (this._initiated) {
          this.element.DataTable().destroy();
        }
        this.element.empty();
        this.element.off(".docGrid");
      },

      //get the current filters of the grid
      getFilters: function getFilters() {
        var filters = [];
        this.element.find("input.filter").each(function each() {
          var $this = $(this),
            value = "";

          if ($this.attr("data-type")) {
            if (
              $this.attr("data-type") === "enum" ||
              $this.attr("data-type") === "state"
            ) {
              value = $this.data("kendoComboBox").value();
            } else {
              value = $this.val();
            }
          }
          if (value) {
            filters.push({
              id: $this.attr("data-id"),
              famId: $this.attr("data-famId"),
              type: $this.attr("data-type"),
              value: value
            });
          }
        });
        return filters;
      },
      option: function setOption(key, value) {
        this._setOption(key, value);
      },

      //set option
      _setOption: function _setOption(key, value) {
        if (key === "criterias") {
          this.options.criterias = value;
          this.options.temporarySearch = null;
          this.refresh();
        } else if (key === "collection") {
          this.options.collection = value;
          this.options.temporarySearch = null;
          this.refresh();
        } else {
          this._error([
            "Unable to change option " +
              key +
              " you have to destroy the docgrid and rebuild it to change this option"
          ]);
          return;
        }
      },

      // trigger an error event and log error
      _error: function _error(error, event) {
        event = event || {};
        onError(error || "");
        this._trigger("error", event, { error: error });
        $("<pre/>")
          .addClass("grid-error")
          .text(error)
          .kendoWindow({
            title: "Error",
            height: "auto"
          })
          .data("kendoWindow")
          .center()
          .open();
      },

      // handle error and exception for ajax request
      _handleAjaxRequest: function _handleAjaxRequest(
        requestObject,
        success,
        fail
      ) {
        //noinspection JSCheckFunctionSignatures
        requestObject
          .pipe(
            function success(response) {
              if (response.success) {
                return response;
              } else {
                return $.Deferred().reject(response);
              }
            },
            function fail(response) {
              return {
                success: false,
                result: null,
                error:
                  "Unexpected error: " +
                  response.status +
                  " " +
                  response.statusText
              };
            }
          )
          .then(success, fail);
      },

      // merge the definition of columns (ajax and options def)
      _mergeColumnsDef: function _mergeColumnsDef(data) {
        var displayColumns = data.displayColumns || [];
        var userColumns = data.userColumns || [];
        var dg = this;

        this.visibleColumns = data.visibleColumns;

        this.options.columns = _.union(
          this.options.columns,
          _.map(this.options.columnsDef.columns, function map(
            currentInitialColumn
          ) {
            var mergedColumn, currentDataColumn;
            currentDataColumn = _.find(displayColumns, function find(
              currentDataColumn
            ) {
              return currentDataColumn.id === currentInitialColumn.id;
            });
            mergedColumn = _.extend(
              {},
              currentDataColumn,
              currentInitialColumn
            );
            if (!mergedColumn.className) {
              if (mergedColumn.id) {
                mergedColumn.className = "cell-" + mergedColumn.id;
              } else if (mergedColumn.type) {
                mergedColumn.className = "cell-" + mergedColumn.type;
              }
            }
            return mergedColumn;
          })
        );

        _.each(userColumns, function grid_addUserColumn(userCol) {
          var isDefined = _.some(dg.options.columns, function(colfdef) {
            return colfdef.id === userCol.id;
          });
          if (!isDefined) {
            userCol.userDefined = true;
            dg.options.columns.push(userCol);
          }
        });
      },

      // initiate the dataTable : get options, init datatable, attach overlay
      _initiateDocGrid: function _initiateDocGrid() {
        var element = this.element,
          trigger = $.bind(this._trigger, this);

        var dg = this;
        this.options.dataTableOptions = this._generateDataTableOptions();

        var customdrawCallback = this.options.dataTableOptions.drawCallback;
        if (this.options.filterable) {
          this._initiateHeader();
        }
        if (this.options.footer) {
          this._initiateFooter();
        }

        this.options.dataTableOptions.drawCallback = function DGdrawCallback() {
          if (customdrawCallback) {
            customdrawCallback.apply(element, arguments);
          }
          element.addClass("document-grid");
          dg._addSelectColumn();
          trigger("redraw");
        };
        //noinspection JSUnresolvedFunction
        element.dataTable(this.options.dataTableOptions);
        if (this.options.withOverlay) {
          addOverlay(element);
        }
        this._attachEvents();
        this._initiated = true;
        if (window.parent) {
          window.parent.postMessage("docGrid.loaded", window.location.href);
        }
      },

      _addSelectColumn: function() {
        if (!this.options.columnsUserSelection || !this.visibleColumns) {
          return;
        }

        var $th = this.element.find("thead th").first();
        var $ulmain = $("<ul/>").addClass("dg-selectcolumn-menu");
        var $li = $("<li/>").text("+");
        var $ul = $("<ul/>").addClass("dg-selectcolumn");
        var dg = this;

        $ulmain.append($li);
        $li.append($ul);
        _.each(this.visibleColumns, function(item) {
          var $li = $("<li/>")
            .text(item.label)
            .data("attrid", item.id);
          var alreadyVisible = _.some(dg.options.columns, function(colInfo) {
            return colInfo.id === item.id;
          });

          if (alreadyVisible) {
            $li.addClass("dg-select-visible");
            $li.data("isSelected", true);
          }
          $ul.append($li);
        });
        $th.prepend($ulmain);
        $ulmain.kendoMenu({
          openOnClick: true,
          select: function(event) {
            var $item = $(event.item);
            var attrid = $item.data("attrid");
            if (attrid) {
              $.ajax({
                url: dg.options.columnsDefUrl,
                type: $item.data("isSelected") ? "DELETE" : "POST",
                data: JSON.stringify({
                  famId: dg.options.columnsDef.defaultFam,
                  attrid: attrid
                }),
                dataType: "json",
                contentType: "application/json"
              })
                .done(function() {
                  dg.reload();
                })
                .fail(function(response) {
                  if (response.responseJSON) {
                    dg._error(response.responseJSON.exceptionMessage);
                  } else {
                    dg._error(response.responseText);
                  }
                });
            }
          }
        });
      },

      //attach the event to the header
      _attachEvents: function _attachEvents() {
        var element = this.element;
        element.on("keyup.docGrid", ".filter", function keyUp(event) {
          if (event.keyCode === 13) {
            if (element.val()) {
              element.removeAttr("data-key");
            }
            element.dataTable().fnDraw();
          }
        });
      },

      /**
       * Complete reinit dattatable object
       */
      reload: function DGReload() {
        this.element.off(".docGrid");
        this.element.DataTable().destroy();
        this.element.empty();
        this.options.columnsDef.columns = _.filter(
          this.options.columnsDef.columns,
          function(colItem) {
            return colItem.userDefined !== true;
          }
        );
        this.options.columns = [];
        this.options.dataTableOptions.columnDefs = [];
        this.options.dataTableOptions.aoColumnDefs = undefined;
        this._create();
      },

      //initiate the Header (used for filtering)
      _initiateHeader: function _initiateHeader() {
        var $tr,
          tHead,
          HeaderElement = [],
          currentWidget = this,
          analyseColumns = function analyseColumns(currentColumn) {
            var currentHeaderCallback = currentColumn.HeaderCallBack || "",
              target = currentColumn.targets;
            if (
              currentWidget.options.dataTableOptions.columnDefs !== undefined &&
              currentWidget.options.dataTableOptions.columnDefs[target] !==
                undefined &&
              currentWidget.options.dataTableOptions.columnDefs[target] !==
                null &&
              currentWidget.options.dataTableOptions.columnDefs[target]
                .visible === false
            ) {
              return;
            }
            HeaderElement[target] = _.isFunction(currentHeaderCallback)
              ? currentHeaderCallback(currentColumn)
              : currentHeaderCallback;
          },
          htmlElement = "";
        $tr = $("<tr />");
        if (this.element.find("thead").length === 0) {
          tHead = $("<thead></thead>").append($tr);
          this.element.append(tHead);
        } else {
          this.element.find("thead").prepend($tr);
        }
        _.each(
          this.options.dataTableOptions.columnDefs,
          _.bind(analyseColumns, this)
        );
        _.each(HeaderElement, function each(headerPart) {
          if (headerPart !== undefined) {
            htmlElement += "<td>" + (headerPart || "") + "</td>";
          }
        });
        $tr.append(htmlElement);
        this.element.prepend("<thead></thead>");
        //initiate the enum helper

        $tr.find("[data-type=enum]").each(function eachEnum() {
          var $this = $(this);
          currentWidget._initComboBox(
            $this,
            currentWidget,
            _.template(currentWidget.options.filterEnumContentUrl),
            {
              famId: $this.attr("data-famId"),
              attrId: $this.attr("data-id")
            }
          );
        });
        $tr.find("[data-type=state]").each(function eachState() {
          var $this = $(this);
          currentWidget._initComboBox(
            $this,
            currentWidget,
            _.template(currentWidget.options.filterStateContentUrl),
            {
              famId: $this.attr("data-famId")
            }
          );
        });
      },

      //initiate the Header (used for filtering)
      _initiateFooter: function _initiateFooter() {
        var $tr,
          HeaderElement = [],
          currentWidget = this,
          $tfoot,
          analyseColumns = function analyseColumns(currentColumn) {
            var target = currentColumn.targets;
            if (
              currentWidget.options.dataTableOptions.columnDefs !== undefined &&
              currentWidget.options.dataTableOptions.columnDefs[target] !==
                undefined &&
              currentWidget.options.dataTableOptions.columnDefs[target] !==
                null &&
              currentWidget.options.dataTableOptions.columnDefs[target]
                .visible === false
            ) {
              return;
            }
            HeaderElement[target] =
              currentWidget.options.dataTableOptions.columnDefs[target];
          };
        $tr = $("<tr />");
        if (this.element.find("tfoot").length === 0) {
          $tfoot = $("<tfoot />");
          $tfoot.append($tr);
          this.element.append($tfoot);
        } else {
          this.element.find("tfoot").append($tr);
        }
        _.each(
          this.options.dataTableOptions.columnDefs,
          _.bind(analyseColumns, this)
        );
        _.each(HeaderElement, function each(headerPart) {
          if (headerPart !== undefined) {
            $tr.append($("<td />").attr("data-id", headerPart.id));
          }
        });
      },

      // Compute and generate options at dataTableFormat
      _generateDataTableOptions: function _generateDataTableOptions() {
        var options = {};
        options.ordering = this.options.sortable;
        options.searching = this.options.filterable;
        options.columnDefs = this._generateDataTableColumns();
        options.ajax = this.options.ajax || _.bind(serverData, this);
        if (this.options.dataTableOptions.columnDefs) {
          this.options.dataTableOptions.columnDefs = _.union(
            options.columnDefs,
            this.options.dataTableOptions.columnDefs
          );
        }
        options = _.extend(
          options,
          this._dataTableDefaultOptions,
          this.options.dataTableOptions
        );
        if (_.isNull(options.order)) {
          options.order = [];
        }
        return options;
      },

      // Compute and generate aoColumnsDef options
      _generateDataTableColumns: function _generateDataTableColumns() {
        var length,
          dataTableColumns = [],
          i,
          currentColumn;
        for (i = 0, length = this.options.columns.length; i < length; i++) {
          currentColumn = this._productDataTableColumn(this.options.columns[i]);
          currentColumn.targets = currentColumn.targets || i;
          dataTableColumns.push(currentColumn);
        }
        return dataTableColumns;
      },

      // Compute and generate ColumnDef element
      _productDataTableColumn: function _productDataTableColumn(value) {
        var type = value.type || "",
          column = {},
          render;
        _.extend(column, value);
        if (column.id) {
          column.famId = column.famId || this.options.columnsDef.defaultFam;
        }
        column.title = value.label || "";
        column.orderable = value.sortable || false;
        column.searchable = value.filterable || false;
        if (column.searchable && !column.id) {
          throw "You cannot filter a column without id";
        }
        column.data = null;
        column.HeaderCallBack = this._HeaderFilterRender.simple;
        if (type === "openDoc") {
          column.width = value.width || "20px";
        }
        render = value.render || value.type;
        if (!_.isFunction(render)) {
          render = columnRender[render] || columnRender.simple;
        }
        if (_.isFunction(render)) {
          render = _.bind(render, value);
        }
        column.render = render;
        return column;
      },

      // HeaderFilter
      _HeaderFilterRender: {
        /** @namespace value.doctitle */
        /**
         * Simple render (text and enum)
         *
         * @param value
         * @return {String} html
         */
        simple: function simple(value) {
          var kind,
            id = value.id;
          if (value.id && value.filterable) {
            kind = value.type;

            if (kind === "docid" || kind === "account") {
              id = value.doctitle || id;
            }
            if (kind === "enum") {
              return (
                '<div class="form-group"><div class="input-group"><input class="filter enumFilter combobox" ' +
                'type="text" ' +
                'data-id="' +
                id +
                '" data-famId="' +
                value.famId +
                '" data-type="' +
                kind +
                '"/>' +
                '<div class="input-group-addon removeButton">' +
                '<button class="btn btn btn-default"><span class="glyphicon glyphicon-remove"></span></button>' +
                "</div></div></div>"
              );
            } else if (kind === "state") {
              return (
                '<div class="form-group"><div class="input-group"><input class="filter stateFilter combobox" ' +
                'type="text" ' +
                'data-id="' +
                id +
                '" data-famId="' +
                value.famId +
                '" data-type="' +
                kind +
                '"/>' +
                '<div class="input-group-addon removeButton">' +
                '<button class="btn btn btn-default"><span class="glyphicon glyphicon-remove"></span></button>' +
                "</div></div></div>"
              );
            } else {
              return (
                '<input class="filter textFilter k-textbox" ' +
                'type="search" ' +
                'data-id="' +
                id +
                '" data-famId="' +
                value.famId +
                '" data-type="' +
                kind +
                '"/>'
              );
            }
          }
          return "";
        }
      },

      _initComboBox: function _initComboBox(
        element,
        currentWidget,
        url,
        additionnalData
      ) {
        element.kendoComboBox({
          minLength: 1,
          filter: "contains",
          autoBind: false,
          change: function onComboChange() {
            currentWidget.refresh();
          },
          dataSource: {
            type: "json",
            serverFiltering: true,
            transport: {
              read: {
                url: url(additionnalData)
              },
              parameterMap: function mapsParameters(data) {
                if (
                  data.filter &&
                  data.filter.filters &&
                  data.filter.filters.length &&
                  data.filter.filters[0] &&
                  _.isString(data.filter.filters[0].value)
                ) {
                  data.keyword = data.filter.filters[0].value;
                }
                delete data.filter;
                return data;
              }
            },
            schema: {
              data: function parseData(response) {
                if (!response.success) {
                  throw new Error(
                    "Unable to read the return of helpers " +
                      response.messages || ""
                  );
                }
                var data = response.data || [];
                if (data.enumItems) {
                  data = data.enumItems;
                }
                return data;
              }
            }
          },
          dataTextField: "label",
          dataValueField: "key"
        });
        element
          .closest(".form-group")
          .on("click", ".btn", function buttonClick() {
            var kendoCombo = element.data("kendoComboBox");
            if (kendoCombo.value() !== "") {
              kendoCombo.value("");
              kendoCombo.trigger("change");
            }
          });
      }
    });

    return $.fn.docGrid;
  };

  if (typeof define === "function" && define.amd) {
    define([
      "underscore",
      "jquery",
      "dcpDocument/widgets/widget",
      "datatables-bootstrap"
    ], function require_grid(_, $) {
      init_grid(_, $);
    });
  } else {
    //noinspection JSUnresolvedVariable
    init_grid(window._, window.$);
  }
})();
