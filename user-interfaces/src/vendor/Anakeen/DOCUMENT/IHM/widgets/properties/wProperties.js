import $ from "jquery";
import _ from "underscore";
import Mustache from "mustache";
import "../widget";
import "../window/wDialog";

export default $.widget("dcp.dcpDocumentProperties", $.dcp.dcpDialog, {
  options: {
    documentId: 0,
    revision: -1,
    window: {
      modal: true,
      title: "Document properties"
    },
    labels: {
      identifier: "Identifier",
      title: "Title",
      logicalName: "Logical name",
      revision: "Revision number",
      version: "Version",
      family: "Family",
      lockedBy: "Locked by",
      createdBy: "Created by",
      notLocked: "Not locked",
      confidential: "Confidential",
      notConfidential: "Not confidential",
      creationDate: "Creation date",
      lastModificationDate: "Last modification date",
      profil: "Profil",
      profilReference: "Profil reference",
      fieldAccess: "Field access",
      viewController: "View controller",
      propertiesTitle: "Properties of {{title}}",
      property: "Property",
      propertyValue: "Value",
      workflow: "Workflow",
      activity: "Activity"
    }
  },
  documentProperties: null,
  htmlCaneva: function wProperties_htmlCaneva() {
    return (
      '<table class="properties-main table table-condensed table-hover"><thead>' +
      '<tr class="properties-header">' +
      '<th class="properties-header--description">{{labels.property}}</th>' +
      '<th class="properties-header--value">{{labels.propertyValue}}</th>' +
      "</tr></thead>" +
      "<tbody>" +
      '<tr><td class="properties-description">{{labels.title}}</td><td class="properties-value">{{title}}</td></tr>' +
      '<tr><td class="properties-description">{{labels.identifier}}</td><td class="properties-value">{{initid}}</td></tr>' +
      '<tr><td class="properties-description">{{labels.logicalName}}</td><td class="properties-value">{{name}}</td></tr>' +
      '<tr><td class="properties-description">{{labels.revision}}</td><td class="properties-value">{{revision}}</td></tr>' +
      '<tr><td class="properties-description">{{labels.version}}</td><td class="properties-value">{{version}}</td></tr>' +
      '<tr><td class="properties-separator" colspan="2"></td></tr>' +
      '<tr><td class="properties-description">{{labels.workflow}}</td><td class="properties-value"><a  data-document-id="{{workflow.id}}" href="/api/v2/smart-elements/{{workflow.id}}.html">{{#workflow.icon}}<img src="{{workflow.icon}}"/>{{/workflow.icon}}{{workflow.title}}</a></td></tr>' +
      '<tr><td class="properties-description">{{labels.activity}}</td><td class="properties-value">{{#state.reference}}<div class="properties-value-statecolor" style="background-color:{{state.color}}"/>{{state.displayValue}}{{/state.reference}}</td></tr>' +
      '<tr><td class="properties-description">{{labels.family}}</td><td class="properties-value"><a data-document-id="{{family.id}}" href="/api/v2/smart-elements/{{family.id}}.html"><img src="{{family.icon}}"/>{{family.title}}</a><br/><div class="properties-value--famname">{{family.name}}</div></td></tr>' +
      '<tr><td class="properties-separator" colspan="2"></td></tr>' +
      '<tr><td class="properties-description">{{labels.createdBy}}</td><td class="properties-value"><a data-document-id="{{createdBy.id}}" href="/api/v2/smart-elements/{{createdBy.id}}.html"><img src="{{createdBy.icon}}"/>{{createdBy.title}}</a></td></tr>' +
      '<tr><td class="properties-description">{{labels.lockedBy}}</td><td class="properties-value">' +
      "{{#security.lock.lockedBy.id}}" +
      '<a data-document-id="{{security.lock.lockedBy.id}}" href="/api/v2/smart-elements/{{security.lock.lockedBy.id}}.html"><img src="{{security.lock.lockedBy.icon}}"/>{{security.lock.lockedBy.title}}</a>' +
      "{{/security.lock.lockedBy.id}}" +
      "{{^security.lock.lockedBy.id}}{{labels.notLocked}}{{/security.lock.lockedBy.id}}" +
      "</td></tr>" +
      '<tr><td class="properties-description">{{labels.confidential}}</td><td class="properties-value">' +
      "{{#confidential}}" +
      "{{labels.confidential}}" +
      "{{/confidential}}" +
      "{{^confidential}}{{labels.notConfidential}}{{/confidential}}" +
      "</td></tr>" +
      '<tr><td class="properties-separator" colspan="2"></td></tr>' +
      '<tr><td class="properties-description">{{labels.creationDate}}</td><td class="properties-value">{{#formatDate}}{{creationDate}}{{/formatDate}}</td></tr>' +
      '<tr><td class="properties-description">{{labels.lastModificationDate}}</td><td class="properties-value">{{#formatDate}}{{lastModificationDate}}{{/formatDate}}</td></tr>' +
      '<tr><td class="properties-separator" colspan="2"></td></tr>' +
      '<tr><td class="properties-description">{{labels.profil}}</td><td class="properties-value"><a data-document-id="{{security.profil.id}}" href="/api/v2/smart-elements/{{security.profil.id}}.html">{{#security.profil.icon}}<img src="{{security.profil.icon}}"/>{{/security.profil.icon}}{{security.profil.title}}</a></td></tr>' +
      '<tr><td class="properties-description">{{labels.profilReference}}</td><td class="properties-value"><a data-document-id="{{security.profil.reference.id}}" href="/api/v2/smart-elements/{{security.profil.reference.id}}.html">{{#security.profil.reference.icon}}<img src="{{security.profil.reference.icon}}"/>{{/security.profil.reference.icon}}{{security.profil.reference.title}}</a></td></tr>' +
      '<tr><td class="properties-description">{{labels.fieldAccess}}</td><td class="properties-value"><a data-document-id="{{security.fieldAccess.id}}" href="/api/v2/smart-elements/{{security.fieldAccess.id}}.html">{{#security.fieldAccess.icon}}<img src="{{security.fieldAccess.icon}}"/>{{/security.fieldAccess.icon}}{{security.fieldAccess.title}}</a></td></tr>' +
      '<tr><td class="properties-description">{{labels.viewController}}</td><td class="properties-value"><a data-document-id="{{viewController.id}}" href="/api/v2/smart-elements/{{viewController.id}}.html">{{#viewController.icon}}<img src="{{viewController.icon}}"/>{{/viewController.icon}}{{viewController.title}}</a></td></tr>' +
      "</tbody></table>"
    );
  },

  _create: function wProperties__create() {
    var scope = this;

    this._displayProperties();

    this._super();

    this.element.data("dcpDocumentProperties", this);
    this.element.on("click" + this.eventNamespace, "a[data-document-id]", function wProperties_bindClick(event) {
      var docid = $(this).data("document-id");
      if (docid) {
        event.preventDefault();
        scope.element.trigger("viewDocument", docid);
      }
    });
  },

  _displayProperties: function wPropertiesGetProperties() {
    var scope = this;
    let url = "/api/v2/smart-elements/" + this.options.documentId;
    url +=
      this.options.revision >= 0
        ? "/revisions/" + this.options.revision + ".json?fields=document.properties.all&useTrash=true"
        : ".json?fields=document.properties.all&useTrash=true";
    $.getJSON(url)
      .done(function wProperties_done(data) {
        var info;
        scope.documentProperties = data.data.revision ? data.data.revision.properties : data.data.document.properties;
        info = _.extend(scope.documentProperties, {
          labels: scope.options.labels
        });
        info.formatDate = function wProperties_formatDate() {
          return function wProperties_formatDate2(text, render) {
            return kendo.toString(new Date(render(text).replace(" ", "T")), "G");
          };
        };
        scope.element.html(Mustache.render(scope.htmlCaneva() || "", info));
        //scope.dialogWindow.center();

        scope.dialogWindow.setOptions({
          title: data.data.revision
            ? Mustache.render(scope.options.labels.propertiesTitle, data.data.revision.properties)
            : Mustache.render(scope.options.labels.propertiesTitle, data.data.document.properties)
        });
      })
      .fail(function wProperties_fail(xhr) {
        var result = JSON.parse(xhr.responseText);
        window.alert(result.exceptionMessage);
      });
  }
});
