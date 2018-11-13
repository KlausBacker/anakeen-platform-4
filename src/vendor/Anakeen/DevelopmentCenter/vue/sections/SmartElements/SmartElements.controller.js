import Vue from "vue";
import { Splitter, LayoutInstaller } from "@progress/kendo-layout-vue-wrapper";
import { AnkSEGrid } from "@anakeen/ank-components";
import PropertyView from "./PropertyView/PropertyView.vue";
import hljs from "highlight.js/lib/highlight";
import xml from "highlight.js/lib/languages/xml";
import json from "highlight.js/lib/languages/json";

Vue.use(AnkSEGrid);
Vue.use(LayoutInstaller);

const prettifyXml = sourceXml => {
  const xmlDoc = new DOMParser().parseFromString(sourceXml, "application/xml");
  const xsltDoc = new DOMParser().parseFromString(
    [
      // describes how we want to modify the XML - indent everything
      '<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform">',
      '  <xsl:strip-space elements="*"/>',
      '  <xsl:template match="para[content-style][not(text())]">', // change to just text() to strip space in text nodes
      '    <xsl:value-of select="normalize-space(.)"/>',
      "  </xsl:template>",
      '  <xsl:template match="node()|@*">',
      '    <xsl:copy><xsl:apply-templates select="node()|@*"/></xsl:copy>',
      "  </xsl:template>",
      '  <xsl:output indent="yes"/>',
      "</xsl:stylesheet>"
    ].join("\n"),
    "application/xml"
  );

  const xsltProcessor = new XSLTProcessor();
  xsltProcessor.importStylesheet(xsltDoc);
  const resultDoc = xsltProcessor.transformToDocument(xmlDoc);
  const resultXml = new XMLSerializer().serializeToString(resultDoc);
  return resultXml;
};

export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "kendo-splitter": Splitter,
    "property-view": PropertyView
  },
  computed: {
    urlConfig() {
      return `/api/v2/devel/security/elements/config/`;
    }
  },
  data() {
    return {
      panes: [
        { scrollable: false, min: "33%", max: "100%" },
        { collapsed: true, collapsible: true }
      ],
      viewURL: "",
      viewType: "html",
      viewRawContent: "",
      viewComponent: null,
      viewComponentProps: {}
    };
  },
  beforeCreate() {
    hljs.registerLanguage("xml", xml);
    hljs.registerLanguage("json", json);
  },
  methods: {
    cellRender(event) {
      if (event.data) {
        if (event.data.columnConfig) {
          switch (event.data.columnConfig.field) {
            case "fromid":
              event.data.cellRender.text(event.data.cellData.name);
              break;
          }
        }
        if (event.data.rowData.doctype && event.data.rowData.doctype === "C") {
          event.data.cellRender.addClass("structure-type-cell");
        }
      }
    },
    gridDataBound(event) {
      if (event.data.kendoWidget.dataSource) {
        const items = event.data.kendoWidget.dataSource.view().toJSON();
        if (items.length) {
          const that = this;
          this.$(".actionMenu", this.$el).each(function(indexItem) {
            const kendoMenu = that.$(this).data("kendoMenu");
            const currentData = items[indexItem];
            if (currentData.rowData && currentData.rowData.doctype !== "C") {
              kendoMenu.remove("[data-actiontype=create]");
            }
          });
        }
      }
    },
    actionClick(event) {
      let urlRawContent;
      switch (event.data.type) {
        case "consult":
          this.viewType = "html";
          event.preventDefault();
          this.viewURL = `/api/v2/documents/${event.data.row.id}.html`;
          break;
        case "viewJSON":
          this.viewType = "json";
          switch (event.data.row.doctype) {
            case "C":
              urlRawContent = `/api/v2/families/${
                event.data.row.id
              }/views/structure`;
              break;
            default:
              urlRawContent = `/api/v2/documents/${event.data.row.id}.json`;
              break;
          }
          break;
        case "viewXML":
          this.viewType = "xml";
          switch (event.data.row.doctype) {
            case "C":
              urlRawContent = `/api/v2/devel/config/smart/structures/${
                event.data.row.id
              }.xml`;
              break;
            case "W":
              urlRawContent = `/api/v2/devel/config/smart/workflows/${
                event.data.row.id
              }.xml`;
              break;
            default:
              urlRawContent = `/api/v2/documents/${event.data.row.id}.xml`;
          }
          break;
        case "viewProps":
          this.viewType = "vue";
          this.viewComponent = "property-view";
          this.viewComponentProps = {
            elementId: event.data.row.id
          };
          break;
        case "security":
          // console.log(event.data);
          break;
        case "create":
          if (event.data.row.doctype === "C") {
            this.viewType = "html";
            this.viewURL = `/api/v2/documents/${
              event.data.row.id
            }/views/!defaultCreation.html`;
          }
          break;
      }
      if (this.viewType === "html" || this.viewType === "vue") {
        this.openView();
      } else if (this.viewType === "json" || this.viewType === "xml") {
        this.$http
          .get(urlRawContent)
          .then(response => {
            if (this.viewType === "json") {
              this.viewRawContent = hljs.highlightAuto(
                JSON.stringify(response.data.data, null, 2)
              ).value;
            } else if (this.viewType === "xml") {
              this.viewRawContent = hljs.highlightAuto(
                prettifyXml(response.data)
              ).value;
            }
            this.openView();
          })
          .catch(err => {
            console.error(err);
            throw err;
          });
      }
    },
    openView() {
      const splitter = this.$refs.splitter.kendoWidget();
      splitter.expand(".k-pane:last");
    }
  }
};
