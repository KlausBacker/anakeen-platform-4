import $ from "jquery";
import _ from "underscore";
import ViewAttribute from "../vAttribute";
import attributeTemplate from "../../document/attributeTemplate";
import * as EventPromiseUtils from "../../../widgets/globalController/utils/EventUtils";

export default ViewAttribute.extend({
  /**
   * Use special event to trigger only attributes of model
   */
  attributeEvents: function vColumnEvents() {
    var events = {};
    this._mergeEvent(events, "delete", "deleteValue");
    this._mergeEvent(events, "changeattrmenuvisibility", "changeMenuVisibility");
    this._mergeEvent(events, "changeattrsvalue", "changeAttributesValue");
    this._mergeEvent(events, "fetchdocument", "loadDocument");
    this._mergeEvent(events, "externallinkselected", "externalLinkSelected");
    this._mergeEvent(events, "downloadfile", "downloadFileSelect");
    this._mergeEvent(events, "uploadfile", "uploadFileSelect");
    this._mergeEvent(events, "uploadfiledone", "uploadFileDone");
    this._mergeEvent(events, "uploadfileerror", "uploadFileError");
    this._mergeEvent(events, "anchorclick", "anchorClick");
    this._mergeEvent(events, "displaynetworkerror", "displayNetworkError");
    this.listenTo(this.model, "change:label", this.changeLabel);
    return events;
  },

  _mergeEvent: function vColumn_addEvent(events, name, method) {
    events["dcpattribute" + name + ' .dcpArray__content__cell[data-attrid="' + this.model.id + '"]'] = method;
  },

  render: function vColumnRender() {
    var scope = this;
    if (this.displayLabel === false) {
      // Need to defer because thead is not construct yet
      _.defer(function vColumnHideHead() {
        var $head = scope.$el.find('.dcpArray__head__cell[data-attrid="' + scope.model.id + '"]');
        $head.hide();
      });
    } else {
      // Need to defer because thead is not construct yet
      _.defer(function vColumnDescriptionHead() {
        var $head = scope.$el.find('.dcpArray__head__cell[data-attrid="' + scope.model.id + '"]');
        attributeTemplate.insertDescription(scope, $head);
      });
    }
    this.model.trigger("renderColumnDone", {
      model: this.model,
      $el: this.$el
    });
    return this;
  },

  /**
   * Change the label of the column
   */
  changeLabel: function vColumnChangeLabel() {
    this.$el.find('.dcpArray__head__cell[data-attrid="' + this.model.id + '"]').text(this.model.get("label"));
  },

  /**
   * called by vArray::addLine()
   * @param index row index
   * @param customView HTML fragment to use for a custom view
   */
  addNewWidget: function vColumnAddNewWidget(index, customView) {
    return new Promise(
      _.bind(function vColumnAddNewWidget_promise(resolve, reject) {
        try {
          if (this.options) {
            var cells = this.options.parentElement.find(
                '.dcpArray__content__cell[data-attrid="' + this.model.id + '"]'
              ),
              $el,
              event = { prevent: false };

            if (cells[index]) {
              try {
                $el = $(cells[index]);
                this.model.trigger("beforeRender", event, {
                  model: this.model,
                  $el: $el,
                  index: index,
                  options: { customTemplate: !!customView }
                });

                EventPromiseUtils.getBeforeEventPromise(event, () => {
                  if (customView) {
                    $el.append(customView);
                    this.model.trigger("renderDone", {
                      model: this.model,
                      $el: $el,
                      index: index,
                      options: { customTemplate: true }
                    });
                    this.moveValueIndex({});
                    resolve($el);
                  } else {
                    $el.one(
                      "dcpattributewidgetready .dcpAttribute__content",
                      _.bind(function vcolumnRender_widgetready() {
                        this.model.trigger("renderDone", {
                          model: this.model,
                          $el: $el,
                          index: index,
                          options: { customTemplate: false }
                        });
                        this.moveValueIndex({});
                        resolve();
                      }, this)
                    );
                    this.widgetInit($el, this.getData(index));
                    attributeTemplate.insertDescription(this, $el.parent());
                    resolve();
                  }
                }).catch(() => {
                  resolve();
                });
              } catch (error) {
                if (window.dcp.logger) {
                  window.dcp.logger(error);
                } else {
                  console.error(error);
                }
                resolve();
              }
            } else {
              resolve();
            }
          } else {
            resolve();
          }
        } catch (e) {
          reject(e);
        }
      }, this)
    );
  },

  /**
   *
   * @param event
   * @param options
   */
  loadDocument: function vColumnLoadDocument(event, options) {
    var tableLine = options.tableLine,
      index = options.index,
      initid,
      valueLine = this.model.get("attributeValue")[tableLine],
      documentModel = this.model.getDocumentModel();
    if (_.isUndefined(index)) {
      initid = valueLine.value;
    } else {
      initid = valueLine[index].value;
    }

    this.model.trigger("internalLinkSelected", event, {
      eventId: "document.load",
      target: event.target,
      attrid: this.model.id,
      options: [initid, "!defaultConsultation"],
      index: options.index,
      row: tableLine
    });

    return EventPromiseUtils.getBeforeEventPromise(
      event,
      () => {
        documentModel.fetchDocument({
          initid: initid,
          revision: -1,
          viewId: "!defaultConsultation"
        });
        return this;
      },
      () => {
        return this;
      }
    );
  },

  /**
   * Hide all items of the column
   */
  hide: function vColumnHide() {
    this.getDOMElements().each(function vColumnHideEach() {
      var $cell = $(this);
      var tagName = $cell.prop("tagName").toLowerCase();

      if (tagName !== "td" && tagName !== "th") {
        $cell = $cell.closest("td.dcpArray__cell, th.dcpArray__head");
      }
      $cell.hide();
    });

    this.$el.find('thead th[data-attrid="' + this.model.id + '"]').hide();
  },
  /**
   * Show all hidden items of the column
   */
  show: function vColumnShow() {
    this.getDOMElements().each(function vColumnShowEach() {
      var $cell = $(this);
      var tagName = $cell.prop("tagName").toLowerCase();

      if (tagName !== "td" && tagName !== "th") {
        $cell = $cell.closest("td.dcpArray__cell, th.dcpArray__head");
      }
      $cell.show();
    });
    this.$el.find('thead th[data-attrid="' + this.model.id + '"]').show();
  }
});
